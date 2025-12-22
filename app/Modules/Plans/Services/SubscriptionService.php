<?php

namespace App\Modules\Plans\Services;

use App\Models\Core\User;
use App\Modules\Plans\Models\Plan;
use App\Modules\Plans\Models\Subscription;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Create a new subscription
     */
    public function createSubscription(User $user, Plan $plan, array $data = []): Subscription
    {
        $paymentFrequency = $data['payment_frequency'] ?? 'monthly';
        $paymentOptions = $plan->payment_options ?? [];
        $selectedOption = $paymentOptions[$paymentFrequency] ?? null;

        if (!$selectedOption) {
            throw new \Exception("Invalid payment frequency: {$paymentFrequency}");
        }

        $startDate = now();
        
        // Calculate end date based on payment frequency
        // Total care = payable_years + care_benefits_years = 10 years
        $payableYears = $selectedOption['payable_years'] ?? 0;
        $careBenefitsYears = $selectedOption['care_benefits_years'] ?? 0;
        $totalYears = $payableYears + $careBenefitsYears;
        
        // If total years is 0, default to monthly (30 days)
        if ($totalYears == 0) {
            $endDate = $startDate->copy()->addDays(30);
        } else {
            $endDate = $startDate->copy()->addYears($totalYears);
        }

        $totalAmount = $selectedOption['price'] ?? $plan->monthly_price ?? $plan->price;

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'payment_frequency' => $paymentFrequency,
            'status' => 'pending',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'care_benefits_years' => $careBenefitsYears,
            'payable_years' => $payableYears,
            'total_amount' => $totalAmount,
            'paid_amount' => 0.00,
            'payment_status' => 'pending',
            'auto_renew' => $data['auto_renew'] ?? false,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Approve a subscription
     */
    public function approveSubscription(Subscription $subscription, User $approvedBy): Subscription
    {
        $subscription->update([
            'status' => 'active',
            'approved_by' => $approvedBy->id,
            'approved_at' => now(),
        ]);

        return $subscription;
    }

    /**
     * Reject a subscription
     */
    public function rejectSubscription(Subscription $subscription, User $rejectedBy): Subscription
    {
        $subscription->update([
            'status' => 'rejected',
            'approved_by' => $rejectedBy->id,
            'approved_at' => now(),
        ]);

        return $subscription;
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => 'cancelled',
        ]);

        return $subscription;
    }

    /**
     * Renew a subscription
     */
    public function renewSubscription(Subscription $subscription): Subscription
    {
        $startDate = now();
        $endDate = $startDate->copy()->addDays($subscription->plan->duration_days);

        $subscription->update([
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $subscription;
    }

    /**
     * Get user's subscriptions
     */
    public function getUserSubscriptions(User $user)
    {
        return $user->subscriptions()
                   ->with(['plan', 'payments'])
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    /**
     * Get all subscriptions for admin
     */
    public function getAllSubscriptions(string $status = 'all')
    {
        $query = Subscription::with(['user', 'plan', 'approvedBy', 'paymentVerifiedBy']);
        
        if ($status !== 'all') {
            if ($status === 'pending') {
                // Show subscriptions with payment proof but not verified
                $query->where(function($q) {
                    $q->where('status', 'pending')
                      ->orWhere(function($q2) {
                          $q2->where('payment_status', '!=', 'paid')
                             ->where(function($q3) {
                                 $q3->whereNotNull('payment_screenshot')
                                    ->orWhereNotNull('transaction_id');
                             });
                      });
                });
            } else {
                $query->where('status', $status);
            }
        }
        
        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Get subscription statistics
     */
    public function getSubscriptionStats()
    {
        return [
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::active()->count(),
            'pending_subscriptions' => Subscription::pending()->count(),
            'expired_subscriptions' => Subscription::expired()->count(),
            'cancelled_subscriptions' => Subscription::cancelled()->count(),
        ];
    }

    /**
     * Check for expired subscriptions and update their status
     */
    public function checkExpiredSubscriptions(): int
    {
        $expiredCount = Subscription::where('status', 'active')
                                  ->where('end_date', '<', now())
                                  ->count();

        if ($expiredCount > 0) {
            Subscription::where('status', 'active')
                       ->where('end_date', '<', now())
                       ->update(['status' => 'expired']);
        }

        return $expiredCount;
    }

    /**
     * Get subscriptions expiring soon (within 7 days)
     */
    public function getExpiringSubscriptions(int $days = 7)
    {
        return Subscription::active()
                          ->whereBetween('end_date', [now(), now()->addDays($days)])
                          ->with(['user', 'plan'])
                          ->get();
    }

    /**
     * Check if user has active subscription that covers service
     */
    public function hasActiveSubscription(User $user): bool
    {
        return Subscription::where('user_id', $user->id)
                          ->where('status', 'active')
                          ->where('end_date', '>', now())
                          ->exists();
    }

    /**
     * Get active subscription for user
     */
    public function getActiveSubscription(User $user): ?Subscription
    {
        return Subscription::where('user_id', $user->id)
                          ->where('status', 'active')
                          ->where('end_date', '>', now())
                          ->with('plan')
                          ->latest()
                          ->first();
    }

    /**
     * Verify payment and activate subscription
     */
    public function verifyPayment(Subscription $subscription, User $verifiedBy): Subscription
    {
        $subscription->update([
            'payment_status' => 'paid',
            'paid_amount' => $subscription->total_amount,
            'payment_verified_by' => $verifiedBy->id,
            'payment_verified_at' => now(),
            'status' => 'active',
            'approved_by' => $verifiedBy->id,
            'approved_at' => now(),
        ]);

        return $subscription;
    }

    /**
     * Reject payment
     */
    public function rejectPayment(Subscription $subscription, User $rejectedBy, string $reason): Subscription
    {
        $subscription->update([
            'payment_status' => 'failed',
            'payment_verified_by' => $rejectedBy->id,
            'payment_verified_at' => now(),
            'notes' => ($subscription->notes ? $subscription->notes . "\n\n" : '') . "Payment Rejected: {$reason}",
        ]);

        return $subscription;
    }
}

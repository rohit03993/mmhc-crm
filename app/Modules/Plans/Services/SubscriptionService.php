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

        // Base amount (before GST)
        $baseAmount = $selectedOption['price'] ?? $plan->monthly_price ?? $plan->price;
        
        // Calculate GST (18% on base amount)
        $gstRate = (float) config('subscription.gst_rate', 18.00);
        $gstAmount = ($baseAmount * $gstRate) / 100;
        
        // Total amount (base + GST)
        $totalAmount = $baseAmount + $gstAmount;
        
        // Get referral commission rate (default 5%, editable by admin)
        $commissionRate = (float) config('subscription.referral_commission_rate', 5.00);
        $referrerId = $data['referrer_id'] ?? null;
        $commissionAmount = 0.00;
        
        // Calculate commission if referrer exists
        if ($referrerId) {
            $referrer = \App\Models\Core\User::find($referrerId);
            if ($referrer && ($referrer->isNurse() || $referrer->isCaregiver())) {
                $commissionAmount = ($baseAmount * $commissionRate) / 100;
            } else {
                $referrerId = null; // Invalid referrer
            }
        }

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'referrer_id' => $referrerId,
            'payment_frequency' => $paymentFrequency,
            'status' => 'pending',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'care_benefits_years' => $careBenefitsYears,
            'payable_years' => $payableYears,
            'base_amount' => $baseAmount,
            'gst_amount' => $gstAmount,
            'gst_rate' => $gstRate,
            'total_amount' => $totalAmount,
            'paid_amount' => 0.00,
            'payment_status' => 'pending',
            'referral_commission_amount' => $commissionAmount,
            'referral_commission_rate' => $commissionRate,
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
     * Upgrade or downgrade subscription
     * Calculates prorated amount based on remaining days
     */
    public function upgradeDowngradeSubscription(Subscription $currentSubscription, Plan $newPlan, array $data = []): Subscription
    {
        $paymentFrequency = $data['payment_frequency'] ?? 'monthly';
        $paymentOptions = $newPlan->payment_options ?? [];
        $selectedOption = $paymentOptions[$paymentFrequency] ?? null;

        if (!$selectedOption) {
            throw new \Exception("Invalid payment frequency: {$paymentFrequency}");
        }

        // Calculate remaining days in current subscription
        $remainingDays = max(0, now()->diffInDays($currentSubscription->end_date, false));
        $totalDays = $currentSubscription->start_date->diffInDays($currentSubscription->end_date);
        $usedDays = $totalDays - $remainingDays;
        
        // Calculate prorated refund for current subscription (if downgrade)
        $currentDailyRate = $currentSubscription->total_amount / max(1, $totalDays);
        $refundAmount = $remainingDays * $currentDailyRate;
        
        // New subscription amount
        $newBaseAmount = $selectedOption['price'] ?? $newPlan->monthly_price ?? $newPlan->price;
        $gstRate = (float) config('subscription.gst_rate', 18.00);
        $newGstAmount = ($newBaseAmount * $gstRate) / 100;
        $newTotalAmount = $newBaseAmount + $newGstAmount;
        
        // Calculate amount to pay (new amount - refund)
        $amountToPay = max(0, $newTotalAmount - $refundAmount);
        
        // Calculate new end date
        $payableYears = $selectedOption['payable_years'] ?? 0;
        $careBenefitsYears = $selectedOption['care_benefits_years'] ?? 0;
        $totalYears = $payableYears + $careBenefitsYears;
        
        $startDate = now();
        $endDate = $totalYears > 0 
            ? $startDate->copy()->addYears($totalYears) 
            : $startDate->copy()->addDays(30);
        
        // Cancel current subscription
        $currentSubscription->update([
            'status' => 'cancelled',
            'notes' => ($currentSubscription->notes ? $currentSubscription->notes . "\n\n" : '') . 
                      "Cancelled due to upgrade/downgrade on " . now()->format('Y-m-d H:i:s') . 
                      ". Prorated refund: ₹" . number_format($refundAmount, 2),
        ]);
        
        // Get referral commission
        $commissionRate = (float) config('subscription.referral_commission_rate', 5.00);
        $referrerId = $currentSubscription->referrer_id ?? $data['referrer_id'] ?? null;
        $commissionAmount = 0.00;
        
        if ($referrerId) {
            $referrer = \App\Models\Core\User::find($referrerId);
            if ($referrer && ($referrer->isNurse() || $referrer->isCaregiver())) {
                $commissionAmount = ($newBaseAmount * $commissionRate) / 100;
            } else {
                $referrerId = null;
            }
        }
        
        // Create new subscription
        return Subscription::create([
            'user_id' => $currentSubscription->user_id,
            'plan_id' => $newPlan->id,
            'referrer_id' => $referrerId,
            'payment_frequency' => $paymentFrequency,
            'status' => 'pending',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'care_benefits_years' => $careBenefitsYears,
            'payable_years' => $payableYears,
            'base_amount' => $newBaseAmount,
            'gst_amount' => $newGstAmount,
            'gst_rate' => $gstRate,
            'total_amount' => $newTotalAmount,
            'paid_amount' => 0.00,
            'payment_status' => 'pending',
            'referral_commission_amount' => $commissionAmount,
            'referral_commission_rate' => $commissionRate,
            'auto_renew' => $data['auto_renew'] ?? false,
            'notes' => ($data['notes'] ?? '') . 
                      "\nUpgraded from: {$currentSubscription->plan->name} (Subscription #{$currentSubscription->id})" .
                      "\nProrated refund applied: ₹" . number_format($refundAmount, 2) .
                      "\nAmount to pay: ₹" . number_format($amountToPay, 2),
            'previous_subscription_id' => $currentSubscription->id,
        ]);
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

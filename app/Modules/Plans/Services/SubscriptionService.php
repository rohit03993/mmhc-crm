<?php

namespace App\Modules\Plans\Services;

use App\Models\Core\User;
use App\Modules\Plans\Models\Plan;
use App\Modules\Plans\Models\Subscription;

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

        if (! $selectedOption) {
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

        // GST (student launch can be GST-inclusive flat price)
        if (! empty($selectedOption['price_includes_gst'])) {
            $gstRate = 0.0;
            $gstAmount = 0.0;
            $totalAmount = $baseAmount;
        } else {
            $gstRate = \App\Modules\Plans\Support\SubscriptionSettings::gstRate();
            $gstAmount = ($baseAmount * $gstRate) / 100;
            $totalAmount = $baseAmount + $gstAmount;
        }

        $referrerId = $data['referrer_id'] ?? null;
        if ($referrerId) {
            $refUser = \App\Models\Core\User::find($referrerId);
            if (! $refUser || (! $refUser->isNurse() && ! $refUser->isCaregiver())) {
                $referrerId = null;
            }
        }

        // Referral commission is finalized in rupees on payment verify (incentive engine + growth+DtA)
        $commissionAmount = 0.00;

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
            'referral_base_amount' => null,
            'referral_growth_percent' => null,
            'referral_dta_percent' => null,
            'auto_renew' => $data['auto_renew'] ?? false,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Recompute base, GST, total, care years, and end date from the plan catalogue for this subscription's payment_frequency.
     * Does not change plan_id, payment_frequency, status, or notes.
     *
     * @param  bool  $syncPaidAmount  When true, sets paid_amount to the new total (typical for demo repair).
     */
    public function reconcileSubscriptionFromPlanCatalogue(Subscription $subscription, bool $syncPaidAmount = true): Subscription
    {
        $plan = $subscription->plan;
        if (! $plan) {
            throw new \InvalidArgumentException('Subscription has no linked plan.');
        }

        $paymentOptions = $plan->payment_options ?? [];
        if (! is_array($paymentOptions)) {
            throw new \InvalidArgumentException('Plan has no payment options.');
        }

        $frequency = $subscription->payment_frequency;
        $selectedOption = $paymentOptions[$frequency] ?? null;
        if (! $selectedOption) {
            throw new \InvalidArgumentException("No payment option «{$frequency}» on this plan.");
        }

        $payableYears = (int) round((float) ($selectedOption['payable_years'] ?? 0));
        $careBenefitsYears = (int) round((float) ($selectedOption['care_benefits_years'] ?? 0));
        $totalYears = $payableYears + $careBenefitsYears;

        $baseAmount = (float) ($selectedOption['price'] ?? $plan->monthly_price ?? $plan->price ?? 0);
        if (! empty($selectedOption['price_includes_gst'])) {
            $gstRate = 0.0;
            $gstAmount = 0.0;
            $totalAmount = round($baseAmount, 2);
        } else {
            $gstRate = \App\Modules\Plans\Support\SubscriptionSettings::gstRate();
            $gstAmount = round(($baseAmount * $gstRate) / 100, 2);
            $totalAmount = round($baseAmount + $gstAmount, 2);
        }

        $startDate = $subscription->start_date ?? now();
        if (! $startDate instanceof \Carbon\CarbonInterface) {
            $startDate = \Carbon\Carbon::parse($startDate);
        }

        if ($totalYears <= 0) {
            $endDate = $startDate->copy()->addMonth();
        } else {
            $endDate = $startDate->copy()->addYears($totalYears);
        }

        $updates = [
            'payable_years' => $payableYears,
            'care_benefits_years' => $careBenefitsYears,
            'base_amount' => $baseAmount,
            'gst_amount' => $gstAmount,
            'gst_rate' => $gstRate,
            'total_amount' => $totalAmount,
            'end_date' => $endDate,
        ];

        if ($syncPaidAmount) {
            $updates['paid_amount'] = $totalAmount;
        }

        $subscription->update($updates);

        return $subscription->fresh();
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

        if (! $selectedOption) {
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
        $gstRate = \App\Modules\Plans\Support\SubscriptionSettings::gstRate();
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
            'notes' => ($currentSubscription->notes ? $currentSubscription->notes."\n\n" : '').
                      'Cancelled due to upgrade/downgrade on '.now()->format('Y-m-d H:i:s').
                      '. Prorated refund: ₹'.number_format($refundAmount, 2),
        ]);

        $referrerId = $currentSubscription->referrer_id ?? $data['referrer_id'] ?? null;
        if ($referrerId) {
            $refUser = \App\Models\Core\User::find($referrerId);
            if (! $refUser || (! $refUser->isNurse() && ! $refUser->isCaregiver())) {
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
            'referral_commission_amount' => 0.00,
            'referral_base_amount' => null,
            'referral_growth_percent' => null,
            'referral_dta_percent' => null,
            'auto_renew' => $data['auto_renew'] ?? false,
            'notes' => ($data['notes'] ?? '').
                      "\nUpgraded from: {$currentSubscription->plan->name} (Subscription #{$currentSubscription->id})".
                      "\nProrated refund applied: ₹".number_format($refundAmount, 2).
                      "\nAmount to pay: ₹".number_format($amountToPay, 2),
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
    public function getAllSubscriptions(string $status = 'all', ?int $filterUserId = null, int $perPage = 15)
    {
        $query = Subscription::with(['user', 'plan', 'approvedBy', 'paymentVerifiedBy']);

        if ($filterUserId) {
            $query->where('user_id', $filterUserId);
        }

        if ($status !== 'all') {
            if ($status === 'pending') {
                // Show subscriptions with payment proof but not verified
                $query->where(function ($q) {
                    $q->where('status', 'pending')
                        ->orWhere(function ($q2) {
                            $q2->where('payment_status', '!=', 'paid')
                                ->where(function ($q3) {
                                    $q3->whereNotNull('payment_screenshot')
                                        ->orWhereNotNull('transaction_id');
                                });
                        });
                });
            } else {
                $query->where('status', $status);
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
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

        $subscription->refresh();

        app(\App\Modules\Plans\Services\SubscriptionInvoiceService::class)
            ->ensurePaymentRecord($subscription);

        if ($subscription->referrer_id) {
            try {
                app(\App\Modules\Incentives\Services\IncentiveCalculatorService::class)
                    ->createOrUpdateSubscriptionSaleLedger($subscription);
            } catch (\Throwable $e) {
                \Log::error('Subscription incentive ledger: '.$e->getMessage(), [
                    'subscription_id' => $subscription->id,
                ]);
            }
        }

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
            'notes' => ($subscription->notes ? $subscription->notes."\n\n" : '')."Payment Rejected: {$reason}",
        ]);

        return $subscription;
    }
}

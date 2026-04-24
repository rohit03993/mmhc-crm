<?php

namespace App\Modules\Payments\Services;

use App\Models\Core\User;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Rewards\Models\CaregiverReward;
use App\Modules\Services\Models\ServiceRequest;

class StaffPayoutService
{
    /**
     * Build a query for service request payouts that are actually payable.
     * Payable means completed, admin-approved, payout exists, and not yet processed.
     */
    public function pendingServiceRequestQuery(int $staffId)
    {
        return ServiceRequest::where('assigned_staff_id', $staffId)
            ->where('status', 'completed')
            ->whereNotNull('admin_approved_at')
            ->whereNotNull('total_staff_payout')
            ->where('total_staff_payout', '>', 0)
            ->where(function ($query) {
                $query->where('staff_payment_processed', false)
                    ->orWhereNull('staff_payment_processed');
            });
    }

    public function pendingPatientRewardQuery(int $staffId)
    {
        return CaregiverReward::where('user_id', $staffId)
            ->where(function ($query) {
                $query->where('payment_processed', false)
                    ->orWhereNull('payment_processed');
            });
    }

    public function pendingSubscriptionReferralQuery(int $staffId)
    {
        return Subscription::where('referrer_id', $staffId)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->where('referral_payment_processed', false)
                    ->orWhereNull('referral_payment_processed');
            });
    }

    public function calculatePendingPayments(User $staff): array
    {
        $serviceQuery = $this->pendingServiceRequestQuery($staff->id);
        $rewardQuery = $this->pendingPatientRewardQuery($staff->id);
        $subscriptionQuery = $this->pendingSubscriptionReferralQuery($staff->id);

        $serviceEarnings = $serviceQuery->sum('total_staff_payout') ?? 0;
        $patientRewardEarnings = $rewardQuery->sum('reward_amount') ?? 0;
        $subscriptionReferralEarnings = $subscriptionQuery->sum('referral_commission_amount') ?? 0;

        return [
            'service_request' => [
                'amount' => $serviceEarnings,
                'count' => $serviceQuery->count(),
            ],
            'patient_reward' => [
                'amount' => $patientRewardEarnings,
                'count' => $rewardQuery->count(),
            ],
            'staff_referral' => [
                'amount' => 0,
                'count' => 0,
                'meets_threshold' => false,
            ],
            'subscription_referral' => [
                'amount' => $subscriptionReferralEarnings,
                'count' => $subscriptionQuery->count(),
            ],
            'total' => $serviceEarnings + $patientRewardEarnings + $subscriptionReferralEarnings,
        ];
    }
}


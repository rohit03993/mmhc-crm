<?php

namespace App\Modules\Payments\Services;

use App\Models\Core\User;
use App\Modules\Incentives\Models\IncentiveLedger;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Referrals\Models\Referral;
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
                $query->where('verification_status', 'verified')
                    ->orWhereNull('verification_status');
            })
            ->where(function ($query) {
                $query->where('payment_processed', false)
                    ->orWhereNull('payment_processed');
            });
    }

    public function pendingSubscriptionReferralQuery(int $staffId)
    {
        return IncentiveLedger::query()
            ->where('staff_id', $staffId)
            ->where('source_type', IncentiveLedger::SOURCE_SUBSCRIPTION_SALE)
            ->where(function ($query) {
                $query->where('payment_settled', false)
                    ->orWhereNull('payment_settled');
            });
    }

    public function pendingStaffReferralQuery(int $staffId)
    {
        return IncentiveLedger::query()
            ->where('staff_id', $staffId)
            ->where('source_type', IncentiveLedger::SOURCE_REFERRAL)
            ->where(function ($query) {
                $query->where('payment_settled', false)
                    ->orWhereNull('payment_settled');
            });
    }

    public function calculatePendingPayments(User $staff): array
    {
        $serviceQuery = $this->pendingServiceRequestQuery($staff->id);
        $rewardQuery = $this->pendingPatientRewardQuery($staff->id);
        $staffReferralQuery = $this->pendingStaffReferralQuery($staff->id);
        $subscriptionQuery = $this->pendingSubscriptionReferralQuery($staff->id);

        $serviceEarnings = $serviceQuery->sum('total_staff_payout') ?? 0;
        $patientRewardEarnings = $rewardQuery->sum('reward_amount') ?? 0;
        $staffReferralEarnings = (float) ($staffReferralQuery->sum('final_amount') ?? 0);
        $subscriptionReferralEarnings = (float) ($subscriptionQuery->sum('final_amount') ?? 0);
        $legacyReferralIds = IncentiveLedger::query()
            ->where('staff_id', $staff->id)
            ->where('source_type', IncentiveLedger::SOURCE_REFERRAL)
            ->pluck('source_id');
        $legacyReferralQ = Referral::query()
            ->where('referrer_id', $staff->id)
            ->where('status', 'completed')
            ->where(function ($q) {
                $q->where('payment_processed', false)->orWhereNull('payment_processed');
            });
        if ($legacyReferralIds->isNotEmpty()) {
            $legacyReferralQ->whereNotIn('id', $legacyReferralIds);
        }
        $staffReferralEarnings += (float) $legacyReferralQ->sum('reward_amount');
        $staffReferralCount = $staffReferralQuery->count() + $legacyReferralQ->count();

        $ledgerSubIds = IncentiveLedger::query()
            ->where('staff_id', $staff->id)
            ->where('source_type', IncentiveLedger::SOURCE_SUBSCRIPTION_SALE)
            ->pluck('source_id');
        $legacySubQ = Subscription::query()
            ->where('referrer_id', $staff->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('referral_payment_processed', false)->orWhereNull('referral_payment_processed');
            });
        if ($ledgerSubIds->isNotEmpty()) {
            $legacySubQ->whereNotIn('id', $ledgerSubIds);
        }
        $subscriptionReferralEarnings += (float) $legacySubQ->sum('referral_commission_amount');
        $subscriptionReferralCount = $subscriptionQuery->count() + $legacySubQ->count();

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
                'amount' => $staffReferralEarnings,
                'count' => $staffReferralCount,
                'meets_threshold' => $staffReferralEarnings > 0,
            ],
            'subscription_referral' => [
                'amount' => $subscriptionReferralEarnings,
                'count' => $subscriptionReferralCount,
            ],
            'total' => $serviceEarnings + $patientRewardEarnings + $staffReferralEarnings + $subscriptionReferralEarnings,
        ];
    }
}

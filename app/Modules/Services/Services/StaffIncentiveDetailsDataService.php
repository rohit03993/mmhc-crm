<?php

namespace App\Modules\Services\Services;

use App\Models\Core\User;
use App\Modules\Incentives\Models\IncentiveLedger;
use App\Modules\Payments\Services\StaffPayoutService;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Rewards\Models\CaregiverReward;

class StaffIncentiveDetailsDataService
{
    public function __construct(
        private StaffPayoutService $staffPayoutService
    ) {}

    /**
     * Build all view data for staff incentive details (standalone page or profile embed).
     *
     * @return array<string, mixed>
     */
    public function buildForStaff(User $targetStaff): array
    {
        $staffMobileVerified = $targetStaff->hasVerifiedPhone();
        $heldEarningsDueToUnverifiedMobile = $this->staffPayoutService
            ->calculateHeldDueToUnverifiedMobile($targetStaff);

        $serviceLedgerBaseQuery = IncentiveLedger::query()
            ->where('staff_id', $targetStaff->id)
            ->where('source_type', IncentiveLedger::SOURCE_SERVICE_REQUEST);

        $serviceAggregate = (clone $serviceLedgerBaseQuery)
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('COALESCE(SUM(base_amount), 0) as base_total')
            ->selectRaw('COALESCE(SUM(final_amount), 0) as final_total')
            ->selectRaw('COALESCE(AVG(growth_percent), 0) as avg_growth_percent')
            ->selectRaw('COALESCE(AVG(dta_percent), 0) as avg_dta_percent')
            ->selectRaw('COALESCE(MAX(service_count_at_event), 0) as latest_service_count')
            ->first();

        $serviceLedgers = (clone $serviceLedgerBaseQuery)
            ->with(['sourceServiceRequest.patient', 'sourceServiceRequest.serviceType'])
            ->orderByDesc('service_count_at_event')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'service_page')
            ->withQueryString();

        $subscriptionLedgerBaseQuery = IncentiveLedger::query()
            ->where('staff_id', $targetStaff->id)
            ->where('source_type', IncentiveLedger::SOURCE_SUBSCRIPTION_SALE);

        $subscriptionLedgerSourceIds = (clone $subscriptionLedgerBaseQuery)
            ->pluck('source_id')
            ->filter()
            ->values();

        $subscriptionLedgerTotalCount = (clone $subscriptionLedgerBaseQuery)->count();
        $subscriptionLedgerTotalAmount = (float) (clone $subscriptionLedgerBaseQuery)->sum('final_amount');

        $subscriptionLedgers = (clone $subscriptionLedgerBaseQuery)
            ->with(['sourceSubscription.user', 'sourceSubscription.plan'])
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'subscription_page')
            ->withQueryString();

        $staffReferralsBaseQuery = Referral::query()
            ->where('referrer_id', $targetStaff->id)
            ->referralMobileOtpVerified()
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->orderByDesc('id');

        $staffReferralTotalCount = (clone $staffReferralsBaseQuery)->count();

        $staffReferrals = (clone $staffReferralsBaseQuery)
            ->with('referred')
            ->paginate(10, ['*'], 'staff_referral_page')
            ->withQueryString();

        $patientRewardBase = CaregiverReward::query()->where('user_id', $targetStaff->id);
        $verifiedPatientRewardBase = (clone $patientRewardBase)->verified();

        $rawPatientRewardsTotalAmount = (float) (clone $verifiedPatientRewardBase)->sum('reward_amount');
        $rawPatientRewardsPendingAmount = (float) (clone $verifiedPatientRewardBase)
            ->where(function ($query) {
                $query->where('payment_processed', false)
                    ->orWhereNull('payment_processed');
            })
            ->sum('reward_amount');

        $patientRewardsTotalAmount = $staffMobileVerified ? $rawPatientRewardsTotalAmount : 0.0;
        $patientRewardsPendingAmount = $staffMobileVerified ? $rawPatientRewardsPendingAmount : 0.0;

        $patientRewards = (clone $patientRewardBase)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'reward_page')
            ->withQueryString();

        $legacySubscriptionBaseQuery = Subscription::query()
            ->where('referrer_id', $targetStaff->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('referral_payment_processed', false)
                    ->orWhereNull('referral_payment_processed');
            });

        if ($subscriptionLedgerSourceIds->isNotEmpty()) {
            $legacySubscriptionBaseQuery->whereNotIn('id', $subscriptionLedgerSourceIds);
        }

        $legacySubscriptionTotalCount = (clone $legacySubscriptionBaseQuery)->count();
        $legacySubscriptionTotalAmount = (float) (clone $legacySubscriptionBaseQuery)->sum('referral_commission_amount');

        $legacySubscriptions = (clone $legacySubscriptionBaseQuery)
            ->with(['user', 'plan'])
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'legacy_subscription_page')
            ->withQueryString();

        $ledgerNonServiceTotal = (float) IncentiveLedger::query()
            ->where('staff_id', $targetStaff->id)
            ->whereIn('source_type', [
                IncentiveLedger::SOURCE_SUBSCRIPTION_SALE,
                IncentiveLedger::SOURCE_REFERRAL,
            ])
            ->sum('final_amount');

        $serviceSummary = [
            'count' => (int) ($serviceAggregate->total_count ?? 0),
            'base_total' => (float) ($serviceAggregate->base_total ?? 0),
            'final_total' => (float) ($serviceAggregate->final_total ?? 0),
            'avg_growth_percent' => round((float) ($serviceAggregate->avg_growth_percent ?? 0), 2),
            'avg_dta_percent' => round((float) ($serviceAggregate->avg_dta_percent ?? 0), 2),
            'latest_service_count' => (int) ($serviceAggregate->latest_service_count ?? 0),
            'ledger_grand_total' => (float) ($serviceAggregate->final_total ?? 0) + $ledgerNonServiceTotal,
        ];

        $rawSubscriptionSummaryAmount = $subscriptionLedgerTotalAmount + $legacySubscriptionTotalAmount;
        $subscriptionSummaryAmount = $staffMobileVerified ? $rawSubscriptionSummaryAmount : 0.0;

        $staffReferralBasePerReferral = 100;
        $staffReferralTotalBase = $staffReferralTotalCount * $staffReferralBasePerReferral;
        $staffReferralLedgerBaseQuery = IncentiveLedger::query()
            ->where('staff_id', $targetStaff->id)
            ->where('source_type', IncentiveLedger::SOURCE_REFERRAL);
        $staffReferralLedgerSourceIds = (clone $staffReferralLedgerBaseQuery)->pluck('source_id');
        $rawStaffReferralLedgerAmount = (float) (clone $staffReferralLedgerBaseQuery)->sum('final_amount');
        $rawLegacyStaffReferralAmount = (float) Referral::query()
            ->where('referrer_id', $targetStaff->id)
            ->referralMobileOtpVerified()
            ->where('status', 'completed')
            ->when($staffReferralLedgerSourceIds->isNotEmpty(), function ($q) use ($staffReferralLedgerSourceIds) {
                $q->whereNotIn('id', $staffReferralLedgerSourceIds);
            })
            ->sum('reward_amount');
        $rawStaffReferralTotalAmount = $rawStaffReferralLedgerAmount + $rawLegacyStaffReferralAmount;
        $staffReferralTotalAmount = $staffMobileVerified ? $rawStaffReferralTotalAmount : 0.0;

        $referralLedgerSettledBySourceId = IncentiveLedger::query()
            ->where('staff_id', $targetStaff->id)
            ->where('source_type', IncentiveLedger::SOURCE_REFERRAL)
            ->pluck('payment_settled', 'source_id');

        return [
            'targetStaff' => $targetStaff,
            'staffMobileVerified' => $staffMobileVerified,
            'heldEarningsDueToUnverifiedMobile' => $heldEarningsDueToUnverifiedMobile,
            'serviceLedgers' => $serviceLedgers,
            'subscriptionLedgers' => $subscriptionLedgers,
            'legacySubscriptions' => $legacySubscriptions,
            'staffReferrals' => $staffReferrals,
            'patientRewards' => $patientRewards,
            'serviceSummary' => $serviceSummary,
            'subscriptionSummaryAmount' => $subscriptionSummaryAmount,
            'rawSubscriptionSummaryAmount' => $rawSubscriptionSummaryAmount,
            'subscriptionSummaryCount' => $subscriptionLedgerTotalCount + $legacySubscriptionTotalCount,
            'staffReferralTotalCount' => $staffReferralTotalCount,
            'staffReferralBasePerReferral' => $staffReferralBasePerReferral,
            'staffReferralTotalBase' => $staffReferralTotalBase,
            'staffReferralTotalAmount' => $staffReferralTotalAmount,
            'rawStaffReferralTotalAmount' => $rawStaffReferralTotalAmount,
            'patientRewardsTotalAmount' => $patientRewardsTotalAmount,
            'patientRewardsPendingAmount' => $patientRewardsPendingAmount,
            'rawPatientRewardsTotalAmount' => $rawPatientRewardsTotalAmount,
            'rawPatientRewardsPendingAmount' => $rawPatientRewardsPendingAmount,
            'referralLedgerSettledBySourceId' => $referralLedgerSettledBySourceId,
            'combinedLedgerAndPatientRewards' => (float) ($serviceAggregate->final_total ?? 0)
                + ($staffMobileVerified ? $ledgerNonServiceTotal : 0.0)
                + $patientRewardsTotalAmount,
            'rawCombinedLedgerAndPatientRewards' => (float) ($serviceAggregate->final_total ?? 0)
                + $ledgerNonServiceTotal
                + ($rawPatientRewardsTotalAmount ?? 0),
        ];
    }
}

<?php

namespace App\Modules\Payments\Services;

use App\Models\Core\User;
use App\Modules\Incentives\Models\IncentiveLedger;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Rewards\Models\CaregiverReward;
use App\Modules\Services\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Builder;

class StaffPayoutService
{
    /**
     * Staff account mobile must be OTP-verified before any payout amounts accumulate.
     */
    public function staffMayAccumulatePayouts(User $staff): bool
    {
        return $staff->hasVerifiedPhone();
    }

    /**
     * @return array<string, array{amount: float|int, count: int, meets_threshold?: bool}>
     */
    public function emptyPendingPayments(): array
    {
        return [
            'service_request' => [
                'amount' => 0,
                'count' => 0,
            ],
            'patient_reward' => [
                'amount' => 0,
                'count' => 0,
            ],
            'staff_referral' => [
                'amount' => 0,
                'count' => 0,
                'meets_threshold' => false,
            ],
            'subscription_referral' => [
                'amount' => 0,
                'count' => 0,
            ],
            'total' => 0,
        ];
    }

    /**
     * Earnings already earned (patient OTP / referral OTP done) but not payable until account mobile is verified.
     *
     * @return array<string, mixed>|null null when mobile is already verified
     */
    public function calculateHeldDueToUnverifiedMobile(User $staff): ?array
    {
        if ($staff->hasVerifiedPhone()) {
            return null;
        }

        $payments = $this->summarizePendingPayments($staff->id);
        $verifiedRewardPoints = (int) CaregiverReward::query()
            ->where('user_id', $staff->id)
            ->verified()
            ->sum('reward_points');

        if ((float) $payments['total'] <= 0 && $verifiedRewardPoints <= 0) {
            return null;
        }

        return array_merge($payments, [
            'verified_reward_points' => $verifiedRewardPoints,
        ]);
    }

    public function basePendingServiceRequestQuery(int $staffId): Builder
    {
        return ServiceRequest::where('assigned_staff_id', $staffId)
            ->where('status', 'completed')
            ->whereNotNull('completion_verified_at')
            ->whereNotNull('admin_approved_at')
            ->whereNotNull('total_staff_payout')
            ->where('total_staff_payout', '>', 0)
            ->where(function ($query) {
                $query->where('staff_payment_processed', false)
                    ->orWhereNull('staff_payment_processed');
            });
    }

    public function pendingServiceRequestQuery(int $staffId): Builder
    {
        $staff = User::query()->find($staffId);
        if (! $staff || ! $this->staffMayAccumulatePayouts($staff)) {
            return ServiceRequest::query()->whereRaw('0 = 1');
        }

        return $this->basePendingServiceRequestQuery($staffId);
    }

    public function basePendingPatientRewardQuery(int $staffId): Builder
    {
        return CaregiverReward::where('user_id', $staffId)
            ->verified()
            ->where(function ($query) {
                $query->where('payment_processed', false)
                    ->orWhereNull('payment_processed');
            });
    }

    public function pendingPatientRewardQuery(int $staffId): Builder
    {
        $staff = User::query()->find($staffId);
        if (! $staff || ! $this->staffMayAccumulatePayouts($staff)) {
            return CaregiverReward::query()->whereRaw('0 = 1');
        }

        return $this->basePendingPatientRewardQuery($staffId);
    }

    public function basePendingSubscriptionReferralQuery(int $staffId): Builder
    {
        return IncentiveLedger::query()
            ->where('staff_id', $staffId)
            ->where('source_type', IncentiveLedger::SOURCE_SUBSCRIPTION_SALE)
            ->where(function ($query) {
                $query->where('payment_settled', false)
                    ->orWhereNull('payment_settled');
            });
    }

    public function pendingSubscriptionReferralQuery(int $staffId): Builder
    {
        $staff = User::query()->find($staffId);
        if (! $staff || ! $this->staffMayAccumulatePayouts($staff)) {
            return IncentiveLedger::query()->whereRaw('0 = 1');
        }

        return $this->basePendingSubscriptionReferralQuery($staffId);
    }

    public function basePendingStaffReferralQuery(int $staffId): Builder
    {
        return IncentiveLedger::query()
            ->where('staff_id', $staffId)
            ->where('source_type', IncentiveLedger::SOURCE_REFERRAL)
            ->where(function ($query) {
                $query->where('payment_settled', false)
                    ->orWhereNull('payment_settled');
            });
    }

    public function pendingStaffReferralQuery(int $staffId): Builder
    {
        $staff = User::query()->find($staffId);
        if (! $staff || ! $this->staffMayAccumulatePayouts($staff)) {
            return IncentiveLedger::query()->whereRaw('0 = 1');
        }

        return $this->basePendingStaffReferralQuery($staffId);
    }

    public function basePendingLegacyStaffReferralQuery(int $staffId): Builder
    {
        $ledgerReferralIds = IncentiveLedger::query()
            ->where('staff_id', $staffId)
            ->where('source_type', IncentiveLedger::SOURCE_REFERRAL)
            ->pluck('source_id');

        return Referral::query()
            ->where('referrer_id', $staffId)
            ->where('status', 'completed')
            ->referralMobileOtpVerified()
            ->where(function ($q) {
                $q->where('payment_processed', false)->orWhereNull('payment_processed');
            })
            ->when($ledgerReferralIds->isNotEmpty(), function ($query) use ($ledgerReferralIds) {
                $query->whereNotIn('id', $ledgerReferralIds);
            });
    }

    public function pendingLegacyStaffReferralQuery(int $staffId): Builder
    {
        $staff = User::query()->find($staffId);
        if (! $staff || ! $this->staffMayAccumulatePayouts($staff)) {
            return Referral::query()->whereRaw('0 = 1');
        }

        return $this->basePendingLegacyStaffReferralQuery($staffId);
    }

    public function calculatePendingPayments(User $staff): array
    {
        if (! $this->staffMayAccumulatePayouts($staff)) {
            return $this->emptyPendingPayments();
        }

        return $this->summarizePendingPayments($staff->id);
    }

    /**
     * @return array<string, mixed>
     */
    private function summarizePendingPayments(int $staffId): array
    {
        $serviceQuery = $this->basePendingServiceRequestQuery($staffId);
        $rewardQuery = $this->basePendingPatientRewardQuery($staffId);
        $staffReferralQuery = $this->basePendingStaffReferralQuery($staffId);
        $subscriptionQuery = $this->basePendingSubscriptionReferralQuery($staffId);
        $legacyReferralQ = $this->basePendingLegacyStaffReferralQuery($staffId);

        $serviceEarnings = $serviceQuery->sum('total_staff_payout') ?? 0;
        $patientRewardEarnings = $rewardQuery->sum('reward_amount') ?? 0;
        $staffReferralEarnings = (float) ($staffReferralQuery->sum('final_amount') ?? 0);
        $subscriptionReferralEarnings = (float) ($subscriptionQuery->sum('final_amount') ?? 0);
        $staffReferralEarnings += (float) $legacyReferralQ->sum('reward_amount');
        $staffReferralCount = $staffReferralQuery->count() + $legacyReferralQ->count();

        $ledgerSubIds = IncentiveLedger::query()
            ->where('staff_id', $staffId)
            ->where('source_type', IncentiveLedger::SOURCE_SUBSCRIPTION_SALE)
            ->pluck('source_id');
        $legacySubQ = Subscription::query()
            ->where('referrer_id', $staffId)
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

    /**
     * Active nurses/caregivers whose mobile is verified (amounts count toward dashboard pending).
     *
     * @return list<int>
     */
    public function verifiedPayableStaffIds(): array
    {
        return User::query()
            ->whereIn('role', ['nurse', 'caregiver'])
            ->where('is_active', true)
            ->whereNotNull('phone_verified_at')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function globalPendingServiceRequestQuery(): Builder
    {
        $staffIds = $this->verifiedPayableStaffIds();
        if ($staffIds === []) {
            return ServiceRequest::query()->whereRaw('0 = 1');
        }

        return ServiceRequest::query()
            ->whereIn('assigned_staff_id', $staffIds)
            ->where('status', 'completed')
            ->whereNotNull('completion_verified_at')
            ->whereNotNull('admin_approved_at')
            ->whereNotNull('total_staff_payout')
            ->where('total_staff_payout', '>', 0)
            ->where(function ($query) {
                $query->where('staff_payment_processed', false)
                    ->orWhereNull('staff_payment_processed');
            });
    }

    public function globalPendingPatientRewardQuery(): Builder
    {
        $staffIds = $this->verifiedPayableStaffIds();
        if ($staffIds === []) {
            return CaregiverReward::query()->whereRaw('0 = 1');
        }

        return CaregiverReward::query()
            ->whereIn('user_id', $staffIds)
            ->verified()
            ->where(function ($query) {
                $query->where('payment_processed', false)
                    ->orWhereNull('payment_processed');
            });
    }

    public function globalPendingStaffReferralLedgerQuery(): Builder
    {
        $staffIds = $this->verifiedPayableStaffIds();
        if ($staffIds === []) {
            return IncentiveLedger::query()->whereRaw('0 = 1');
        }

        return IncentiveLedger::query()
            ->whereIn('staff_id', $staffIds)
            ->where('source_type', IncentiveLedger::SOURCE_REFERRAL)
            ->where(function ($query) {
                $query->where('payment_settled', false)
                    ->orWhereNull('payment_settled');
            });
    }

    public function globalPendingLegacyStaffReferralQuery(): Builder
    {
        $staffIds = $this->verifiedPayableStaffIds();
        if ($staffIds === []) {
            return Referral::query()->whereRaw('0 = 1');
        }

        $ledgerReferralIds = IncentiveLedger::query()
            ->whereIn('staff_id', $staffIds)
            ->where('source_type', IncentiveLedger::SOURCE_REFERRAL)
            ->pluck('source_id');

        return Referral::query()
            ->whereIn('referrer_id', $staffIds)
            ->where('status', 'completed')
            ->referralMobileOtpVerified()
            ->where(function ($q) {
                $q->where('payment_processed', false)->orWhereNull('payment_processed');
            })
            ->when($ledgerReferralIds->isNotEmpty(), function ($query) use ($ledgerReferralIds) {
                $query->whereNotIn('id', $ledgerReferralIds);
            });
    }

    public function globalPendingSubscriptionReferralLedgerQuery(): Builder
    {
        $staffIds = $this->verifiedPayableStaffIds();
        if ($staffIds === []) {
            return IncentiveLedger::query()->whereRaw('0 = 1');
        }

        return IncentiveLedger::query()
            ->whereIn('staff_id', $staffIds)
            ->where('source_type', IncentiveLedger::SOURCE_SUBSCRIPTION_SALE)
            ->where(function ($query) {
                $query->where('payment_settled', false)
                    ->orWhereNull('payment_settled');
            });
    }

    public function globalPendingLegacySubscriptionReferralQuery(): Builder
    {
        $staffIds = $this->verifiedPayableStaffIds();
        if ($staffIds === []) {
            return Subscription::query()->whereRaw('0 = 1');
        }

        $ledgerSubIds = IncentiveLedger::query()
            ->whereIn('staff_id', $staffIds)
            ->where('source_type', IncentiveLedger::SOURCE_SUBSCRIPTION_SALE)
            ->pluck('source_id');

        return Subscription::query()
            ->whereIn('referrer_id', $staffIds)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('referral_payment_processed', false)->orWhereNull('referral_payment_processed');
            })
            ->when($ledgerSubIds->isNotEmpty(), function ($query) use ($ledgerSubIds) {
                $query->whereNotIn('id', $ledgerSubIds);
            });
    }

    /**
     * Sum pending amount for a category (matches dashboard payout breakdown).
     */
    public function sumGlobalPendingAmount(string $paymentType): float
    {
        return match ($paymentType) {
            'service_request' => (float) $this->globalPendingServiceRequestQuery()->sum('total_staff_payout'),
            'patient_reward' => (float) $this->globalPendingPatientRewardQuery()->sum('reward_amount'),
            'staff_referral' => (float) $this->globalPendingStaffReferralLedgerQuery()->sum('final_amount')
                + (float) $this->globalPendingLegacyStaffReferralQuery()->sum('reward_amount'),
            'subscription_referral' => (float) $this->globalPendingSubscriptionReferralLedgerQuery()->sum('final_amount')
                + (float) $this->globalPendingLegacySubscriptionReferralQuery()->sum('referral_commission_amount'),
            default => 0.0,
        };
    }
}

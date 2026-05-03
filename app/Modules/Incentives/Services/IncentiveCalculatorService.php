<?php

namespace App\Modules\Incentives\Services;

use App\Models\Core\User;
use App\Modules\Incentives\Models\IncentiveGrowthDtaSlab;
use App\Modules\Incentives\Models\IncentiveLedger;
use App\Modules\Incentives\Models\IncentiveRuleSet;
use App\Modules\Incentives\Models\IncentiveServiceRate;
use App\Modules\Incentives\Models\IncentiveSubscriptionRate;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Services\Models\ServiceType;

class IncentiveCalculatorService
{
    public function roundMoney(float $amount, int $decimals = 2): float
    {
        return round($amount, $decimals);
    }

    public function mapDurationToVisitKind(int $durationHours): string
    {
        return match (true) {
            $durationHours >= 24 => '24h',
            $durationHours === 12 => '12h',
            $durationHours === 8 => '8h',
            default => 'visit',
        };
    }

    public function resolveVisitKind(ServiceType $serviceType): string
    {
        if (! empty($serviceType->incentive_visit_kind)) {
            return $serviceType->incentive_visit_kind;
        }

        return $this->mapDurationToVisitKind((int) $serviceType->duration_hours);
    }

    public function resolveExperienceTier(User $staff): string
    {
        $t = $staff->experience_tier;
        if (in_array($t, ['1y', '3y', '5y_plus'], true)) {
            return $t;
        }

        return '1y';
    }

    public function findSlabRow(IncentiveRuleSet $ruleSet, int $serviceCount): ?IncentiveGrowthDtaSlab
    {
        return IncentiveGrowthDtaSlab::query()
            ->where('rule_set_id', $ruleSet->id)
            ->where('min_inclusive', '<=', $serviceCount)
            ->where(function ($q) use ($serviceCount) {
                $q->whereNull('max_exclusive')
                    ->orWhere('max_exclusive', '>', $serviceCount);
            })
            ->orderByDesc('min_inclusive')
            ->first();
    }

    /**
     * @return array{0: float, 1: float} growth and DtA as percentage numbers (e.g. 2.5 means 2.5%)
     */
    public function getGrowthDtaPercentages(IncentiveRuleSet $ruleSet, int $serviceCount): array
    {
        $row = $this->findSlabRow($ruleSet, $serviceCount);
        if (! $row) {
            return [0.0, 0.0];
        }

        return [(float) $row->growth_percent, (float) $row->dta_percent];
    }

    public function applyUniversalGrowthDta(
        IncentiveRuleSet $ruleSet,
        float $baseAmount,
        int $serviceCountAtEvent
    ): array {
        $decimals = (int) $ruleSet->round_decimals;
        [$gPct, $dPct] = $this->getGrowthDtaPercentages($ruleSet, $serviceCountAtEvent);
        $pre = $baseAmount * (1.0 + $gPct / 100.0) * (1.0 - $dPct / 100.0);

        return [
            'growth_percent' => $gPct,
            'dta_percent' => $dPct,
            'pre_adjustment_amount' => $this->roundMoney($pre, $decimals),
        ];
    }

    public function computeServiceBaseInr(
        IncentiveRuleSet $ruleSet,
        string $visitKind,
        string $experienceTier,
        bool $isSubscriberPatient,
        int $durationDays
    ): float {
        $unit = in_array($visitKind, ['24h', '12h', '8h'], true) ? 'day' : 'visit';
        $rate = IncentiveServiceRate::query()
            ->where('rule_set_id', $ruleSet->id)
            ->where('visit_kind', $visitKind)
            ->where('experience_tier', $experienceTier)
            ->where('is_subscriber_patient', $isSubscriberPatient)
            ->where('unit', $unit)
            ->first();

        if (! $rate) {
            return 0.0;
        }

        $mult = $unit === 'day' ? max(1, $durationDays) : 1;

        return $this->roundMoney((float) $rate->rate_per_unit * $mult, $ruleSet->round_decimals);
    }

    public function estimateProvisionalServicePayout(
        User $staff,
        ServiceType $serviceType,
        int $durationDays,
        bool $isSubscriberPatient
    ): float {
        $ruleSet = IncentiveRuleSet::currentActive();
        if (! $ruleSet) {
            return 0.0;
        }
        $visitKind = $this->resolveVisitKind($serviceType);
        $tier = $this->resolveExperienceTier($staff);

        return $this->computeServiceBaseInr($ruleSet, $visitKind, $tier, $isSubscriberPatient, $durationDays);
    }

    public function computeSubscriptionBaseInr(
        IncentiveRuleSet $ruleSet,
        string $paymentFrequency,
        float $planBaseAmount
    ): float {
        $row = IncentiveSubscriptionRate::query()
            ->where('rule_set_id', $ruleSet->id)
            ->where('payment_frequency', $paymentFrequency)
            ->first();

        if (! $row) {
            return 0.0;
        }

        $pct = (float) $row->commission_percent / 100.0;

        return $this->roundMoney($planBaseAmount * $pct, $ruleSet->round_decimals);
    }

    public function approvedServiceCountExcludingRequest(int $staffId, int $excludeServiceRequestId): int
    {
        return ServiceRequest::query()
            ->where('assigned_staff_id', $staffId)
            ->where('status', 'completed')
            ->whereNotNull('admin_approved_at')
            ->where('id', '!=', $excludeServiceRequestId)
            ->count();
    }

    public function approvedServiceCountForStaff(int $staffId): int
    {
        return ServiceRequest::query()
            ->where('assigned_staff_id', $staffId)
            ->where('status', 'completed')
            ->whereNotNull('admin_approved_at')
            ->count();
    }

    public function createOrUpdateServiceLedger(
        User $staff,
        ServiceRequest $request,
        bool $isSubscriberPatient
    ): IncentiveLedger {
        $ruleSet = IncentiveRuleSet::currentActive();
        if (! $ruleSet) {
            throw new \RuntimeException('No active incentive rule set.');
        }

        $request->load('serviceType');
        $serviceType = $request->serviceType;
        if (! $serviceType) {
            throw new \RuntimeException('Service type missing for request.');
        }

        $visitKind = $this->resolveVisitKind($serviceType);
        $tier = $this->resolveExperienceTier($staff);
        $base = $this->computeServiceBaseInr(
            $ruleSet,
            $visitKind,
            $tier,
            $isSubscriberPatient,
            (int) $request->duration_days
        );

        $atEvent = $this->approvedServiceCountExcludingRequest($staff->id, $request->id) + 1;
        $growthDta = $this->applyUniversalGrowthDta($ruleSet, $base, $atEvent);

        $ledger = IncentiveLedger::updateOrCreate(
            [
                'source_type' => IncentiveLedger::SOURCE_SERVICE_REQUEST,
                'source_id' => $request->id,
            ],
            [
                'rule_set_id' => $ruleSet->id,
                'staff_id' => $staff->id,
                'base_amount' => $base,
                'service_count_at_event' => $atEvent,
                'snapshot_visit_kind' => $visitKind,
                'snapshot_experience_tier' => $tier,
                'snapshot_subscriber_patient' => $isSubscriberPatient,
                'growth_percent' => $growthDta['growth_percent'],
                'dta_percent' => $growthDta['dta_percent'],
                'pre_adjustment_amount' => $growthDta['pre_adjustment_amount'],
                'adjustment_amount' => 0,
                'adjustment_reason' => null,
                'final_amount' => $growthDta['pre_adjustment_amount'],
            ]
        );

        $request->update([
            'total_staff_payout' => $ledger->final_amount,
        ]);

        return $ledger;
    }

    public function createOrUpdateSubscriptionSaleLedger(Subscription $subscription): ?IncentiveLedger
    {
        if (! $subscription->referrer_id) {
            return null;
        }
        $referrer = User::query()->find($subscription->referrer_id);
        if (! $referrer || (! $referrer->isNurse() && ! $referrer->isCaregiver())) {
            return null;
        }

        $ruleSet = IncentiveRuleSet::currentActive();
        if (! $ruleSet) {
            throw new \RuntimeException('No active incentive rule set.');
        }

        $baseAmount = (float) ($subscription->base_amount ?? 0);
        $baseCommission = $this->computeSubscriptionBaseInr(
            $ruleSet,
            $subscription->payment_frequency,
            $baseAmount
        );

        $atEvent = $this->approvedServiceCountForStaff($referrer->id);
        $growthDta = $this->applyUniversalGrowthDta($ruleSet, $baseCommission, $atEvent);
        $subRate = IncentiveSubscriptionRate::query()
            ->where('rule_set_id', $ruleSet->id)
            ->where('payment_frequency', $subscription->payment_frequency)
            ->value('commission_percent');

        $ledger = IncentiveLedger::updateOrCreate(
            [
                'source_type' => IncentiveLedger::SOURCE_SUBSCRIPTION_SALE,
                'source_id' => $subscription->id,
            ],
            [
                'rule_set_id' => $ruleSet->id,
                'staff_id' => $referrer->id,
                'base_amount' => $baseCommission,
                'service_count_at_event' => $atEvent,
                'snapshot_visit_kind' => null,
                'snapshot_experience_tier' => null,
                'snapshot_subscriber_patient' => null,
                'growth_percent' => $growthDta['growth_percent'],
                'dta_percent' => $growthDta['dta_percent'],
                'pre_adjustment_amount' => $growthDta['pre_adjustment_amount'],
                'adjustment_amount' => 0,
                'adjustment_reason' => null,
                'final_amount' => $growthDta['pre_adjustment_amount'],
            ]
        );

        $subscription->update([
            'referral_base_amount' => $baseCommission,
            'referral_growth_percent' => $growthDta['growth_percent'],
            'referral_dta_percent' => $growthDta['dta_percent'],
            'referral_commission_amount' => $ledger->final_amount,
        ]);

        return $ledger;
    }

    /**
     * Placeholder for Phase 5 — record a one-off referral incentive in the ledger.
     */
    public function createReferralLedger(
        User $referrer,
        int $referralId,
        float $baseInr
    ): IncentiveLedger {
        if (! $referrer->isStaff()) {
            throw new \InvalidArgumentException('Referrer must be staff.');
        }
        $ruleSet = IncentiveRuleSet::currentActive();
        if (! $ruleSet) {
            throw new \RuntimeException('No active incentive rule set.');
        }
        $atEvent = $this->approvedServiceCountForStaff($referrer->id);
        $growthDta = $this->applyUniversalGrowthDta($ruleSet, $baseInr, $atEvent);

        return IncentiveLedger::updateOrCreate(
            [
                'source_type' => IncentiveLedger::SOURCE_REFERRAL,
                'source_id' => $referralId,
            ],
            [
                'rule_set_id' => $ruleSet->id,
                'staff_id' => $referrer->id,
                'base_amount' => $baseInr,
                'service_count_at_event' => $atEvent,
                'snapshot_visit_kind' => null,
                'snapshot_experience_tier' => $this->resolveExperienceTier($referrer),
                'snapshot_subscriber_patient' => null,
                'growth_percent' => $growthDta['growth_percent'],
                'dta_percent' => $growthDta['dta_percent'],
                'pre_adjustment_amount' => $growthDta['pre_adjustment_amount'],
                'adjustment_amount' => 0,
                'adjustment_reason' => null,
                'final_amount' => $growthDta['pre_adjustment_amount'],
            ]
        );
    }
}

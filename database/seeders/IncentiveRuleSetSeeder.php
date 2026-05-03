<?php

namespace Database\Seeders;

use App\Models\Core\User;
use App\Modules\Incentives\Models\IncentiveGrowthDtaSlab;
use App\Modules\Incentives\Models\IncentiveLedger;
use App\Modules\Incentives\Models\IncentiveRuleSet;
use App\Modules\Incentives\Models\IncentiveServiceRate;
use App\Modules\Incentives\Models\IncentiveSubscriptionRate;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Services\Models\ServiceType;
use Illuminate\Database\Seeder;

class IncentiveRuleSetSeeder extends Seeder
{
    public function run(): void
    {
        $rs = IncentiveRuleSet::query()->updateOrCreate(
            ['code' => 'v1'],
            [
                'name' => 'Default v1 (PDF)',
                'effective_from' => '2020-01-01',
                'effective_to' => null,
                'is_active' => true,
                'round_decimals' => 2,
            ]
        );

        $sort = 0;
        // Ranges are half-open: count >= min_inclusive and (max_exclusive is null OR count < max_exclusive)
        $slabs = [
            [0, 50, 0, 0],
            [50, 101, 2.5, 1],
            [100, 501, 5, 2],
            [500, 1001, 10, 2.5],
            [1000, 3001, 15, 3],
            [3000, 6001, 20, 3.5],
            [6000, 10001, 25, 4],
            [10000, null, 25, 5],
        ];
        IncentiveGrowthDtaSlab::query()->where('rule_set_id', $rs->id)->delete();
        foreach ($slabs as $row) {
            IncentiveGrowthDtaSlab::create([
                'rule_set_id' => $rs->id,
                'min_inclusive' => $row[0],
                'max_exclusive' => $row[1],
                'growth_percent' => $row[2],
                'dta_percent' => $row[3],
                'sort_order' => $sort++,
            ]);
        }

        IncentiveSubscriptionRate::query()->where('rule_set_id', $rs->id)->delete();
        $sub = [
            ['monthly', 0],
            ['half_yearly', 6],
            ['annually', 10],
            ['full_payment', 15],
        ];
        foreach ($sub as $s) {
            IncentiveSubscriptionRate::create([
                'rule_set_id' => $rs->id,
                'payment_frequency' => $s[0],
                'commission_percent' => $s[1],
            ]);
        }

        IncentiveServiceRate::query()->where('rule_set_id', $rs->id)->delete();

        $non = [
            '24h' => 2000,
            '12h' => 1200,
            '8h' => 800,
            'visit' => 500,
        ];
        foreach ($non as $vk => $rate) {
            $unit = $vk === 'visit' ? 'visit' : 'day';
            foreach (['1y', '3y', '5y_plus'] as $tier) {
                IncentiveServiceRate::create([
                    'rule_set_id' => $rs->id,
                    'visit_kind' => $vk,
                    'experience_tier' => $tier,
                    'is_subscriber_patient' => false,
                    'unit' => $unit,
                    'rate_per_unit' => $rate,
                ]);
            }
        }

        $subRates = [
            '24h' => ['1y' => 1400, '3y' => 1600, '5y_plus' => 2000],
            '12h' => ['1y' => 800, '3y' => 1200, '5y_plus' => 1500],
            '8h' => ['1y' => 500, '3y' => 800, '5y_plus' => 1200],
            'visit' => ['1y' => 250, '3y' => 500, '5y_plus' => 800],
        ];
        foreach ($subRates as $vk => $byTier) {
            $unit = $vk === 'visit' ? 'visit' : 'day';
            foreach ($byTier as $tier => $rate) {
                IncentiveServiceRate::create([
                    'rule_set_id' => $rs->id,
                    'visit_kind' => $vk,
                    'experience_tier' => $tier,
                    'is_subscriber_patient' => true,
                    'unit' => $unit,
                    'rate_per_unit' => $rate,
                ]);
            }
        }

        foreach (ServiceType::all() as $st) {
            $kind = match (true) {
                (int) $st->duration_hours >= 24 => '24h',
                (int) $st->duration_hours === 12 => '12h',
                (int) $st->duration_hours === 8 => '8h',
                default => 'visit',
            };
            $st->update(['incentive_visit_kind' => $kind]);
        }

        $this->backfillSubscriptionLedgers($rs);
    }

    private function backfillSubscriptionLedgers(IncentiveRuleSet $rs): void
    {
        Subscription::query()
            ->whereIn('status', ['active', 'pending'])
            ->whereNotNull('referrer_id')
            ->where('referral_commission_amount', '>', 0)
            ->whereHas('referrer', function ($q) {
                $q->whereIn('role', ['nurse', 'caregiver']);
            })
            ->orderBy('id')
            ->each(function (Subscription $sub) use ($rs) {
                if (IncentiveLedger::query()
                    ->where('source_type', IncentiveLedger::SOURCE_SUBSCRIPTION_SALE)
                    ->where('source_id', $sub->id)
                    ->exists()) {
                    return;
                }
                $referrer = User::find($sub->referrer_id);
                if (! $referrer) {
                    return;
                }
                $amt = (float) $sub->referral_commission_amount;
                IncentiveLedger::create([
                    'rule_set_id' => $rs->id,
                    'staff_id' => $referrer->id,
                    'source_type' => IncentiveLedger::SOURCE_SUBSCRIPTION_SALE,
                    'source_id' => $sub->id,
                    'base_amount' => $amt,
                    'service_count_at_event' => 0,
                    'snapshot_visit_kind' => null,
                    'snapshot_experience_tier' => null,
                    'snapshot_subscriber_patient' => null,
                    'growth_percent' => 0,
                    'dta_percent' => 0,
                    'pre_adjustment_amount' => $amt,
                    'adjustment_amount' => 0,
                    'adjustment_reason' => 'legacy_backfill',
                    'final_amount' => $amt,
                    'payment_settled' => (bool) $sub->referral_payment_processed,
                    'settled_at' => $sub->referral_payment_processed_at,
                ]);
            });
    }
}

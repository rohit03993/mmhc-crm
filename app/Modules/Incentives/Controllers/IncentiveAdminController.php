<?php

namespace App\Modules\Incentives\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Incentives\Models\IncentiveGrowthDtaSlab;
use App\Modules\Incentives\Models\IncentiveLedger;
use App\Modules\Incentives\Models\IncentiveRuleSet;
use App\Modules\Incentives\Models\IncentiveServiceRate;
use App\Modules\Incentives\Models\IncentiveSubscriptionRate;

class IncentiveAdminController extends Controller
{
    public function preview()
    {
        $ruleSet = IncentiveRuleSet::currentActive();
        if (! $ruleSet) {
            return view('incentives::admin.preview', [
                'ruleSet' => null,
                'slabs' => collect(),
                'serviceRates' => collect(),
                'subscriptionRates' => collect(),
                'ledgerEntryCount' => IncentiveLedger::query()->count(),
            ]);
        }

        return view('incentives::admin.preview', [
            'ruleSet' => $ruleSet,
            'slabs' => IncentiveGrowthDtaSlab::where('rule_set_id', $ruleSet->id)->orderBy('sort_order')->get(),
            'serviceRates' => IncentiveServiceRate::where('rule_set_id', $ruleSet->id)
                ->orderBy('visit_kind')
                ->orderBy('experience_tier')
                ->get(),
            'subscriptionRates' => IncentiveSubscriptionRate::where('rule_set_id', $ruleSet->id)
                ->orderBy('payment_frequency')
                ->get(),
            'ledgerEntryCount' => IncentiveLedger::query()->count(),
        ]);
    }
}

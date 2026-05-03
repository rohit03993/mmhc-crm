<?php

namespace App\Modules\Incentives\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncentiveGrowthDtaSlab extends Model
{
    protected $table = 'incentive_growth_dta_slabs';

    protected $fillable = [
        'rule_set_id', 'min_inclusive', 'max_exclusive', 'growth_percent', 'dta_percent', 'sort_order',
    ];

    protected $casts = [
        'min_inclusive' => 'integer',
        'max_exclusive' => 'integer',
        'growth_percent' => 'decimal:4',
        'dta_percent' => 'decimal:4',
        'sort_order' => 'integer',
    ];

    public function ruleSet(): BelongsTo
    {
        return $this->belongsTo(IncentiveRuleSet::class, 'rule_set_id');
    }
}

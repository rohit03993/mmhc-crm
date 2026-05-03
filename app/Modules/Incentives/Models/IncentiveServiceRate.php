<?php

namespace App\Modules\Incentives\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncentiveServiceRate extends Model
{
    protected $table = 'incentive_service_rates';

    protected $fillable = [
        'rule_set_id', 'visit_kind', 'experience_tier', 'is_subscriber_patient', 'unit', 'rate_per_unit',
    ];

    protected $casts = [
        'is_subscriber_patient' => 'boolean',
        'rate_per_unit' => 'decimal:2',
    ];

    public function ruleSet(): BelongsTo
    {
        return $this->belongsTo(IncentiveRuleSet::class, 'rule_set_id');
    }
}

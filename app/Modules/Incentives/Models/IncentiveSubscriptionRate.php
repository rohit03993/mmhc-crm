<?php

namespace App\Modules\Incentives\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncentiveSubscriptionRate extends Model
{
    protected $table = 'incentive_subscription_rates';

    protected $fillable = [
        'rule_set_id', 'payment_frequency', 'commission_percent',
    ];

    protected $casts = [
        'commission_percent' => 'decimal:4',
    ];

    public function ruleSet(): BelongsTo
    {
        return $this->belongsTo(IncentiveRuleSet::class, 'rule_set_id');
    }
}

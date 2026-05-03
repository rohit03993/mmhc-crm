<?php

namespace App\Modules\Incentives\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncentiveRuleSet extends Model
{
    protected $fillable = [
        'code', 'name', 'effective_from', 'effective_to', 'is_active', 'round_decimals',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
        'round_decimals' => 'integer',
    ];

    public function growthDtaSlabs(): HasMany
    {
        return $this->hasMany(IncentiveGrowthDtaSlab::class, 'rule_set_id')->orderBy('sort_order');
    }

    public function serviceRates(): HasMany
    {
        return $this->hasMany(IncentiveServiceRate::class, 'rule_set_id');
    }

    public function subscriptionRates(): HasMany
    {
        return $this->hasMany(IncentiveSubscriptionRate::class, 'rule_set_id');
    }

    public static function currentActive(): ?self
    {
        $today = now()->toDateString();

        return static::query()
            ->where('is_active', true)
            ->where('effective_from', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $today);
            })
            ->orderByDesc('effective_from')
            ->first();
    }
}

<?php

namespace App\Modules\Plans\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionCoupon extends Model
{
    protected $fillable = [
        'code',
        'description',
        'audience',
        'discount_type',
        'discount_value',
        'max_uses',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'subscription_coupon_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function discountLabel(): string
    {
        if ($this->discount_type === 'percent') {
            return rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.').'% off';
        }

        return '₹'.number_format((float) $this->discount_value, 0).' off';
    }

    public function audienceLabel(): string
    {
        return match ($this->audience) {
            'student' => 'Students',
            'patient' => 'Patients',
            default => 'All',
        };
    }
}

<?php

namespace App\Modules\Plans\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'currency',
        'duration_days',
        'features',
        'is_active',
        'is_popular',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the subscriptions for this plan.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Scope for active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for popular plans
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * Scope ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }

    /**
     * Get duration in human readable format
     */
    public function getDurationTextAttribute()
    {
        if ($this->duration_days == 1) {
            return '1 Day';
        } elseif ($this->duration_days < 30) {
            return $this->duration_days . ' Days';
        } elseif ($this->duration_days == 30) {
            return '1 Month';
        } elseif ($this->duration_days < 365) {
            return floor($this->duration_days / 30) . ' Months';
        } else {
            return floor($this->duration_days / 365) . ' Year(s)';
        }
    }

    /**
     * Get active subscriptions count
     */
    public function getActiveSubscriptionsCountAttribute()
    {
        return $this->subscriptions()
                    ->where('status', 'active')
                    ->count();
    }

    /**
     * Get total revenue from this plan
     */
    public function getTotalRevenueAttribute()
    {
        return $this->subscriptions()
                    ->where('status', 'active')
                    ->with('payments')
                    ->get()
                    ->sum(function ($subscription) {
                        return $subscription->payments->sum('amount');
                    });
    }
}

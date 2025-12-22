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
        'monthly_price',
        'members_included',
        'payment_options',
        'currency',
        'duration_days',
        'features',
        'icon_class',
        'color_theme',
        'popular_label',
        'button_text',
        'button_link',
        'is_active',
        'is_popular',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'payment_options' => 'array',
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
     * Get formatted price for display
     */
    public function getFormattedPriceAttribute()
    {
        // Use monthly_price if available, otherwise use price
        $price = $this->monthly_price ?? $this->price;
        return '₹' . number_format($price, 0);
    }

    /**
     * Get duration in readable format
     */
    public function getDurationTextAttribute()
    {
        if ($this->duration_days >= 365) {
            $years = round($this->duration_days / 365, 1);
            return $years == 1 ? '/year' : "/{$years} years";
        } elseif ($this->duration_days >= 30) {
            $months = round($this->duration_days / 30);
            return $months == 1 ? '/month' : "/{$months} months";
        } else {
            return $this->duration_days == 1 ? '/day' : "/{$this->duration_days} days";
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

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
        'slug',
        'audience',
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

    public function scopeForHealthcareAudience($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('audience')->orWhere('audience', 'healthcare');
        });
    }

    public function scopeForStudentAudience($query)
    {
        return $query->where('audience', 'student');
    }

    public function isStudentPlan(): bool
    {
        return $this->audience === 'student';
    }

    /**
     * Payment terms shown at checkout: 6 months, 1 year, 3 years (no monthly).
     */
    public function checkoutPaymentOptions(): array
    {
        $stored = is_array($this->payment_options) ? $this->payment_options : [];
        $monthly = (float) ($this->monthly_price ?? $this->price ?? 0);

        $terms = [
            'half_yearly' => [
                'label' => '6 Months',
                'description' => 'Pay 6 months. Coverage 6 months. No extra years.',
                'months' => 6,
                'payable_years' => 0.5,
                'care_benefits_years' => 0,
            ],
            'annually' => [
                'label' => '1 Year',
                'description' => 'Pay 12 months. Five consecutive years unlock 10 years of service.',
                'months' => 12,
                'payable_years' => 5,
                'care_benefits_years' => 5,
            ],
            'full_payment' => [
                'label' => '3 Years',
                'description' => 'Pay 36 months once. Get 10 years of service (7 extra years).',
                'months' => 36,
                'payable_years' => 3,
                'care_benefits_years' => 7,
            ],
        ];

        $out = [];
        foreach ($terms as $frequency => $meta) {
            $existing = $stored[$frequency] ?? [];
            $price = isset($existing['price'])
                ? (float) $existing['price']
                : round($monthly * $meta['months'], 2);

            $out[$frequency] = array_merge($existing, [
                'label' => $meta['label'],
                'description' => $meta['description'],
                'price' => $price,
                'payable_years' => $existing['payable_years'] ?? $meta['payable_years'],
                'care_benefits_years' => $existing['care_benefits_years'] ?? $meta['care_benefits_years'],
            ]);
        }

        return $out;
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

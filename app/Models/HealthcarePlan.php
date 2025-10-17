<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthcarePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'currency',
        'duration_days',
        'features',
        'icon_class',
        'color_theme',
        'is_popular',
        'popular_label',
        'is_active',
        'sort_order',
        'button_text',
        'button_link',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return '₹' . number_format($this->price);
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
            return "/{$this->duration_days} days";
        }
    }

    /**
     * Get all active plans ordered by sort_order
     */
    public static function getActivePlans()
    {
        return self::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get popular plans
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * Get active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

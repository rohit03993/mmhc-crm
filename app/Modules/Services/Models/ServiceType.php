<?php

namespace App\Modules\Services\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'duration_hours',
        'patient_charge',
        'nurse_payout',
        'caregiver_payout',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'patient_charge' => 'decimal:2',
        'nurse_payout' => 'decimal:2',
        'caregiver_payout' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get formatted patient charge
     */
    public function getFormattedPatientChargeAttribute()
    {
        return '₹' . number_format($this->patient_charge);
    }

    /**
     * Get formatted nurse payout
     */
    public function getFormattedNursePayoutAttribute()
    {
        return '₹' . number_format($this->nurse_payout);
    }

    /**
     * Get formatted caregiver payout
     */
    public function getFormattedCaregiverPayoutAttribute()
    {
        return '₹' . number_format($this->caregiver_payout);
    }

    /**
     * Get platform profit (patient charge - staff payout)
     */
    public function getPlatformProfitAttribute()
    {
        return $this->patient_charge - max($this->nurse_payout, $this->caregiver_payout);
    }

    /**
     * Get formatted platform profit
     */
    public function getFormattedPlatformProfitAttribute()
    {
        return '₹' . number_format($this->platform_profit);
    }

    /**
     * Scope for active service types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered service types
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('duration_hours', 'desc');
    }

    /**
     * Get all active service types
     */
    public static function getActiveServiceTypes()
    {
        return self::active()->ordered()->get();
    }
}

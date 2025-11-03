<?php

namespace App\Modules\Services\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Core\User;

class DailyService extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'staff_id',
        'service_date',
        'start_time',
        'end_time',
        'patient_charge',
        'staff_payout',
        'platform_profit',
        'status', // 'scheduled', 'in_progress', 'completed', 'cancelled'
        'notes',
        'patient_feedback',
        'staff_notes',
        'completed_at',
    ];

    protected $casts = [
        'service_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'completed_at' => 'datetime',
        'patient_charge' => 'decimal:2',
        'staff_payout' => 'decimal:2',
        'platform_profit' => 'decimal:2',
    ];

    /**
     * Get the service request
     */
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Get the staff member
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Get formatted patient charge
     */
    public function getFormattedPatientChargeAttribute()
    {
        return '₹' . number_format($this->patient_charge);
    }

    /**
     * Get formatted staff payout
     */
    public function getFormattedStaffPayoutAttribute()
    {
        return '₹' . number_format($this->staff_payout);
    }

    /**
     * Get formatted platform profit
     */
    public function getFormattedPlatformProfitAttribute()
    {
        return '₹' . number_format($this->platform_profit);
    }

    /**
     * Check if service is scheduled
     */
    public function isScheduled()
    {
        return $this->status === 'scheduled';
    }

    /**
     * Check if service is in progress
     */
    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if service is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Scope for scheduled services
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope for in progress services
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for completed services
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for services by date
     */
    public function scopeByDate($query, $date)
    {
        return $query->where('service_date', $date);
    }

    /**
     * Scope for services by staff
     */
    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }
}

<?php

namespace App\Modules\Services\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Core\User;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'service_type_id',
        'preferred_staff_type', // 'nurse', 'caregiver', 'any'
        'start_date',
        'end_date',
        'duration_days',
        'total_amount',
        'total_staff_payout',
        'prepaid_amount',
        'payment_status',
        'status', // 'pending', 'assigned', 'in_progress', 'completed', 'cancelled'
        'notes',
        'special_requirements',
        'location',
        'contact_person',
        'contact_phone',
        'assigned_staff_id',
        'assigned_at',
        'started_at',
        'completed_at',
        'admin_approved_at',
        'approved_by',
        'payment_processed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'admin_approved_at' => 'datetime',
        'payment_processed_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'total_staff_payout' => 'decimal:2',
        'prepaid_amount' => 'decimal:2',
    ];

    /**
     * Get the patient who requested the service
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Get the assigned staff member
     */
    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    /**
     * Get the admin who approved the payment
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if service is approved by admin
     */
    public function isApprovedByAdmin()
    {
        return !is_null($this->admin_approved_at);
    }

    /**
     * Check if payment is processed
     */
    public function isPaymentProcessed()
    {
        return !is_null($this->payment_processed_at);
    }

    /**
     * CRITICAL FIX #5: Valid status transitions state machine
     */
    private static $validTransitions = [
        'pending' => ['assigned', 'cancelled'],
        'assigned' => ['in_progress', 'cancelled'],
        'in_progress' => ['completed', 'cancelled'],
        'completed' => [], // Terminal state - cannot change
        'cancelled' => [], // Terminal state - cannot change
    ];

    /**
     * Check if service can transition to new status
     */
    public function canTransitionTo($newStatus)
    {
        $allowedStatuses = self::$validTransitions[$this->status] ?? [];
        return in_array($newStatus, $allowedStatuses);
    }

    /**
     * Get valid next statuses for current state
     */
    public function getValidNextStatuses()
    {
        return self::$validTransitions[$this->status] ?? [];
    }

    /**
     * Get the service type
     */
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    /**
     * Get daily service records
     */
    public function dailyServices()
    {
        return $this->hasMany(DailyService::class);
    }

    /**
     * Calculate total amount based on duration and service type
     */
    public function calculateTotalAmount()
    {
        if ($this->serviceType && $this->duration_days) {
            return $this->serviceType->patient_charge * $this->duration_days;
        }
        return 0;
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAmountAttribute()
    {
        return '₹' . number_format($this->total_amount);
    }

    /**
     * Check if request is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is assigned
     */
    public function isAssigned()
    {
        return $this->status === 'assigned';
    }

    /**
     * Check if request is in progress
     */
    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if request is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for assigned requests
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    /**
     * Scope for in progress requests
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for completed requests
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}

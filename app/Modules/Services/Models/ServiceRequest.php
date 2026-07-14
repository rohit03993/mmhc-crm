<?php

namespace App\Modules\Services\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'service_type_id',
        'preferred_staff_type', // 'nurse', 'caregiver', 'any'
        'preferred_staff_id', // Specific staff member patient selected
        'start_date',
        'end_date',
        'duration_days',
        'total_amount',
        'total_staff_payout',
        'prepaid_amount',
        'payment_status',
        'payment_provider',
        'gateway_status',
        'gateway_payload',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'razorpay_event_id',
        'visit_paid_at',
        'status', // 'pending', 'pending_approval', 'assigned', 'in_progress', 'completed', 'cancelled'
        'notes',
        'special_requirements',
        'location',
        'contact_person',
        'contact_phone',
        'assigned_staff_id',
        'assigned_at',
        'staff_approved_at',
        'staff_rejected_at',
        'staff_rejection_reason',
        'started_at',
        'completed_at',
        'admin_approved_at',
        'approved_by',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'payment_processed_at',
        'staff_payment_processed',
        'staff_payment_processed_at',
        'completion_otp_hash',
        'completion_otp_expires_at',
        'completion_otp_attempts',
        'completion_otp_channel',
        'completion_otp_sent_to',
        'completion_otp_sent_at',
        'completion_verified_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'assigned_at' => 'datetime',
        'staff_approved_at' => 'datetime',
        'staff_rejected_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'admin_approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'payment_processed_at' => 'datetime',
        'staff_payment_processed_at' => 'datetime',
        'staff_payment_processed' => 'boolean',
        'completion_otp_expires_at' => 'datetime',
        'completion_otp_sent_at' => 'datetime',
        'completion_verified_at' => 'datetime',
        'completion_otp_attempts' => 'integer',
        'total_amount' => 'decimal:2',
        'total_staff_payout' => 'decimal:2',
        'prepaid_amount' => 'decimal:2',
        'gateway_payload' => 'array',
        'visit_paid_at' => 'datetime',
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
     * Get the preferred staff member (selected by patient)
     */
    public function preferredStaff()
    {
        return $this->belongsTo(User::class, 'preferred_staff_id');
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
        return ! is_null($this->admin_approved_at);
    }

    /**
     * Check if payment is processed
     */
    public function isPaymentProcessed()
    {
        return ! is_null($this->payment_processed_at);
    }

    /**
     * Legacy: amount not recorded as prepaid. Product model is plan-free or full per-visit fee at booking — not used in patient UI.
     */
    public function balanceDue(): float
    {
        $total = (float) $this->total_amount;
        if ($total <= 0) {
            return 0.0;
        }

        return max(0.0, round($total - (float) $this->prepaid_amount, 2));
    }

    public function isCoveredBySubscription(): bool
    {
        return (float) $this->total_amount <= 0
            && $this->payment_status === 'paid';
    }

    /**
     * Visit has a charge that must be settled (online or office collection).
     */
    public function requiresVisitPayment(): bool
    {
        return (float) $this->total_amount > 0;
    }

    /**
     * Online/office fee is settled (free visits count as settled).
     */
    public function isVisitPaymentSettled(): bool
    {
        if (! $this->requiresVisitPayment()) {
            return true;
        }

        return $this->payment_status === 'paid';
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'partially_paid' => 'Partially paid',
            'paid' => 'Paid',
            'refunded' => 'Refunded',
            default => 'Payment pending',
        };
    }

    public function paymentStatusBadgeClass(): string
    {
        return match ($this->payment_status) {
            'paid' => 'success',
            'partially_paid' => 'warning',
            'refunded' => 'secondary',
            default => 'danger',
        };
    }

    /**
     * Statuses a patient may cancel from (v1: before staff has accepted).
     */
    public const PATIENT_CANCELLABLE_STATUSES = ['pending', 'pending_approval'];

    /**
     * CRITICAL FIX #5: Valid status transitions state machine
     */
    private static $validTransitions = [
        'pending' => ['assigned', 'pending_approval', 'cancelled'],
        'pending_approval' => ['assigned', 'pending', 'cancelled'],
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
        return '₹'.number_format($this->total_amount);
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
     * Check if request is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * User who cancelled the request (patient or future admin cancel).
     */
    public function cancelledByUser()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Patient self-cancel: own request, pending or pending_approval only.
     */
    public function canBeCancelledByPatient(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user || ! $user->isPatient()) {
            return false;
        }

        if ((int) $this->patient_id !== (int) $user->id) {
            return false;
        }

        if (! in_array($this->status, self::PATIENT_CANCELLABLE_STATUSES, true)) {
            return false;
        }

        return $this->canTransitionTo('cancelled');
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

    /**
     * Patient charge remains after recorded collections.
     */
    public function scopeWithBalanceDue($query)
    {
        return $query->where('total_amount', '>', 0)
            ->whereRaw('prepaid_amount < total_amount');
    }

    /**
     * Patient mobile on file (profile or booking contact).
     */
    public function patientContactPhone(): ?string
    {
        $this->loadMissing('patient');

        return (string) ($this->patient?->phone ?: $this->contact_phone ?: '') ?: null;
    }

    /**
     * Patient mobile matches staff verified account — login OTP already proved possession.
     * When true, completion does not need a separate patient OTP.
     */
    public function staffMayCompleteWithoutPatientOtp(User $staff): bool
    {
        if (! $staff->hasVerifiedPhone()) {
            return false;
        }

        $patientPhone = $this->patientContactPhone();

        return $patientPhone && $staff->accountPhonesMatch($staff->phone, $patientPhone);
    }

    /**
     * Staff may mark complete only on or after the service end date.
     */
    public function isReadyForStaffCompletion(): bool
    {
        if ($this->status !== 'in_progress' || $this->completion_verified_at) {
            return false;
        }

        return $this->end_date && $this->end_date->lte(now()->startOfDay());
    }
}

<?php

namespace App\Modules\Rewards\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaregiverReward extends Model
{
    protected $fillable = [
        'user_id',
        'patient_user_id',
        'patient_name',
        'patient_phone',
        'patient_email',
        'patient_age',
        'patient_address',
        'patient_pincode',
        'hospital_name',
        'treatment_details',
        'reward_points',
        'reward_amount',
        'verification_status',
        'verification_otp_hash',
        'verification_otp_expires_at',
        'verification_otp_attempts',
        'verification_otp_sent_at',
        'verification_otp_sent_to',
        'verified_at',
        'payment_processed',
        'payment_processed_at',
    ];

    protected $casts = [
        'verification_otp_expires_at' => 'datetime',
        'verification_otp_sent_at' => 'datetime',
        'verified_at' => 'datetime',
        'verification_otp_attempts' => 'integer',
        'payment_processed' => 'boolean',
        'payment_processed_at' => 'datetime',
        'reward_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Core\User::class);
    }

    public function patientUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Core\User::class, 'patient_user_id');
    }

    public function canChangePatientPhone(): bool
    {
        return ! $this->isPatientMobileOtpVerified() && ! $this->payment_processed;
    }

    /**
     * Human-readable patient mobile (+91 XXXXXXXXXX).
     */
    public function getDisplayPatientPhoneAttribute(): string
    {
        return app(\App\Modules\Auth\Services\UserService::class)->formatPhoneDisplay($this->patient_phone);
    }

    /**
     * 10-digit core for forms / OTP.
     */
    public function getPatientPhoneTenDigitsAttribute(): string
    {
        return app(\App\Modules\Auth\Services\UserService::class)->extractPhoneDigits((string) $this->patient_phone);
    }

    /**
     * Patient SMS OTP completed (not legacy/demo rows with only verification_status set).
     */
    public function isPatientMobileOtpVerified(): bool
    {
        return $this->verification_status === 'verified'
            && $this->verified_at !== null
            && $this->verification_otp_sent_at !== null;
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query
            ->where('verification_status', 'verified')
            ->whereNotNull('verified_at')
            ->whereNotNull('verification_otp_sent_at');
    }
}

<?php

namespace App\Modules\Rewards\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaregiverReward extends Model
{
    protected $fillable = [
        'user_id',
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
}

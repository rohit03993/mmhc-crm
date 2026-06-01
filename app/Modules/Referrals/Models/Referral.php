<?php

namespace App\Modules\Referrals\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referral_code',
        'referrer_id',
        'referrer_name_snapshot',
        'referrer_unique_id_snapshot',
        'referred_id',
        'referred_name_snapshot',
        'referred_unique_id_snapshot',
        'status',
        'verification_status',
        'verification_otp_hash',
        'verification_otp_expires_at',
        'verification_otp_attempts',
        'verification_otp_sent_at',
        'verification_otp_sent_to',
        'verified_at',
        'reward_points',
        'reward_amount',
        'completed_at',
        'payment_processed',
        'payment_processed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'verification_otp_expires_at' => 'datetime',
        'verification_otp_sent_at' => 'datetime',
        'verified_at' => 'datetime',
        'verification_otp_attempts' => 'integer',
        'payment_processed_at' => 'datetime',
        'reward_points' => 'integer',
        'reward_amount' => 'decimal:2',
        'payment_processed' => 'boolean',
    ];

    /**
     * Get the user who made the referral (referrer)
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id')->withTrashed();
    }

    /**
     * Get the user who was referred
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id')->withTrashed();
    }

    public function displayReferrerName(): string
    {
        if ($this->referrer && ! $this->referrer->trashed()) {
            return $this->referrer->name;
        }

        return $this->referrer_name_snapshot ?: 'Removed user';
    }

    public function displayReferredName(): string
    {
        if ($this->referred && ! $this->referred->trashed()) {
            return $this->referred->name;
        }

        if ($this->referred_name_snapshot) {
            return $this->referred_name_snapshot;
        }

        return 'Pending Registration';
    }

    public function isReferrerInactive(): bool
    {
        return $this->referrer_id === null
            || $this->referrer === null
            || $this->referrer->trashed();
    }

    public function isReferredInactive(): bool
    {
        if ($this->referred_id === null && ! $this->referred_name_snapshot) {
            return false;
        }

        if ($this->referred_name_snapshot && (! $this->referred || $this->referred->trashed())) {
            return true;
        }

        return $this->referred !== null && $this->referred->trashed();
    }

    /**
     * Check if referral is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if referral is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Referred staff completed SMS OTP (not legacy/demo completed rows only).
     */
    public function isReferralMobileOtpVerified(): bool
    {
        return $this->verification_status === 'verified'
            && $this->verified_at !== null;
    }

    public function scopeReferralMobileOtpVerified($query)
    {
        return $query
            ->where('verification_status', 'verified')
            ->whereNotNull('verified_at');
    }

    /**
     * Mark referral as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Scope for pending referrals
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed referrals
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for referrals by referrer
     */
    public function scopeByReferrer($query, $referrerId)
    {
        return $query->where('referrer_id', $referrerId);
    }
}

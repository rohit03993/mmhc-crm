<?php

namespace App\Modules\Referrals\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Core\User;

class Referral extends Model
{
    protected $fillable = [
        'referral_code',
        'referrer_id',
        'referred_id',
        'status',
        'reward_points',
        'reward_amount',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'reward_points' => 'integer',
        'reward_amount' => 'decimal:2',
    ];

    /**
     * Get the user who made the referral (referrer)
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the user who was referred
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
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


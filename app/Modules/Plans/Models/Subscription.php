<?php

namespace App\Modules\Plans\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\User;

class Subscription extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'plan_id',
        'referrer_id',
        'payment_frequency',
        'status',
        'start_date',
        'end_date',
        'care_benefits_years',
        'payable_years',
        'base_amount',
        'gst_amount',
        'gst_rate',
        'total_amount',
        'paid_amount',
        'payment_status',
        'payment_screenshot',
        'transaction_id',
        'payment_notes',
        'payment_verified_by',
        'payment_verified_at',
        'referral_commission_amount',
        'referral_commission_rate',
        'referral_payment_processed',
        'referral_payment_processed_at',
        'auto_renew',
        'notes',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'base_amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'referral_commission_amount' => 'decimal:2',
        'referral_commission_rate' => 'decimal:2',
        'referral_payment_processed' => 'boolean',
        'referral_payment_processed_at' => 'datetime',
        'auto_renew' => 'boolean',
        'approved_at' => 'datetime',
        'payment_verified_at' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan that this subscription belongs to.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the admin who approved this subscription.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user that verified the payment.
     */
    public function paymentVerifiedBy()
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }

    /**
     * Get the staff member who referred this subscription.
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the payments for this subscription.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for pending subscriptions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for expired subscriptions
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope for cancelled subscriptions
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Check if subscription is active
     */
    public function isActive()
    {
        return $this->status === 'active' && $this->end_date > now();
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired()
    {
        return $this->end_date < now();
    }



    /**
     * Get total amount paid for this subscription
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()
                    ->where('status', 'completed')
                    ->sum('amount');
    }

    /**
     * Get last payment
     */
    public function getLastPaymentAttribute()
    {
        return $this->payments()
                    ->orderBy('created_at', 'desc')
                    ->first();
    }

    /**
     * Get days remaining in subscription
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->end_date) {
            return 0;
        }
        
        $days = now()->diffInDays($this->end_date, false);
        return max(0, $days);
    }

    /**
     * Get status color for badges
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'success',
            'pending' => 'warning',
            'expired' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get human-readable status display
     */
    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'active' => 'Active',
            'pending' => 'Pending',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status ?? 'Unknown'),
        };
    }
}

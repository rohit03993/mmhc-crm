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
        'status',
        'start_date',
        'end_date',
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
        'auto_renew' => 'boolean',
        'approved_at' => 'datetime',
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
     * Get days remaining
     */
    public function getDaysRemainingAttribute()
    {
        if ($this->end_date < now()) {
            return 0;
        }
        
        return now()->diffInDays($this->end_date);
    }

    /**
     * Get subscription status display
     */
    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending Approval',
            'active' => 'Active',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
            'rejected' => 'Rejected',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'active' => 'success',
            'expired' => 'danger',
            'cancelled' => 'secondary',
            'rejected' => 'danger',
            default => 'secondary'
        };
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
}

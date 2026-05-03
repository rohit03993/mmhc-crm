<?php

namespace App\Modules\Payments\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;

class StaffPayment extends Model
{
    protected $fillable = [
        'staff_id',
        'admin_id',
        'payment_type',
        'amount',
        'payment_provider',
        'payment_mode',
        'gateway_status',
        'gateway_reference_id',
        'gateway_payload',
        'beneficiary_upi',
        'transaction_id',
        'notes',
        'payment_screenshot',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'gateway_payload' => 'array',
    ];

    /**
     * Get the staff member who received the payment
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Get the admin who made the payment
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Scope for specific payment type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('payment_type', $type);
    }

    /**
     * Scope for specific staff member
     */
    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }
}

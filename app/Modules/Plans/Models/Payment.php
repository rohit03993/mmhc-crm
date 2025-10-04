<?php

namespace App\Modules\Plans\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\User;

class Payment extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'subscription_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'transaction_id',
        'gateway_response',
        'invoice_number',
        'receipt_number',
        'paid_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the user that made the payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription that this payment belongs to.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Scope for completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for refunded payments
     */
    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    /**
     * Get payment status display
     */
    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
            'cancelled' => 'Cancelled',
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
            'completed' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
            'cancelled' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Get payment method display
     */
    public function getPaymentMethodDisplayAttribute()
    {
        return match($this->payment_method) {
            'razorpay' => 'Razorpay',
            'stripe' => 'Stripe',
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            default => ucfirst(str_replace('_', ' ', $this->payment_method))
        };
    }

    /**
     * Generate invoice number
     */
    public static function generateInvoiceNumber()
    {
        $lastInvoice = self::whereNotNull('invoice_number')
                          ->orderBy('id', 'desc')
                          ->first();
        
        if ($lastInvoice && $lastInvoice->invoice_number) {
            $number = (int) substr($lastInvoice->invoice_number, 3) + 1;
        } else {
            $number = 1;
        }
        
        return 'INV' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate receipt number
     */
    public static function generateReceiptNumber()
    {
        $lastReceipt = self::whereNotNull('receipt_number')
                          ->orderBy('id', 'desc')
                          ->first();
        
        if ($lastReceipt && $lastReceipt->receipt_number) {
            $number = (int) substr($lastReceipt->receipt_number, 3) + 1;
        } else {
            $number = 1;
        }
        
        return 'RCP' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment can be refunded
     */
    public function canBeRefunded()
    {
        return $this->status === 'completed' && 
               $this->paid_at && 
               $this->paid_at->diffInDays(now()) <= 30; // 30 days refund window
    }
}

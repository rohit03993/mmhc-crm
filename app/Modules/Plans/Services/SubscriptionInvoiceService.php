<?php

namespace App\Modules\Plans\Services;

use App\Modules\Plans\Models\Payment;
use App\Modules\Plans\Models\Subscription;

class SubscriptionInvoiceService
{
    /**
     * Create or return the completed payment record used for invoicing.
     */
    public function ensurePaymentRecord(Subscription $subscription): ?Payment
    {
        if ($subscription->payment_status !== 'paid') {
            return null;
        }

        $existing = Payment::query()
            ->where('subscription_id', $subscription->id)
            ->where('status', 'completed')
            ->first();

        if ($existing) {
            return $existing;
        }

        $method = $subscription->payment_provider ?: (
            ($subscription->razorpay_payment_id || $subscription->payment_provider === 'razorpay')
                ? 'razorpay'
                : 'manual'
        );

        return Payment::create([
            'user_id' => $subscription->user_id,
            'subscription_id' => $subscription->id,
            'amount' => $subscription->total_amount,
            'currency' => $subscription->plan->currency ?? 'INR',
            'status' => 'completed',
            'payment_method' => $method,
            'transaction_id' => $subscription->razorpay_payment_id ?: $subscription->transaction_id,
            'gateway_response' => is_array($subscription->gateway_payload)
                ? $subscription->gateway_payload
                : null,
            'invoice_number' => Payment::generateInvoiceNumber(),
            'receipt_number' => Payment::generateReceiptNumber(),
            'paid_at' => $subscription->payment_verified_at ?? now(),
        ]);
    }
}

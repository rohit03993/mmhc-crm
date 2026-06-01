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

        $collected = (float) ($subscription->paid_amount > 0
            ? $subscription->paid_amount
            : $subscription->total_amount);

        if ($existing) {
            if (abs((float) $existing->amount - $collected) >= 0.01) {
                $existing->update(['amount' => $collected]);
            }

            return $existing;
        }

        $method = $subscription->payment_provider ?: (
            ($subscription->razorpay_payment_id || $subscription->payment_provider === 'razorpay')
                ? 'razorpay'
                : 'manual'
        );

        $payment = Payment::create([
            'user_id' => $subscription->user_id,
            'subscription_id' => $subscription->id,
            'amount' => $collected,
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

        SubscriptionPaymentHistoryService::bustAdminDashboardCache();

        return $payment;
    }
}

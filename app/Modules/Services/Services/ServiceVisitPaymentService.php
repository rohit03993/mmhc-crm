<?php

namespace App\Modules\Services\Services;

use App\Models\Core\User;
use App\Modules\Auth\Services\AppNotificationService;
use App\Modules\Plans\Services\RazorpayService;
use App\Modules\Services\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ServiceVisitPaymentService
{
    public function __construct(
        private RazorpayService $razorpayService
    ) {}

    public function isRazorpayEnabled(): bool
    {
        return $this->razorpayService->isEnabled();
    }

    /**
     * Create Razorpay order for an unpaid visit booking.
     *
     * @return array{order_id: string, key: string, amount: int, currency: string}
     */
    public function createOrder(ServiceRequest $serviceRequest, User $patient): array
    {
        if ((int) $serviceRequest->patient_id !== (int) $patient->id) {
            throw new InvalidArgumentException('You can only pay for your own bookings.');
        }

        if (! $serviceRequest->requiresVisitPayment()) {
            throw new InvalidArgumentException('This booking does not require online payment.');
        }

        if ($serviceRequest->isVisitPaymentSettled()) {
            throw new InvalidArgumentException('This visit is already paid.');
        }

        if (! $this->razorpayService->isEnabled()) {
            throw new InvalidArgumentException(
                'Razorpay checkout is not configured. '.$this->razorpayService->configurationHint()
            );
        }

        $currency = (string) config('payments.razorpay.currency', 'INR');
        $amountPaise = (int) round(((float) $serviceRequest->total_amount) * 100);
        if ($amountPaise <= 0) {
            throw new InvalidArgumentException('Invalid visit amount.');
        }

        $order = $this->razorpayService->createOrder([
            'amount' => $amountPaise,
            'currency' => $currency,
            'receipt' => 'srv_'.$serviceRequest->id.'_'.time(),
            'notes' => [
                'service_request_id' => (string) $serviceRequest->id,
                'patient_id' => (string) $serviceRequest->patient_id,
                'purpose' => 'visit_fee',
            ],
        ]);

        $serviceRequest->update([
            'payment_provider' => 'razorpay',
            'gateway_status' => 'created',
            'gateway_payload' => $order,
            'razorpay_order_id' => $order['id'] ?? null,
        ]);

        return [
            'order_id' => $order['id'],
            'key' => $this->razorpayService->getKeyId(),
            'amount' => $order['amount'],
            'currency' => $order['currency'] ?? $currency,
        ];
    }

    public function verifyAndMarkPaid(
        ServiceRequest $serviceRequest,
        array $callback,
        ?User $actor = null
    ): ServiceRequest {
        if ($serviceRequest->isVisitPaymentSettled()) {
            return $serviceRequest;
        }

        $orderId = (string) ($callback['razorpay_order_id'] ?? '');
        $paymentId = (string) ($callback['razorpay_payment_id'] ?? '');
        $signature = (string) ($callback['razorpay_signature'] ?? '');

        if ($orderId === '' || $paymentId === '' || $signature === '') {
            throw new InvalidArgumentException('Invalid Razorpay callback payload.');
        }

        if ($serviceRequest->razorpay_order_id && $serviceRequest->razorpay_order_id !== $orderId) {
            throw new InvalidArgumentException('Order mismatch for this booking.');
        }

        $valid = $this->razorpayService->verifyPaymentSignature([
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ]);

        if (! $valid) {
            throw new InvalidArgumentException('Payment signature verification failed.');
        }

        $paymentPayload = $callback['gateway_payload'] ?? [];
        if ($paymentPayload === [] && empty($callback['from_webhook'])) {
            try {
                $paymentPayload = $this->razorpayService->fetchPayment($paymentId);
            } catch (\Throwable $e) {
                Log::warning('Could not fetch Razorpay payment for visit', [
                    'service_request_id' => $serviceRequest->id,
                    'payment_id' => $paymentId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->finalizePaid($serviceRequest, [
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
            'razorpay_event_id' => $callback['razorpay_event_id'] ?? null,
            'gateway_payload' => $paymentPayload ?: $serviceRequest->gateway_payload,
        ], $actor);
    }

    public function markPaidFromWebhook(ServiceRequest $serviceRequest, array $data): ServiceRequest
    {
        if ($serviceRequest->isVisitPaymentSettled()) {
            return $serviceRequest;
        }

        if (! empty($data['razorpay_event_id'])
            && $serviceRequest->razorpay_event_id
            && $serviceRequest->razorpay_event_id === $data['razorpay_event_id']) {
            return $serviceRequest;
        }

        return $this->finalizePaid($serviceRequest, $data);
    }

    private function finalizePaid(ServiceRequest $serviceRequest, array $data, ?User $actor = null): ServiceRequest
    {
        DB::transaction(function () use ($serviceRequest, $data) {
            $serviceRequest->refresh();
            if ($serviceRequest->isVisitPaymentSettled()) {
                return;
            }

            $serviceRequest->update([
                'payment_provider' => 'razorpay',
                'gateway_status' => 'captured',
                'gateway_payload' => $data['gateway_payload'] ?? $serviceRequest->gateway_payload,
                'razorpay_order_id' => $data['razorpay_order_id'] ?? $serviceRequest->razorpay_order_id,
                'razorpay_payment_id' => $data['razorpay_payment_id'] ?? $serviceRequest->razorpay_payment_id,
                'razorpay_signature' => $data['razorpay_signature'] ?? $serviceRequest->razorpay_signature,
                'razorpay_event_id' => $data['razorpay_event_id'] ?? $serviceRequest->razorpay_event_id,
                'prepaid_amount' => $serviceRequest->total_amount,
                'payment_status' => 'paid',
                'visit_paid_at' => now(),
            ]);
        });

        $serviceRequest->refresh();

        // Notify staff only after payment (for pending_approval bookings).
        if ($serviceRequest->assigned_staff_id && $serviceRequest->status === 'pending_approval') {
            try {
                app(AppNotificationService::class)->notifyBookingCreated($serviceRequest);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        Log::info('Visit fee paid via Razorpay', [
            'service_request_id' => $serviceRequest->id,
            'payment_id' => $serviceRequest->razorpay_payment_id,
            'actor_id' => $actor?->id,
        ]);

        return $serviceRequest;
    }
}

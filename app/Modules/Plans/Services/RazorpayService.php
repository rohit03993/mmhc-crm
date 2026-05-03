<?php

namespace App\Modules\Plans\Services;

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayService
{
    public function isEnabled(): bool
    {
        return (bool) config('payments.razorpay.enabled')
            && ! empty(config('payments.razorpay.key_id'))
            && ! empty(config('payments.razorpay.key_secret'));
    }

    public function getKeyId(): ?string
    {
        return config('payments.razorpay.key_id');
    }

    public function createOrder(array $payload): array
    {
        return $this->api()->order->create($payload)->toArray();
    }

    public function fetchPayment(string $paymentId): array
    {
        return $this->api()->payment->fetch($paymentId)->toArray();
    }

    public function verifyPaymentSignature(array $attributes): bool
    {
        try {
            $this->api()->utility->verifyPaymentSignature($attributes);

            return true;
        } catch (SignatureVerificationError) {
            return false;
        }
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = (string) config('payments.razorpay.webhook_secret');
        if ($secret === '') {
            return false;
        }

        try {
            $this->api()->utility->verifyWebhookSignature($payload, $signature, $secret);

            return true;
        } catch (SignatureVerificationError) {
            return false;
        }
    }

    private function api(): Api
    {
        return new Api(
            (string) config('payments.razorpay.key_id'),
            (string) config('payments.razorpay.key_secret')
        );
    }
}

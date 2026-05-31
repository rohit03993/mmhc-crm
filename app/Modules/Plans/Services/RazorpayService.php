<?php

namespace App\Modules\Plans\Services;

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayService
{
    public function isEnabled(): bool
    {
        return (bool) config('payments.razorpay.enabled')
            && $this->hasApiCredentials();
    }

    public function hasApiCredentials(): bool
    {
        return ! empty(config('payments.razorpay.key_id'))
            && ! empty(config('payments.razorpay.key_secret'));
    }

    public function hasWebhookSecret(): bool
    {
        return (string) config('payments.razorpay.webhook_secret') !== '';
    }

    /**
     * Plain-English hint when checkout cannot start (for admin / logs).
     */
    public function configurationHint(): string
    {
        if (! config('payments.razorpay.enabled')) {
            return 'Set RAZORPAY_ENABLED=true in .env';
        }

        if (empty(config('payments.razorpay.key_id'))) {
            return 'Add RAZORPAY_KEY_ID from Razorpay → Account & Settings → API keys (Live mode).';
        }

        if (empty(config('payments.razorpay.key_secret'))) {
            return 'Add RAZORPAY_KEY_SECRET from the same API keys page. If you only saved Key ID, click Regenerate Key — the secret is shown once.';
        }

        return 'Razorpay is configured.';
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

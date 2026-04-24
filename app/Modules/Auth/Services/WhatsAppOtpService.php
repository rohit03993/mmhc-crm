<?php

namespace App\Modules\Auth\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Send and verify OTP via WhatsApp using AiSensy/Sensy API.
 * OTP is stored in cache (key: otp:{normalized_phone}, ttl: 10 minutes).
 */
class WhatsAppOtpService
{
    public const OTP_LENGTH = 6;
    public const OTP_TTL_MINUTES = 10;
    public const RATE_LIMIT_MINUTES = 1;
    public const CACHE_PREFIX = 'otp:';
    public const RATE_PREFIX = 'otp_rate:';

    /**
     * Generate a numeric OTP and store in cache; send via WhatsApp.
     * Returns true if sent, false if rate-limited or API failure.
     */
    public function sendOtp(string $normalizedPhone): array
    {
        $rateKey = self::RATE_PREFIX . $normalizedPhone;
        if (Cache::has($rateKey)) {
            return ['success' => false, 'message' => 'Please wait a minute before requesting another OTP.'];
        }

        $otp = $this->generateOtp();
        Cache::put(self::CACHE_PREFIX . $normalizedPhone, $otp, now()->addMinutes(self::OTP_TTL_MINUTES));
        Cache::put($rateKey, true, now()->addMinutes(self::RATE_LIMIT_MINUTES));

        $apiResult = $this->sendViaApi($normalizedPhone, $otp);
        if (!$apiResult['success']) {
            $message = $apiResult['message'] ?? 'Could not send OTP. Please try again or login with email.';
            return ['success' => false, 'message' => $message];
        }

        return ['success' => true, 'message' => 'OTP sent to your WhatsApp.'];
    }

    /**
     * Verify OTP for the given phone. Returns true if valid.
     */
    public function verifyOtp(string $normalizedPhone, string $otp): bool
    {
        $stored = Cache::get(self::CACHE_PREFIX . $normalizedPhone);
        if ($stored === null) {
            return false;
        }
        if (!hash_equals((string) $stored, (string) $otp)) {
            return false;
        }
        Cache::forget(self::CACHE_PREFIX . $normalizedPhone);
        return true;
    }

    protected function generateOtp(): string
    {
        $digits = '';
        for ($i = 0; $i < self::OTP_LENGTH; $i++) {
            $digits .= (string) random_int(0, 9);
        }
        return $digits;
    }

    /**
     * Call AiSensy API to send WhatsApp template message with OTP.
     * Returns ['success' => bool, 'message' => string|null]. message is set on failure for UI.
     */
    protected function sendViaApi(string $normalizedPhone, string $otp): array
    {
        $apiKey = config('services.aisensy.api_key');
        $campaignName = config('services.aisensy.campaign_name');
        $baseUrl = config('services.aisensy.base_url');

        if (empty($apiKey) || empty($campaignName)) {
            Log::warning('WhatsApp OTP: config missing', [
                'api_key_set' => !empty($apiKey),
                'campaign_name_set' => !empty($campaignName),
                'campaign_name_value' => $campaignName ?: '(empty)',
                'base_url' => $baseUrl,
            ]);
            return ['success' => false, 'message' => 'WhatsApp OTP is not configured. Set AISENSY_API_KEY and AISENSY_CAMPAIGN_NAME in .env.'];
        }

        // AiSensy auth template: one variable {{1}} = 6-digit OTP (body; Copy code uses the same OTP from the template).
        $payload = [
            'apiKey' => $apiKey,
            'campaignName' => $campaignName,
            'destination' => $normalizedPhone,
            'userName' => 'User',
            'templateParams' => [$otp],
        ];

        try {
            Log::info('WhatsApp OTP: sending request', [
                'base_url' => $baseUrl,
                'campaign_name' => $campaignName,
                'destination' => $normalizedPhone,
            ]);
            $response = Http::timeout(15)->post($baseUrl, $payload);
            if ($response->successful()) {
                return ['success' => true, 'message' => null];
            }

            $body = $response->body();
            $apiMessage = null;
            if ($body) {
                $decoded = json_decode($body, true);
                if (isset($decoded['message']) && is_string($decoded['message'])) {
                    $apiMessage = $decoded['message'];
                }
            }
            Log::warning('WhatsApp OTP: API returned non-2xx', [
                'status' => $response->status(),
                'body' => $body,
                'base_url' => $baseUrl,
                'phone_last4' => substr($normalizedPhone, -4),
            ]);
            $userMessage = $apiMessage
                ? 'Could not send OTP: ' . $apiMessage
                : 'Could not send OTP. Please try again or login with email.';
            return ['success' => false, 'message' => $userMessage];
        } catch (\Throwable $e) {
            Log::error('WhatsApp OTP: request exception', [
                'message' => $e->getMessage(),
                'base_url' => $baseUrl,
                'phone_last4' => substr($normalizedPhone, -4),
            ]);
            return ['success' => false, 'message' => 'Could not send OTP. Please try again or login with email.'];
        }
    }
}

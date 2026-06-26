<?php

namespace App\Modules\Auth\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Phone OTP: deliver via WhatsApp (Pal Digital / mmhc_verification_code2); verify in-app with HMAC.
 */
class SmsOtpService
{
    public const OTP_LENGTH = 6;

    public const RATE_LIMIT_MINUTES = 1;

    public const CACHE_PREFIX = 'phone_otp:v1:';

    public const RATE_PREFIX = 'otp_delivery_rate:v1:';

    public function __construct(
        private PhoneNormalizer $phone,
        private PalDigitalWhatsAppService $palDigital,
    ) {}

    public function isConfigured(): bool
    {
        return $this->palDigital->isConfigured();
    }

    public function deliveryChannelLabel(): string
    {
        return 'WhatsApp';
    }

    /**
     * @return array{success: bool, message: string|null}
     */
    public function sendOtp(string $phoneForCache, ?string $contactName = null): array
    {
        $e164 = $this->phone->toE164($phoneForCache);
        if ($e164 === null) {
            return ['success' => false, 'message' => 'Invalid phone number.'];
        }

        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'WhatsApp OTP is not configured. Set PAL_DIGITAL_INTEGRATION_KEY and PAL_DIGITAL_CAMPAIGN_ID in .env.',
            ];
        }

        $rateKey = self::RATE_PREFIX.$e164;
        if (Cache::has($rateKey)) {
            return ['success' => false, 'message' => 'Please wait a minute before requesting another code.'];
        }

        $otp = $this->generateOtp();
        $ttl = max(60, (int) config('services.phone_otp.ttl_seconds', 600));
        $pepper = (string) config('services.phone_otp.pepper', config('app.key'));
        $digest = hash_hmac('sha256', $e164.':'.$otp, $pepper);

        Cache::put(self::CACHE_PREFIX.$e164, $digest, now()->addSeconds($ttl));
        Cache::put($rateKey, true, now()->addMinutes(self::RATE_LIMIT_MINUTES));

        $send = $this->deliverOtp($e164, $otp, $contactName);
        if (! ($send['success'] ?? false)) {
            Cache::forget(self::CACHE_PREFIX.$e164);
            $message = $send['message'] ?? 'Could not send the code via WhatsApp. Please try again.';

            return ['success' => false, 'message' => $message];
        }

        return [
            'success' => true,
            'message' => 'A login code was sent to your mobile via WhatsApp.',
        ];
    }

    public function verifyOtp(string $phoneForCache, string $otp): bool
    {
        $e164 = $this->phone->toE164($phoneForCache);
        if ($e164 === null) {
            return false;
        }
        if (! preg_match('/^\d{6}$/', (string) $otp)) {
            return false;
        }

        $key = self::CACHE_PREFIX.$e164;
        $stored = Cache::get($key);
        if (! is_string($stored) || $stored === '') {
            return false;
        }

        $pepper = (string) config('services.phone_otp.pepper', config('app.key'));
        $candidate = hash_hmac('sha256', $e164.':'.$otp, $pepper);
        if (! hash_equals($stored, $candidate)) {
            return false;
        }
        Cache::forget($key);

        return true;
    }

    /**
     * @return array{success: bool, message: string|null}
     */
    public function sendCustomOtp(string $destinationPhone, string $otp, ?string $contactName = null): array
    {
        if (! preg_match('/^\d{6}$/', (string) $otp)) {
            return ['success' => false, 'message' => 'Invalid OTP format.'];
        }

        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'WhatsApp OTP is not configured. Set PAL_DIGITAL_INTEGRATION_KEY and PAL_DIGITAL_CAMPAIGN_ID.',
            ];
        }

        $e164 = $this->phone->toE164($destinationPhone);
        if ($e164 === null) {
            return ['success' => false, 'message' => 'Invalid destination phone number.'];
        }

        return $this->deliverOtp($e164, $otp, $contactName);
    }

    /**
     * @return array{success: bool, message: ?string}
     */
    private function deliverOtp(string $e164, string $otp, ?string $contactName): array
    {
        return $this->palDigital->sendVerificationOtp($e164, $otp, $contactName);
    }

    protected function generateOtp(): string
    {
        $digits = '';
        for ($i = 0; $i < self::OTP_LENGTH; $i++) {
            $digits .= (string) random_int(0, 9);
        }

        return $digits;
    }
}

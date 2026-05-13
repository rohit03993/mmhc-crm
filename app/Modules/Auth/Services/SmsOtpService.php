<?php

namespace App\Modules\Auth\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Phone OTP: send via Sent.dm SMS templates; verify using cache + HMAC (login),
 * or send-only for flows that store OTP hashes on domain rows (referrals, rewards, etc.).
 */
class SmsOtpService
{
    public const OTP_LENGTH = 6;

    public const RATE_LIMIT_MINUTES = 1;

    public const CACHE_PREFIX = 'phone_otp:v1:';

    public const RATE_PREFIX = 'sms_otp_rate:v1:';

    public function __construct(
        private SentDmSmsService $sentDm
    ) {}

    public function isConfigured(): bool
    {
        return $this->sentDm->isConfigured();
    }

    /**
     * Generate OTP, store HMAC in cache, send SMS via Sent.dm template.
     *
     * @return array{success: bool, message: string|null}
     */
    public function sendOtp(string $phoneForCache): array
    {
        $e164 = $this->sentDm->normalizeToE164($phoneForCache);
        if ($e164 === null) {
            return ['success' => false, 'message' => 'Invalid phone number for SMS.'];
        }

        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Phone sign-in is not configured. Set SENT_DM_API_KEY and SENT_DM_TEMPLATE_ID (and SENT_DM_OTP_PARAMETER_NAME to match your template).',
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

        $paramName = (string) config('services.sent_dm.otp_parameter_name', 'code');
        $send = $this->sentDm->sendTemplateSms($e164, [$paramName => $otp]);
        if (! ($send['success'] ?? false)) {
            Cache::forget(self::CACHE_PREFIX.$e164);
            $message = $send['message'] ?? 'Could not send SMS. Please try again or use email login.';

            return ['success' => false, 'message' => $message];
        }

        return ['success' => true, 'message' => 'A login code was sent to your mobile via SMS.'];
    }

    /**
     * Verify login OTP for the given phone (same normalization as send).
     */
    public function verifyOtp(string $phoneForCache, string $otp): bool
    {
        $e164 = $this->sentDm->normalizeToE164($phoneForCache);
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
     * Send a caller-provided OTP via SMS (OTP stored elsewhere, e.g. DB hash).
     *
     * @return array{success: bool, message: string|null}
     */
    public function sendCustomOtp(string $destinationPhone, string $otp): array
    {
        if (! preg_match('/^\d{6}$/', (string) $otp)) {
            return ['success' => false, 'message' => 'Invalid OTP format.'];
        }

        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'SMS is not configured. Set SENT_DM_API_KEY and SENT_DM_TEMPLATE_ID.',
            ];
        }

        $e164 = $this->sentDm->normalizeToE164($destinationPhone);
        if ($e164 === null) {
            return ['success' => false, 'message' => 'Invalid destination phone for SMS.'];
        }

        $paramName = (string) config('services.sent_dm.otp_parameter_name', 'code');

        return $this->sentDm->sendTemplateSms($e164, [$paramName => $otp]);
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

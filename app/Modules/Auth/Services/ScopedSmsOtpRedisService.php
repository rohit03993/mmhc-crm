<?php

namespace App\Modules\Auth\Services;

use Illuminate\Support\Facades\Cache;

/**
 * HMAC-based OTP for SMS flows keyed by domain entity (referral, patient reward, service request).
 * Mobile channel: secret in cache only (no plaintext OTP in DB). Email flows keep Laravel Hash on the model.
 *
 * Key: crm_sms_otp:v1:{purpose}:{id}
 * Digest: HMAC-SHA256("{purpose}:{id}:{code}", pepper)
 */
class ScopedSmsOtpRedisService
{
    public const PREFIX = 'crm_sms_otp:v1:';

    public const PURPOSE_REFERRAL = 'referral';

    public const PURPOSE_PATIENT_REWARD = 'patient_reward';

    public const PURPOSE_SERVICE_COMPLETION = 'service_completion';

    public function cacheKey(string $purpose, int $id): string
    {
        return self::PREFIX.$purpose.':'.$id;
    }

    public function ttlSeconds(): int
    {
        return max(60, (int) config('services.phone_otp.bind_ttl_seconds', 300));
    }

    public function store(string $purpose, int $id, string $otp): void
    {
        if (! preg_match('/^\d{6}$/', $otp)) {
            return;
        }
        $pepper = (string) config('services.phone_otp.pepper', config('app.key'));
        $digest = hash_hmac('sha256', "{$purpose}:{$id}:{$otp}", $pepper);
        Cache::put($this->cacheKey($purpose, $id), $digest, now()->addSeconds($this->ttlSeconds()));
    }

    public function verifyAndConsume(string $purpose, int $id, string $otp): bool
    {
        if (! preg_match('/^\d{6}$/', $otp)) {
            return false;
        }
        $key = $this->cacheKey($purpose, $id);
        $stored = Cache::get($key);
        if (! is_string($stored) || $stored === '') {
            return false;
        }
        $pepper = (string) config('services.phone_otp.pepper', config('app.key'));
        $candidate = hash_hmac('sha256', "{$purpose}:{$id}:{$otp}", $pepper);
        if (! hash_equals($stored, $candidate)) {
            return false;
        }
        Cache::forget($key);

        return true;
    }

    public function forget(string $purpose, int $id): void
    {
        Cache::forget($this->cacheKey($purpose, $id));
    }
}

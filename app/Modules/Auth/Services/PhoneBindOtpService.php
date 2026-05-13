<?php

namespace App\Modules\Auth\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Bind-phone OTP: Redis + HMAC so a code minted for one user cannot verify for another (Laravel implementation of the bind pattern).
 *
 * Cache key: phone_bind_otp:v1:{userId}:{phone_e164}
 * Digest: HMAC-SHA256( "{userId}:{e164}:{code}", pepper )
 */
class PhoneBindOtpService
{
    public const CACHE_PREFIX = 'phone_bind_otp:v1:';

    public function __construct(
        private SentDmSmsService $sentDm
    ) {}

    public function bindCacheKey(int $userId, string $destinationPhone): ?string
    {
        $e164 = $this->sentDm->normalizeToE164($destinationPhone);
        if ($e164 === null) {
            return null;
        }

        return self::CACHE_PREFIX.$userId.':'.$e164;
    }

    /**
     * Store bind OTP digest (after SMS send succeeds).
     */
    public function storeOtp(int $userId, string $destinationPhone, string $otp): void
    {
        $key = $this->bindCacheKey($userId, $destinationPhone);
        if ($key === null || ! preg_match('/^\d{6}$/', $otp)) {
            return;
        }

        $ttl = max(60, (int) config('services.phone_otp.bind_ttl_seconds', 300));
        $pepper = (string) config('services.phone_otp.pepper', config('app.key'));
        $e164 = $this->sentDm->normalizeToE164($destinationPhone);
        if ($e164 === null) {
            return;
        }
        $digest = hash_hmac('sha256', $userId.':'.$e164.':'.$otp, $pepper);
        Cache::put($key, $digest, now()->addSeconds($ttl));
    }

    /**
     * Verify OTP for this user + phone and remove the key (single use).
     */
    public function verifyAndConsume(int $userId, string $destinationPhone, string $otp): bool
    {
        $key = $this->bindCacheKey($userId, $destinationPhone);
        if ($key === null || ! preg_match('/^\d{6}$/', (string) $otp)) {
            return false;
        }

        $stored = Cache::get($key);
        if (! is_string($stored) || $stored === '') {
            return false;
        }

        $e164 = $this->sentDm->normalizeToE164($destinationPhone);
        if ($e164 === null) {
            return false;
        }

        $pepper = (string) config('services.phone_otp.pepper', config('app.key'));
        $candidate = hash_hmac('sha256', $userId.':'.$e164.':'.$otp, $pepper);
        if (! hash_equals($stored, $candidate)) {
            return false;
        }
        Cache::forget($key);

        return true;
    }

    public function forget(int $userId, string $destinationPhone): void
    {
        $key = $this->bindCacheKey($userId, $destinationPhone);
        if ($key !== null) {
            Cache::forget($key);
        }
    }
}

<?php

namespace App\Modules\Auth\Services;

use App\Models\Core\User;

/**
 * Send SMS OTP to confirm the mobile number on a user account.
 */
class PhoneVerificationService
{
    public function __construct(
        protected UserService $userService,
        protected SmsOtpService $smsOtpService,
        protected PhoneBindOtpService $bindOtpService,
    ) {}

    /**
     * @return array{success: bool, message?: string, sent_to?: string, already_verified?: bool}
     */
    public function sendAccountVerificationOtp(User $user, bool $forceResend = false): array
    {
        if ($user->hasVerifiedPhone() && ! $user->hasPendingMobileContactVerification()) {
            return ['success' => true, 'already_verified' => true, 'message' => 'Mobile already verified.'];
        }

        $normalizedPhone = $this->normalizeStoredPhone($user);
        if (! $normalizedPhone) {
            return ['success' => false, 'message' => 'Add a valid 10-digit mobile number to your profile first.'];
        }

        if (
            ! $forceResend
            && $user->hasPendingMobileContactVerification()
            && (string) $user->pending_phone === $normalizedPhone
            && $user->contact_update_otp_sent_at
            && $user->contact_update_otp_expires_at
            && now()->lessThan($user->contact_update_otp_expires_at)
        ) {
            return [
                'success' => true,
                'message' => 'OTP already sent. Check your SMS or wait for it to expire to resend.',
                'sent_to' => (string) ($user->contact_update_otp_sent_to ?? ''),
            ];
        }

        if (
            $user->contact_update_otp_sent_at
            && $user->contact_update_otp_sent_at->gt(now()->subMinutes(15))
            && ! $forceResend
        ) {
            return [
                'success' => false,
                'message' => 'Please wait 15 minutes before requesting another OTP.',
            ];
        }

        $user->forceFill([
            'pending_email' => null,
            'pending_phone' => $normalizedPhone,
            'contact_update_channel' => 'mobile',
            'contact_update_otp_hash' => null,
            'contact_update_otp_expires_at' => null,
            'contact_update_otp_attempts' => 0,
            'contact_update_otp_sent_to' => null,
            'contact_update_otp_sent_at' => null,
            'contact_update_verified_at' => null,
        ])->save();

        $otp = (string) random_int(100000, 999999);
        $send = $this->smsOtpService->sendCustomOtp($normalizedPhone, $otp);
        if (! ($send['success'] ?? false)) {
            return ['success' => false, 'message' => $send['message'] ?? 'Could not send OTP to mobile.'];
        }

        $this->bindOtpService->storeOtp((int) $user->id, $normalizedPhone, $otp);
        $otpDigest = $this->bindOtpService->buildOtpDigest((int) $user->id, $normalizedPhone, $otp);
        $sentTo = 'Mobile: '.$this->maskPhone($normalizedPhone);

        $user->forceFill([
            'contact_update_otp_hash' => $otpDigest,
            'contact_update_otp_expires_at' => now()->addMinutes(5),
            'contact_update_otp_attempts' => 0,
            'contact_update_otp_sent_to' => $sentTo,
            'contact_update_otp_sent_at' => now(),
        ])->save();

        return ['success' => true, 'message' => 'OTP sent by SMS.', 'sent_to' => $sentTo];
    }

    /**
     * Admin manually marks mobile as verified (rewards / payouts unlock).
     */
    public function markVerifiedByAdmin(User $user, User $admin): void
    {
        $user->applyPhoneVerifiedByAdmin($admin);

        if ($user->isStaff()) {
            app(\App\Modules\Rewards\Services\RewardService::class)->syncStaffRewardPoints($user->fresh());
        }
    }

    /**
     * Send verification OTP SMS to all users with unverified mobiles.
     *
     * @return array{sent: int, skipped: int, failed: int, errors: list<string>}
     */
    public function bulkSendVerificationReminders(int $limit = 150): array
    {
        $stats = ['sent' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []];

        $users = User::query()
            ->whereNull('phone_verified_at')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where('is_active', true)
            ->orderBy('id')
            ->limit(max(1, min($limit, 500)))
            ->get();

        foreach ($users as $user) {
            if (! $this->normalizeStoredPhone($user)) {
                $stats['skipped']++;

                continue;
            }

            $result = $this->sendAccountVerificationOtp($user);
            if ($result['success'] ?? false) {
                $stats['sent']++;
            } else {
                $stats['failed']++;
                if (count($stats['errors']) < 8) {
                    $stats['errors'][] = ($user->name ?? 'User #'.$user->id).': '.($result['message'] ?? 'Send failed');
                }
            }

            usleep(150000);
        }

        return $stats;
    }

    protected function normalizeStoredPhone(User $user): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) ($user->phone ?? ''));
        if (strlen($digits) === 10) {
            return '91'.$digits;
        }
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return $digits;
        }

        return null;
    }

    protected function maskPhone(string $normalizedPhone): string
    {
        return str_repeat('*', max(0, strlen($normalizedPhone) - 4)).substr($normalizedPhone, -4);
    }
}

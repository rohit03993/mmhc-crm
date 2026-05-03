<?php

namespace App\Modules\Rewards\Services;

use App\Models\Core\User;
use App\Modules\Auth\Services\WhatsAppOtpService;
use App\Modules\Rewards\Models\CaregiverReward;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RewardService
{
    public const POINT_VALUE = 10; // 1 point = ₹10

    /**
     * Create a reward entry and increment user's reward points.
     */
    public function createReward(User $user, array $data): CaregiverReward
    {
        return $this->createPendingReward($user, $data);
    }

    public function createPendingReward(User $user, array $data): CaregiverReward
    {
        return CaregiverReward::create([
            'user_id' => $user->id,
            'patient_name' => $data['patient_name'],
            'patient_phone' => $data['patient_phone'],
            'patient_email' => $data['patient_email'] ?? null,
            'patient_age' => $data['patient_age'] ?? null,
            'patient_address' => $data['patient_address'] ?? null,
            'patient_pincode' => $data['patient_pincode'] ?? null,
            'hospital_name' => $data['hospital_name'],
            'treatment_details' => $data['treatment_details'] ?? null,
            'reward_points' => 1,
            'reward_amount' => self::POINT_VALUE,
            'verification_status' => 'pending',
        ]);
    }

    public function sendVerificationOtp(CaregiverReward $reward, string $channel = 'mobile'): array
    {
        if ($reward->verification_otp_sent_at && $reward->verification_otp_sent_at->gt(now()->subMinutes(15))) {
            return ['success' => false, 'message' => 'Please wait 15 minutes before requesting OTP again.'];
        }
        $channel = strtolower($channel);
        if (! in_array($channel, ['mobile', 'email'], true)) {
            return ['success' => false, 'message' => 'Invalid OTP channel.'];
        }

        $otp = (string) random_int(100000, 999999);
        $maskedDestination = null;
        if ($channel === 'mobile') {
            $normalizedPhone = $this->normalizeIndianPhone((string) $reward->patient_phone);
            if (! $normalizedPhone) {
                return ['success' => false, 'message' => 'Patient mobile number is invalid for OTP.'];
            }
            $send = app(WhatsAppOtpService::class)->sendCustomOtp($normalizedPhone, $otp);
            if (! ($send['success'] ?? false)) {
                return ['success' => false, 'message' => $send['message'] ?? 'Failed to send OTP on mobile.'];
            }
            $maskedDestination = $this->maskPhone($normalizedPhone);
        } else {
            $email = trim((string) ($reward->patient_email ?? ''));
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Valid patient email is required for email OTP.'];
            }
            try {
                Mail::raw(
                    "Your MMHC patient verification OTP is: {$otp}. It expires in 5 minutes.",
                    function ($message) use ($email) {
                        $message->to($email)->subject('MMHC Patient Verification OTP');
                    }
                );
            } catch (\Throwable $e) {
                return ['success' => false, 'message' => 'Could not send OTP email. Please check mail settings.'];
            }
            $maskedDestination = $this->maskEmail($email);
        }

        $reward->update([
            'verification_otp_hash' => Hash::make($otp),
            'verification_otp_expires_at' => now()->addMinutes(5),
            'verification_otp_attempts' => 0,
            'verification_otp_sent_at' => now(),
            'verification_otp_sent_to' => $maskedDestination,
        ]);

        return ['success' => true, 'message' => 'OTP sent successfully.', 'sent_to' => $reward->verification_otp_sent_to];
    }

    public function verifyRewardOtp(CaregiverReward $reward, string $otp): array
    {
        if ($reward->verification_status === 'verified') {
            return ['success' => true, 'message' => 'Already verified.'];
        }
        if (! $reward->verification_otp_hash || ! $reward->verification_otp_expires_at) {
            return ['success' => false, 'message' => 'OTP not generated. Send OTP first.'];
        }
        if (now()->greaterThan($reward->verification_otp_expires_at)) {
            return ['success' => false, 'message' => 'OTP expired. Send OTP again.'];
        }
        if ((int) $reward->verification_otp_attempts >= 3) {
            return ['success' => false, 'message' => 'Maximum attempts reached. Send OTP again.'];
        }
        if (! Hash::check($otp, (string) $reward->verification_otp_hash)) {
            $reward->increment('verification_otp_attempts');
            $remaining = max(0, 3 - (int) $reward->fresh()->verification_otp_attempts);

            return ['success' => false, 'message' => $remaining > 0 ? "Invalid OTP. {$remaining} attempts left." : 'Invalid OTP. No attempts left.'];
        }

        DB::transaction(function () use ($reward) {
            $locked = CaregiverReward::query()->lockForUpdate()->findOrFail($reward->id);
            if ($locked->verification_status !== 'verified') {
                $locked->verification_status = 'verified';
                $locked->verified_at = now();
                $locked->verification_otp_hash = null;
                $locked->verification_otp_expires_at = null;
                $locked->verification_otp_attempts = 0;
                $locked->save();

                $locked->user()->increment('reward_points', (int) $locked->reward_points);
            }
        });

        return ['success' => true, 'message' => 'Reward verified and credited successfully.'];
    }

    /**
     * Calculate reward amount in rupees.
     */
    public function calculateRewardAmount(int $points): float
    {
        return $points * self::POINT_VALUE;
    }

    private function normalizeIndianPhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (! $digits) {
            return null;
        }
        if (strlen($digits) === 10) {
            return '91'.$digits;
        }
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return $digits;
        }

        return null;
    }

    private function maskPhone(string $normalizedPhone): string
    {
        return str_repeat('*', max(0, strlen($normalizedPhone) - 4)).substr($normalizedPhone, -4);
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }
        $name = $parts[0];
        $domain = $parts[1];
        if (strlen($name) <= 2) {
            return str_repeat('*', strlen($name)).'@'.$domain;
        }

        return substr($name, 0, 2).str_repeat('*', max(0, strlen($name) - 2)).'@'.$domain;
    }
}

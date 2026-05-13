<?php

namespace App\Modules\Rewards\Services;

use App\Models\Core\User;
use App\Modules\Auth\Services\ScopedSmsOtpRedisService;
use App\Modules\Auth\Services\SmsOtpService;
use App\Modules\Incentives\Models\IncentiveRuleSet;
use App\Modules\Rewards\Models\CaregiverReward;
use Illuminate\Support\Facades\DB;

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

    /**
     * Send patient verification OTP by SMS only (Sent.dm).
     */
    public function sendVerificationOtp(CaregiverReward $reward): array
    {
        if ($reward->verification_otp_sent_at && $reward->verification_otp_sent_at->gt(now()->subMinutes(15))) {
            return ['success' => false, 'message' => 'Please wait 15 minutes before requesting OTP again.'];
        }

        $otp = (string) random_int(100000, 999999);
        $normalizedPhone = $this->normalizeIndianPhone((string) $reward->patient_phone);
        if (! $normalizedPhone) {
            return ['success' => false, 'message' => 'Patient mobile number is invalid for OTP.'];
        }
        $send = app(SmsOtpService::class)->sendCustomOtp($normalizedPhone, $otp);
        if (! ($send['success'] ?? false)) {
            return ['success' => false, 'message' => $send['message'] ?? 'Failed to send OTP on mobile.'];
        }
        $maskedDestination = $this->maskPhone($normalizedPhone);

        app(ScopedSmsOtpRedisService::class)->store(
            ScopedSmsOtpRedisService::PURPOSE_PATIENT_REWARD,
            (int) $reward->id,
            $otp
        );

        $reward->update([
            'verification_otp_hash' => null,
            'verification_otp_expires_at' => now()->addMinutes(5),
            'verification_otp_attempts' => 0,
            'verification_otp_sent_at' => now(),
            'verification_otp_sent_to' => $maskedDestination,
        ]);

        return ['success' => true, 'message' => 'OTP sent successfully.', 'sent_to' => $reward->verification_otp_sent_to];
    }

    public function verifyRewardOtp(CaregiverReward $reward, string $otp): array
    {
        if ($reward->isPatientMobileOtpVerified()) {
            return ['success' => true, 'message' => 'Already verified.'];
        }
        if (! $reward->verification_otp_expires_at) {
            return ['success' => false, 'message' => 'OTP not generated. Send OTP first.'];
        }
        if (! $reward->verification_otp_sent_at) {
            return ['success' => false, 'message' => 'OTP not generated. Send OTP first.'];
        }
        if (now()->greaterThan($reward->verification_otp_expires_at)) {
            return ['success' => false, 'message' => 'OTP expired. Send OTP again.'];
        }
        if ((int) $reward->verification_otp_attempts >= 3) {
            return ['success' => false, 'message' => 'Maximum attempts reached. Send OTP again.'];
        }

        $otpValid = app(ScopedSmsOtpRedisService::class)->verifyAndConsume(
            ScopedSmsOtpRedisService::PURPOSE_PATIENT_REWARD,
            (int) $reward->id,
            $otp
        );

        if (! $otpValid) {
            $reward->increment('verification_otp_attempts');
            $remaining = max(0, 3 - (int) $reward->fresh()->verification_otp_attempts);

            return ['success' => false, 'message' => $remaining > 0 ? "Invalid OTP. {$remaining} attempts left." : 'Invalid OTP. No attempts left.'];
        }

        $otpSentTo = (string) $reward->verification_otp_sent_to;

        DB::transaction(function () use ($reward, $otpSentTo) {
            $locked = CaregiverReward::query()->lockForUpdate()->findOrFail($reward->id);
            if ($locked->verification_status !== 'verified') {
                $verifiedCountBefore = (int) CaregiverReward::query()
                    ->where('user_id', $locked->user_id)
                    ->verified()
                    ->count();
                $eventCount = $verifiedCountBefore + 1;
                $locked->verification_status = 'verified';
                $locked->verified_at = now();
                $locked->reward_amount = $this->calculateVerifiedRewardAmount($eventCount);
                $locked->verification_otp_hash = null;
                $locked->verification_otp_expires_at = null;
                $locked->verification_otp_attempts = 0;
                $locked->save();

                if (str_starts_with($otpSentTo, 'Mobile')) {
                    $staff = User::query()->lockForUpdate()->find($locked->user_id);
                    $patientDigits = $this->normalizeIndianPhone((string) $locked->patient_phone);
                    $accountDigits = $staff ? $this->normalizeIndianPhone((string) $staff->phone) : null;
                    if ($staff && $patientDigits && $accountDigits && $patientDigits === $accountDigits) {
                        $staff->applyPhoneVerifiedFromPatientRewardSelfMobileOtp();
                    }
                }
            }
            $staff = User::query()->find($locked->user_id);
            if ($staff) {
                $this->syncStaffRewardPoints($staff);
            }
        });

        $staff = $reward->fresh()->user;
        $message = ($staff && $staff->hasVerifiedPhone())
            ? 'Reward verified and points credited.'
            : 'Patient mobile verified. Points credit after you verify your Profile mobile (SMS OTP).';

        return ['success' => true, 'message' => $message];
    }

    /**
     * Staff reward_points = sum of patient-SMS-verified rows only when Profile mobile is also verified.
     */
    public function syncStaffRewardPoints(User $staff): void
    {
        $staff = $staff->fresh();
        if (! $staff) {
            return;
        }

        $points = 0;
        if ($staff->hasVerifiedPhone()) {
            $points = (int) CaregiverReward::query()
                ->where('user_id', $staff->id)
                ->verified()
                ->sum('reward_points');
        }

        if ((int) $staff->reward_points !== $points) {
            $staff->forceFill(['reward_points' => $points])->save();
        }
    }

    /**
     * Calculate reward amount in rupees.
     */
    public function calculateRewardAmount(int $points): float
    {
        return $points * self::POINT_VALUE;
    }

    public function calculateVerifiedRewardAmount(int $verifiedCountAtEvent): float
    {
        $baseAmount = (float) self::POINT_VALUE;
        $ruleSet = IncentiveRuleSet::currentActive();
        if (! $ruleSet) {
            return $baseAmount;
        }

        $eventCount = max(1, $verifiedCountAtEvent);
        $pre = $baseAmount;
        [$growth, $dta] = app(\App\Modules\Incentives\Services\IncentiveCalculatorService::class)
            ->getGrowthDtaPercentages($ruleSet, $eventCount);
        $pre = $pre * (1.0 + ($growth / 100.0)) * (1.0 - ($dta / 100.0));

        return round($pre, (int) $ruleSet->round_decimals);
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

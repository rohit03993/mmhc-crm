<?php

namespace App\Modules\Rewards\Services;

use App\Models\Core\User;
use App\Modules\Auth\Services\ScopedSmsOtpRedisService;
use App\Modules\Auth\Services\SmsOtpService;
use App\Modules\Auth\Services\UserService;
use App\Modules\Incentives\Models\IncentiveRuleSet;
use App\Modules\Rewards\Models\CaregiverReward;
use Illuminate\Support\Facades\DB;

class RewardService
{
    public const POINT_VALUE = 10; // 1 point = ₹10

    public function __construct(
        private PatientRewardAccountService $patientRewardAccountService
    ) {}

    /**
     * Create a reward entry and increment user's reward points.
     */
    public function createReward(User $user, array $data): CaregiverReward
    {
        return $this->createPendingReward($user, $data);
    }

    public function createPendingReward(User $user, array $data): CaregiverReward
    {
        $storedPhone = app(UserService::class)->formatPhoneStorage($data['patient_phone'] ?? '');
        if ($storedPhone === null) {
            throw new \InvalidArgumentException('Invalid patient mobile number.');
        }

        return CaregiverReward::create([
            'user_id' => $user->id,
            'patient_name' => $data['patient_name'],
            'patient_phone' => $storedPhone,
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
     * Send patient verification OTP via WhatsApp (Pal Digital).
     */
    public function sendVerificationOtp(CaregiverReward $reward): array
    {
        $otpExpired = $reward->verification_otp_expires_at
            && now()->greaterThan($reward->verification_otp_expires_at);
        $needsFreshOtp = $otpExpired
            || empty($reward->verification_otp_hash)
            || ! $reward->verification_otp_sent_at;

        if (
            $reward->verification_otp_sent_at
            && $reward->verification_otp_sent_at->gt(now()->subMinutes(15))
            && ! $needsFreshOtp
        ) {
            return ['success' => false, 'message' => 'Please wait 15 minutes before requesting OTP again.'];
        }

        $userService = app(UserService::class);
        $storedPhone = $userService->formatPhoneStorage((string) $reward->patient_phone);
        if ($storedPhone === null) {
            return ['success' => false, 'message' => 'Patient mobile number is invalid for OTP.'];
        }
        if ($storedPhone !== $reward->patient_phone) {
            $reward->forceFill(['patient_phone' => $storedPhone])->save();
        }

        $otp = (string) random_int(100000, 999999);
        $normalizedPhone = $this->normalizeIndianPhone($storedPhone);
        if (! $normalizedPhone) {
            return ['success' => false, 'message' => 'Patient mobile number is invalid for OTP.'];
        }

        $sms = app(SmsOtpService::class);
        $send = $sms->sendCustomOtp($normalizedPhone, $otp, $reward->patient_name);
        $localDevBypass = app()->environment('local') && ! ($send['success'] ?? false);

        if (! ($send['success'] ?? false) && ! $localDevBypass) {
            return ['success' => false, 'message' => $send['message'] ?? 'Failed to send OTP on mobile.'];
        }

        $maskedDestination = $this->maskPhone($normalizedPhone);

        $scopedOtp = app(ScopedSmsOtpRedisService::class);
        $scopedOtp->store(
            ScopedSmsOtpRedisService::PURPOSE_PATIENT_REWARD,
            (int) $reward->id,
            $otp
        );

        $otpDigest = $scopedOtp->buildDigest(
            ScopedSmsOtpRedisService::PURPOSE_PATIENT_REWARD,
            (int) $reward->id,
            $otp
        );

        $reward->update([
            'verification_otp_hash' => $otpDigest,
            'verification_otp_expires_at' => now()->addMinutes(5),
            'verification_otp_attempts' => 0,
            'verification_otp_sent_at' => now(),
            'verification_otp_sent_to' => $maskedDestination,
        ]);

        $message = 'OTP sent successfully to patient mobile.';
        if ($localDevBypass) {
            $message = "Local testing: WhatsApp is not configured. Use OTP {$otp} (also in storage/logs/laravel.log).";
            \Illuminate\Support\Facades\Log::info('Patient reward OTP (local dev)', [
                'reward_id' => $reward->id,
                'patient_phone' => $maskedDestination,
                'otp' => $otp,
            ]);
        }

        return [
            'success' => true,
            'message' => $message,
            'sent_to' => $reward->verification_otp_sent_to,
            'dev_otp' => $localDevBypass ? $otp : null,
        ];
    }

    public function verifyRewardOtp(CaregiverReward $reward, string $otp): array
    {
        if ($reward->isPatientMobileOtpVerified()) {
            return ['success' => true, 'message' => 'Already verified.'];
        }
        if (! $reward->verification_otp_sent_at || ! $reward->verification_otp_expires_at) {
            return ['success' => false, 'message' => 'OTP not sent yet. Tap “Resend OTP to patient mobile” first.'];
        }
        if (now()->greaterThan($reward->verification_otp_expires_at)) {
            return ['success' => false, 'message' => 'OTP expired. Tap “Resend OTP to patient mobile” and enter the new code.'];
        }
        if (empty($reward->verification_otp_hash)) {
            return ['success' => false, 'message' => 'OTP session missing. Tap “Resend OTP to patient mobile” first.'];
        }
        if ((int) $reward->verification_otp_attempts >= 3) {
            return ['success' => false, 'message' => 'Maximum attempts reached. Send OTP again.'];
        }

        $scopedOtp = app(ScopedSmsOtpRedisService::class);
        $otpValid = $scopedOtp->verifyWithDbFallback(
            ScopedSmsOtpRedisService::PURPOSE_PATIENT_REWARD,
            (int) $reward->id,
            $otp,
            (string) ($reward->verification_otp_hash ?? '')
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

                try {
                    $this->patientRewardAccountService->provisionFromVerifiedReward($locked);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
            $staff = User::query()->find($locked->user_id);
            if ($staff) {
                $this->syncStaffRewardPoints($staff);
            }
        });

        $reward = $reward->fresh(['patientUser']);
        $staff = $reward->user;
        $patientUid = $reward->patientUser?->unique_id;
        $baseMessage = ($staff && $staff->hasVerifiedPhone())
            ? 'Reward verified and points credited.'
            : 'Patient mobile verified. Points credit after your account mobile is confirmed (sign in with WhatsApp OTP on that number, or verify in Profile).';
        if ($patientUid) {
            $baseMessage .= " Patient ID: {$patientUid}. They can sign in with this mobile via WhatsApp OTP.";
        }

        return ['success' => true, 'message' => $baseMessage, 'patient_unique_id' => $patientUid];
    }

    /**
     * Change patient mobile on a pending reward and send OTP to the new number.
     */
    public function updatePatientPhone(CaregiverReward $reward, string $phoneInput): array
    {
        if (! $reward->canChangePatientPhone()) {
            return ['success' => false, 'message' => 'Patient mobile cannot be changed after OTP verification or payout.'];
        }

        $userService = app(UserService::class);
        $tenDigitPhone = $userService->parseTenDigitIndianMobile($phoneInput);
        if ($tenDigitPhone === null) {
            return ['success' => false, 'message' => 'Enter a valid 10-digit Indian mobile number (starting with 6, 7, 8, or 9).'];
        }

        $formattedPhone = $userService->formatPhoneStorage($tenDigitPhone);
        $currentTen = $userService->parseTenDigitIndianMobile((string) $reward->patient_phone);

        if ($currentTen === $tenDigitPhone) {
            return $this->sendVerificationOtp($reward->fresh());
        }

        $variants = $userService->phoneStorageVariants($tenDigitPhone);
        $duplicate = CaregiverReward::query()
            ->where('id', '!=', $reward->id)
            ->where(function ($q) use ($variants) {
                $q->whereIn('patient_phone', $variants);
            })
            ->exists();
        if ($duplicate) {
            return ['success' => false, 'message' => 'This mobile number is already used on another patient reward entry.'];
        }

        if ($userService->applyMatchingPhone(
            User::query()->whereIn('role', ['nurse', 'caregiver', 'admin']),
            $tenDigitPhone
        )->exists()) {
            return ['success' => false, 'message' => 'This mobile belongs to a staff account. Use the patient’s personal number.'];
        }

        $reward->forceFill([
            'patient_phone' => $formattedPhone,
            'patient_user_id' => null,
            'verification_status' => 'pending',
            'verification_otp_hash' => null,
            'verification_otp_expires_at' => null,
            'verification_otp_attempts' => 0,
            'verification_otp_sent_at' => null,
            'verification_otp_sent_to' => null,
            'verified_at' => null,
        ])->save();

        return $this->sendVerificationOtp($reward->fresh());
    }

    /**
     * Staff reward_points = sum of patient-WhatsApp-verified rows only when Profile mobile is also verified.
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

    public function formatPatientPhoneForStorage(string $phone): ?string
    {
        return app(UserService::class)->formatPhoneStorage($phone);
    }

    private function normalizeIndianPhone(string $phone): ?string
    {
        $ten = app(UserService::class)->parseTenDigitIndianMobile($phone);

        return $ten !== null ? '91'.$ten : null;
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

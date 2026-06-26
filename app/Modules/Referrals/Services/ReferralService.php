<?php

namespace App\Modules\Referrals\Services;

use App\Models\Core\User;
use App\Modules\Incentives\Models\IncentiveLedger;
use App\Modules\Incentives\Services\IncentiveCalculatorService;
use App\Modules\Auth\Services\ScopedSmsOtpRedisService;
use App\Modules\Auth\Services\SmsOtpService;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Rewards\Services\RewardService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReferralService
{
    public const REWARD_POINTS_PER_REFERRAL = 0;

    public const STAFF_REFERRAL_BASE_INR = 100.00;

    protected $rewardService;

    public function __construct(RewardService $rewardService)
    {
        $this->rewardService = $rewardService;
    }

    /**
     * Generate a unique referral code for a user
     */
    public function generateReferralCode(User $user): string
    {
        // Generate code based on user ID and random string
        $baseCode = strtoupper(substr($user->name, 0, 3)).$user->id.strtoupper(Str::random(4));
        $referralCode = str_replace(' ', '', $baseCode);

        // Ensure uniqueness
        while (Referral::where('referral_code', $referralCode)->exists()) {
            $referralCode = strtoupper(Str::random(10));
        }

        return $referralCode;
    }

    /**
     * Get or create referral code for a user
     * Each user gets one reusable referral code
     */
    public function getOrCreateReferralCode(User $user): string
    {
        // Check if user already has a referral code (any status)
        // We want one reusable code per user
        $existingReferral = Referral::where('referrer_id', $user->id)
            ->orderBy('created_at', 'asc') // Get the first one created
            ->first();

        if ($existingReferral) {
            return $existingReferral->referral_code;
        }

        // Create new referral code for this user
        $referralCode = $this->generateReferralCode($user);

        // Ensure code is unique
        while (Referral::where('referral_code', $referralCode)->exists()) {
            $referralCode = $this->generateReferralCode($user);
        }

        // Create a pending referral record (this will be completed when someone uses it)
        Referral::create([
            'referral_code' => $referralCode,
            'referrer_id' => $user->id,
            'status' => 'pending',
            'reward_points' => self::REWARD_POINTS_PER_REFERRAL,
            'reward_amount' => self::STAFF_REFERRAL_BASE_INR,
        ]);

        return $referralCode;
    }

    /**
     * Get referral link for a user
     */
    public function getReferralLink(User $user): string
    {
        $referralCode = $this->getOrCreateReferralCode($user);

        return route('auth.register', ['ref' => $referralCode]);
    }

    /**
     * Validate referral code
     * Returns the referral record if valid (code can be reused)
     */
    public function validateReferralCode(string $referralCode): ?Referral
    {
        $referral = Referral::where('referral_code', $referralCode)
            ->with('referrer') // Eager load referrer relationship
            ->first();

        if (! $referral) {
            return null;
        }

        // Check if referrer is still active staff (nurse or caregiver)
        $referrer = $referral->referrer;
        if (! $referrer || ! $referrer->isStaff() || ! $referrer->is_active) {
            return null;
        }

        // Code is valid - it can be reused
        // We'll create a new referral record when someone uses it
        return $referral;
    }

    /**
     * Process referral when new user registers
     * Updates pending referral to completed, or creates new if all pending are used
     */
    public function processReferral(string $referralCode, User $newUser): bool
    {
        return DB::transaction(function () use ($referralCode, $newUser) {
            // Validate referral code
            $existingReferral = $this->validateReferralCode($referralCode);

            if (! $existingReferral) {
                return false;
            }

            // Check if new user is nurse or caregiver (only staff can be referred)
            if (! $newUser->isStaff()) {
                return false;
            }

            // Get referrer
            $referrer = $existingReferral->referrer;

            if (! $referrer || ! $referrer->hasVerifiedPhone()) {
                return false;
            }

            // Check if this user was already referred by this referrer
            $alreadyReferred = Referral::where('referrer_id', $referrer->id)
                ->where('referred_id', $newUser->id)
                ->exists();

            if ($alreadyReferred) {
                return false; // Already referred this user
            }

            // First, try to find a pending referral with this code that hasn't been used yet
            $pendingReferral = Referral::where('referral_code', $referralCode)
                ->where('referrer_id', $referrer->id)
                ->where('status', 'pending')
                ->whereNull('referred_id')
                ->first();

            if ($pendingReferral) {
                // Update the pending referral to completed
                $pendingReferral->update([
                    'referred_id' => $newUser->id,
                    'status' => 'pending',
                    'verification_status' => 'pending',
                    'completed_at' => null,
                    'reward_points' => self::REWARD_POINTS_PER_REFERRAL,
                    'reward_amount' => self::STAFF_REFERRAL_BASE_INR,
                ]);

                return $this->sendReferralCompletionOtp($pendingReferral, $newUser);
            }

            // No pending referral found, create a new completed referral record
            // This allows the same code to be reused multiple times
            try {
                $newReferral = Referral::create([
                    'referral_code' => $referralCode,
                    'referrer_id' => $referrer->id,
                    'referred_id' => $newUser->id,
                    'status' => 'pending',
                    'verification_status' => 'pending',
                    'reward_points' => self::REWARD_POINTS_PER_REFERRAL,
                    'reward_amount' => self::STAFF_REFERRAL_BASE_INR,
                    'completed_at' => null,
                ]);

                return $this->sendReferralCompletionOtp($newReferral, $newUser);
            } catch (\Illuminate\Database\QueryException $e) {
                // If duplicate entry error (referrer_id + referred_id), user was already referred
                if ($e->getCode() == 23000) {
                    return false;
                }
                throw $e;
            }
        });
    }

    /**
     * Keep referral incentive ledger in sync with completed referrals.
     * This is non-blocking by design to avoid impacting core registration flow.
     */
    private function syncReferralIncentiveLedger(User $referrer, Referral $referral): void
    {
        try {
            app(IncentiveCalculatorService::class)->createReferralLedger(
                $referrer,
                (int) $referral->id,
                (float) ($referral->reward_amount ?? 0)
            );
        } catch (\Throwable $e) {
            Log::warning('Referral incentive ledger sync failed: '.$e->getMessage(), [
                'referrer_id' => $referrer->id,
                'referral_id' => $referral->id,
            ]);
        }
    }

    private function sendReferralCompletionOtp(Referral $referral, User $referredStaff): bool
    {
        if ($referral->verification_otp_sent_at && $referral->verification_otp_sent_at->gt(now()->subMinutes(15))) {
            return false;
        }
        $otp = (string) random_int(100000, 999999);
        $rawPhone = (string) ($referredStaff->pending_phone ?: $referredStaff->phone);
        $normalizedPhone = $this->normalizeIndianPhone($rawPhone);
        if (! $normalizedPhone) {
            return false;
        }
        $send = app(SmsOtpService::class)->sendCustomOtp($normalizedPhone, $otp, $referredStaff->name);
        if (! ($send['success'] ?? false)) {
            return false;
        }
        $maskedDestination = 'Mobile: '.$this->maskPhone($normalizedPhone);

        app(ScopedSmsOtpRedisService::class)->store(ScopedSmsOtpRedisService::PURPOSE_REFERRAL, (int) $referral->id, $otp);

        $referral->update([
            'verification_otp_hash' => null,
            'verification_otp_expires_at' => now()->addMinutes(5),
            'verification_otp_attempts' => 0,
            'verification_otp_sent_at' => now(),
            'verification_otp_sent_to' => $maskedDestination,
        ]);

        return true;
    }

    public function resendReferralOtpForReferred(User $referredUser): array
    {
        $referral = Referral::query()
            ->where('referred_id', $referredUser->id)
            ->where('status', 'pending')
            ->where('verification_status', 'pending')
            ->latest('id')
            ->first();

        if (! $referral) {
            return ['success' => false, 'message' => 'No pending referral verification found.'];
        }
        if ($referral->verification_otp_sent_at && $referral->verification_otp_sent_at->gt(now()->subMinutes(15))) {
            return ['success' => false, 'message' => 'Please wait 15 minutes before resending OTP.'];
        }

        $ok = $this->sendReferralCompletionOtp($referral, $referredUser);
        if (! $ok) {
            return ['success' => false, 'message' => 'Could not send OTP on mobile right now. Please check your registered mobile number.'];
        }

        return ['success' => true, 'message' => 'Referral OTP resent successfully via WhatsApp.'];
    }

    public function verifyReferralOtpForReferred(User $referredUser, string $otp): array
    {
        $referral = Referral::query()
            ->where('referred_id', $referredUser->id)
            ->where('status', 'pending')
            ->where('verification_status', 'pending')
            ->latest('id')
            ->first();

        if (! $referral) {
            return ['success' => false, 'message' => 'No pending referral OTP found.'];
        }

        $referrer = User::query()->find($referral->referrer_id);
        if (! $referrer || ! $referrer->hasVerifiedPhone()) {
            return ['success' => false, 'message' => 'This referral cannot be completed until your referrer has verified their mobile number in Profile.'];
        }

        if (! $referral->verification_otp_expires_at) {
            return ['success' => false, 'message' => 'OTP not generated for this referral.'];
        }
        if (! $referral->verification_otp_sent_at) {
            return ['success' => false, 'message' => 'OTP not generated for this referral.'];
        }
        if (now()->greaterThan($referral->verification_otp_expires_at)) {
            return ['success' => false, 'message' => 'OTP expired. Ask referrer to trigger again.'];
        }
        if ((int) $referral->verification_otp_attempts >= 3) {
            return ['success' => false, 'message' => 'Maximum OTP attempts reached.'];
        }

        $otpValid = app(ScopedSmsOtpRedisService::class)->verifyAndConsume(
            ScopedSmsOtpRedisService::PURPOSE_REFERRAL,
            (int) $referral->id,
            $otp
        );

        if (! $otpValid) {
            $referral->increment('verification_otp_attempts');

            return ['success' => false, 'message' => 'Invalid OTP.'];
        }

        DB::transaction(function () use ($referral) {
            $locked = Referral::query()->lockForUpdate()->findOrFail($referral->id);
            if ($locked->status !== 'completed') {
                $locked->update([
                    'status' => 'completed',
                    'verification_status' => 'verified',
                    'verified_at' => now(),
                    'completed_at' => now(),
                    'verification_otp_hash' => null,
                    'verification_otp_expires_at' => null,
                    'verification_otp_attempts' => 0,
                    'verification_otp_sent_at' => null,
                    'verification_otp_sent_to' => null,
                ]);
                $referrer = User::query()->find($locked->referrer_id);
                if ($referrer) {
                    $this->syncReferralIncentiveLedger($referrer, $locked);
                }
                $referred = User::query()->find($locked->referred_id);
                if ($referred) {
                    $referred->applyPhoneVerifiedFromReferralMobileOtp();
                }
            }
        });

        return ['success' => true, 'message' => 'Referral OTP verified. Referral earnings unlocked.'];
    }

    /**
     * Get referral statistics for a user
     */
    public function getReferralStats(User $user): array
    {
        $this->syncMissingReferralLedgersForReferrer($user);

        $totalReferrals = Referral::where('referrer_id', $user->id)->count();
        $completedReferrals = Referral::where('referrer_id', $user->id)
            ->referralMobileOtpVerified()
            ->where('status', 'completed')
            ->count();
        $pendingReferrals = Referral::where('referrer_id', $user->id)
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere('verification_status', 'pending')
                    ->orWhere(function ($legacy) {
                        $legacy->where('status', 'completed')
                            ->where(function ($nullOrPending) {
                                $nullOrPending->whereNull('verification_status')
                                    ->orWhere('verification_status', '!=', 'verified');
                            });
                    });
            })
            ->count();
        $totalRewardPoints = Referral::where('referrer_id', $user->id)
            ->referralMobileOtpVerified()
            ->where('status', 'completed')
            ->sum('reward_points');
        $totalRewardAmount = IncentiveLedger::query()
            ->where('staff_id', $user->id)
            ->where('source_type', IncentiveLedger::SOURCE_REFERRAL)
            ->sum('final_amount');
        if ((float) $totalRewardAmount <= 0) {
            $totalRewardAmount = Referral::where('referrer_id', $user->id)
                ->referralMobileOtpVerified()
                ->where('status', 'completed')
                ->sum('reward_amount');
        }

        return [
            'total_referrals' => $totalReferrals,
            'completed_referrals' => $completedReferrals,
            'pending_referrals' => $pendingReferrals,
            'total_reward_points' => $totalRewardPoints,
            'total_reward_amount' => $totalRewardAmount,
        ];
    }

    /**
     * Backfill referral ledgers for already-completed referrals missing ledger rows.
     * Safe to run repeatedly due to updateOrCreate in incentive service.
     */
    private function syncMissingReferralLedgersForReferrer(User $referrer): void
    {
        if (! $referrer->isStaff()) {
            return;
        }

        if (! $referrer->hasVerifiedPhone()) {
            return;
        }

        $existingLedgerReferralIds = \App\Modules\Incentives\Models\IncentiveLedger::query()
            ->where('staff_id', $referrer->id)
            ->where('source_type', \App\Modules\Incentives\Models\IncentiveLedger::SOURCE_REFERRAL)
            ->pluck('source_id');

        $missingCompletedReferrals = Referral::query()
            ->where('referrer_id', $referrer->id)
            ->referralMobileOtpVerified()
            ->where('status', 'completed')
            ->whereNotNull('referred_id')
            ->when($existingLedgerReferralIds->isNotEmpty(), function ($query) use ($existingLedgerReferralIds) {
                $query->whereNotIn('id', $existingLedgerReferralIds);
            })
            ->get();

        if ($missingCompletedReferrals->isEmpty()) {
            return;
        }

        foreach ($missingCompletedReferrals as $referral) {
            $base = (float) ($referral->reward_amount ?? 0);
            if ($base <= 0) {
                $base = self::STAFF_REFERRAL_BASE_INR;
            }
            $this->syncReferralIncentiveLedger($referrer, $referral->forceFill([
                'reward_amount' => $base,
            ]));
        }
    }

    /**
     * Get referral history for a user
     * Only shows completed referrals (with referred_id)
     */
    public function getReferralHistory(User $user, int $limit = 10)
    {
        return Referral::where('referrer_id', $user->id)
            ->referralMobileOtpVerified()
            ->where('status', 'completed')
            ->whereNotNull('referred_id')
            ->with('referred')
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get();
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
}

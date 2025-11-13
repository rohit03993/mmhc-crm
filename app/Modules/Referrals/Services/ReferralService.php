<?php

namespace App\Modules\Referrals\Services;

use App\Models\Core\User;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Rewards\Services\RewardService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralService
{
    public const REWARD_POINTS_PER_REFERRAL = 1; // 1 point = ₹10
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
        $baseCode = strtoupper(substr($user->name, 0, 3)) . $user->id . strtoupper(Str::random(4));
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
            'reward_amount' => $this->rewardService->calculateRewardAmount(self::REWARD_POINTS_PER_REFERRAL),
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
            ->first();
        
        if (!$referral) {
            return null;
        }
        
        // Check if referrer is still active staff (nurse or caregiver)
        $referrer = $referral->referrer;
        if (!$referrer || !$referrer->isStaff() || !$referrer->is_active) {
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
            
            if (!$existingReferral) {
                return false;
            }
            
            // Check if new user is nurse or caregiver (only staff can be referred)
            if (!$newUser->isStaff()) {
                return false;
            }
            
            // Get referrer
            $referrer = $existingReferral->referrer;
            
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
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                
                // Award reward points to referrer
                $referrer->increment('reward_points', $pendingReferral->reward_points);
                
                return true;
            }
            
            // No pending referral found, create a new completed referral record
            // This allows the same code to be reused multiple times
            try {
                $newReferral = Referral::create([
                    'referral_code' => $referralCode,
                    'referrer_id' => $referrer->id,
                    'referred_id' => $newUser->id,
                    'status' => 'completed',
                    'reward_points' => self::REWARD_POINTS_PER_REFERRAL,
                    'reward_amount' => $this->rewardService->calculateRewardAmount(self::REWARD_POINTS_PER_REFERRAL),
                    'completed_at' => now(),
                ]);
                
                // Award reward points to referrer
                $referrer->increment('reward_points', $newReferral->reward_points);
                
                return true;
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
     * Get referral statistics for a user
     */
    public function getReferralStats(User $user): array
    {
        $totalReferrals = Referral::where('referrer_id', $user->id)->count();
        $completedReferrals = Referral::where('referrer_id', $user->id)
            ->where('status', 'completed')
            ->count();
        $pendingReferrals = Referral::where('referrer_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $totalRewardPoints = Referral::where('referrer_id', $user->id)
            ->where('status', 'completed')
            ->sum('reward_points');
        $totalRewardAmount = Referral::where('referrer_id', $user->id)
            ->where('status', 'completed')
            ->sum('reward_amount');
        
        return [
            'total_referrals' => $totalReferrals,
            'completed_referrals' => $completedReferrals,
            'pending_referrals' => $pendingReferrals,
            'total_reward_points' => $totalRewardPoints,
            'total_reward_amount' => $totalRewardAmount,
        ];
    }

    /**
     * Get referral history for a user
     * Only shows completed referrals (with referred_id)
     */
    public function getReferralHistory(User $user, int $limit = 10)
    {
        return Referral::where('referrer_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('referred_id')
            ->with('referred')
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get();
    }
}


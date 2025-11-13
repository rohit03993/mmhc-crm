<?php

namespace App\Modules\Rewards\Services;

use App\Models\Core\User;
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
        return DB::transaction(function () use ($user, $data) {
            $reward = CaregiverReward::create([
                'user_id' => $user->id,
                'patient_name' => $data['patient_name'],
                'patient_phone' => $data['patient_phone'],
                'patient_age' => $data['patient_age'] ?? null,
                'patient_address' => $data['patient_address'] ?? null,
                'patient_pincode' => $data['patient_pincode'] ?? null,
                'hospital_name' => $data['hospital_name'],
                'treatment_details' => $data['treatment_details'] ?? null,
                'reward_points' => 1,
                'reward_amount' => self::POINT_VALUE,
            ]);

            $user->increment('reward_points', $reward->reward_points);

            return $reward;
        });
    }

    /**
     * Calculate reward amount in rupees.
     */
    public function calculateRewardAmount(int $points): float
    {
        return $points * self::POINT_VALUE;
    }
}


<?php

namespace App\Modules\Rewards\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaregiverReward extends Model
{
    protected $fillable = [
        'user_id',
        'patient_name',
        'patient_phone',
        'patient_age',
        'patient_address',
        'patient_pincode',
        'hospital_name',
        'treatment_details',
        'reward_points',
        'reward_amount',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Core\User::class);
    }
}


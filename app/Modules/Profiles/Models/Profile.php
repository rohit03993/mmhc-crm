<?php

namespace App\Modules\Profiles\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\User;

class Profile extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'bio',
        'experience_years',
        'specialization',
        'availability_status',
        'avatar_path',
        'is_profile_complete',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_profile_complete' => 'boolean',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if profile is complete
     */
    public function isComplete()
    {
        return $this->is_profile_complete;
    }

    /**
     * Get profile completion percentage
     */
    public function getCompletionPercentage()
    {
        $fields = ['bio', 'experience_years', 'specialization', 'availability_status', 'avatar_path'];
        $completed = 0;

        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }

        // Add basic user fields
        $userFields = ['name', 'email', 'phone', 'address', 'date_of_birth'];
        foreach ($userFields as $field) {
            if (!empty($this->user->$field)) {
                $completed++;
            }
        }

        $totalFields = count($fields) + count($userFields);
        return round(($completed / $totalFields) * 100);
    }

    /**
     * Scope for complete profiles
     */
    public function scopeComplete($query)
    {
        return $query->where('is_profile_complete', true);
    }

    /**
     * Scope for incomplete profiles
     */
    public function scopeIncomplete($query)
    {
        return $query->where('is_profile_complete', false);
    }
}

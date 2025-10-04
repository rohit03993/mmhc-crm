<?php

namespace App\Modules\Profiles\Services;

use App\Models\Core\User;
use App\Modules\Profiles\Models\Profile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    /**
     * Get or create user profile
     */
    public function getProfile(User $user): Profile
    {
        return Profile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'bio' => null,
                'experience_years' => null,
                'specialization' => null,
                'availability_status' => 'available',
                'avatar_path' => null,
                'is_profile_complete' => false,
            ]
        );
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $profileData): Profile
    {
        $profile = $this->getProfile($user);
        
        // Update user basic info
        $user->update([
            'name' => $profileData['name'],
            'phone' => $profileData['phone'],
            'address' => $profileData['address'] ?? null,
            'date_of_birth' => $profileData['date_of_birth'] ?? null,
        ]);

        // Update profile specific info
        $profile->update([
            'bio' => $profileData['bio'] ?? null,
            'experience_years' => $profileData['experience_years'] ?? null,
            'specialization' => $profileData['specialization'] ?? null,
            'availability_status' => $profileData['availability_status'] ?? 'available',
            'is_profile_complete' => $this->checkProfileCompletion($profile),
        ]);

        return $profile->fresh();
    }

    /**
     * Upload avatar
     */
    public function uploadAvatar(User $user, $avatarFile): string
    {
        // Delete old avatar if exists
        $profile = $this->getProfile($user);
        if ($profile->avatar_path && Storage::exists($profile->avatar_path)) {
            Storage::delete($profile->avatar_path);
        }

        // Store new avatar
        $avatarPath = $avatarFile->store('avatars/' . $user->id, 'public');
        
        // Update profile with new avatar path
        $profile->update(['avatar_path' => $avatarPath]);

        return $avatarPath;
    }

    /**
     * Check if profile is complete
     */
    protected function checkProfileCompletion(Profile $profile): bool
    {
        $requiredFields = ['bio', 'experience_years', 'specialization'];
        $userFields = ['name', 'email', 'phone', 'address'];
        
        foreach ($requiredFields as $field) {
            if (empty($profile->$field)) {
                return false;
            }
        }

        foreach ($userFields as $field) {
            if (empty($profile->user->$field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get profile statistics
     */
    public function getProfileStats(): array
    {
        return [
            'total_profiles' => Profile::count(),
            'complete_profiles' => Profile::complete()->count(),
            'incomplete_profiles' => Profile::incomplete()->count(),
            'caregiver_profiles' => Profile::whereHas('user', function($query) {
                $query->where('role', 'caregiver');
            })->count(),
            'patient_profiles' => Profile::whereHas('user', function($query) {
                $query->where('role', 'patient');
            })->count(),
        ];
    }

    /**
     * Search profiles
     */
    public function searchProfiles(string $query, string $role = null)
    {
        $profiles = Profile::with('user');

        if ($role) {
            $profiles->whereHas('user', function($q) use ($role) {
                $q->where('role', $role);
            });
        }

        return $profiles->where(function ($q) use ($query) {
            $q->where('bio', 'like', "%{$query}%")
              ->orWhere('specialization', 'like', "%{$query}%")
              ->orWhereHas('user', function($userQuery) use ($query) {
                  $userQuery->where('name', 'like', "%{$query}%")
                           ->orWhere('email', 'like', "%{$query}%");
              });
        })->paginate(15);
    }
}

<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\User;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Generate unique ID for user based on role
     */
    public function generateUniqueId(string $role): string
    {
        $prefix = match($role) {
            'caregiver' => 'C-UID',
            'patient' => 'P-UID',
            'admin' => 'M-UID',
            default => 'U-UID'
        };

        // Get the highest existing number for this role
        $maxId = User::where('role', $role)
                    ->where('unique_id', 'like', $prefix . '-%')
                    ->selectRaw('CAST(SUBSTRING(unique_id, ' . (strlen($prefix) + 2) . ') AS UNSIGNED) as id_num')
                    ->orderBy('id_num', 'desc')
                    ->first();

        $nextNumber = $maxId ? $maxId->id_num + 1 : 1;
        
        // Ensure uniqueness by checking if the ID already exists
        do {
            $uniqueId = $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            $exists = User::where('unique_id', $uniqueId)->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);
        
        return $uniqueId;
    }

    /**
     * Create a new user
     */
    public function createUser(array $userData): User
    {
        $userData['unique_id'] = $this->generateUniqueId($userData['role']);
        
        return User::create($userData);
    }

    /**
     * Update user information
     */
    public function updateUser(User $user, array $userData): bool
    {
        return $user->update($userData);
    }

    /**
     * Activate user account
     */
    public function activateUser(User $user): bool
    {
        return $user->update(['is_active' => true]);
    }

    /**
     * Deactivate user account
     */
    public function deactivateUser(User $user): bool
    {
        return $user->update(['is_active' => false]);
    }

    /**
     * Get users by role
     */
    public function getUsersByRole(string $role)
    {
        return User::where('role', $role)->active()->get();
    }

    /**
     * Search users
     */
    public function searchUsers(string $query, string $role = null)
    {
        $users = User::query();

        if ($role) {
            $users->where('role', $role);
        }

        return $users->where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('unique_id', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%");
        })->active()->get();
    }

    /**
     * Get user statistics
     */
    public function getUserStats(): array
    {
        return [
            'total' => User::count(),
            'active' => User::active()->count(),
            'caregivers' => User::role('caregiver')->count(),
            'patients' => User::role('patient')->count(),
            'admins' => User::role('admin')->count(),
        ];
    }
}

<?php

namespace App\Modules\Auth\Services;

use App\Models\Core\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    /** Starting sequence number for Patient, Nurse, Caregiver (displayed as 022101) */
    public const UNIQUE_ID_START_NUMBER = 22101;

    /**
     * Generate unique ID for user based on role.
     * Patient: P-UID-022101, P-UID-022102, ...
     * Nurse: N-UID-022101, N-UID-022102, ...
     * Caregiver: C-UID-022101, C-UID-022102, ...
     * Admin: M-UID-000001, ... (unchanged)
     */
    public function generateUniqueId(string $role): string
    {
        $prefix = match ($role) {
            'nurse' => 'N-UID',
            'caregiver' => 'C-UID',
            'patient' => 'P-UID',
            'admin' => 'M-UID',
            'institution_admin' => 'ACAD-IA',
            'faculty' => 'ACAD-F',
            'student' => 'ACAD-ST',
            default => 'U-UID',
        };

        $startNumber = in_array($role, ['patient', 'nurse', 'caregiver'], true)
            ? self::UNIQUE_ID_START_NUMBER
            : 1;

        // Get the highest existing number for this role (numeric part after prefix-)
        $maxId = User::where('role', $role)
            ->where('unique_id', 'like', $prefix.'-%')
            ->selectRaw('CAST(SUBSTRING(unique_id, '.(strlen($prefix) + 2).') AS UNSIGNED) as id_num')
            ->orderBy('id_num', 'desc')
            ->first();

        $nextNumber = $maxId ? max($maxId->id_num + 1, $startNumber) : $startNumber;

        // Ensure uniqueness by checking if the ID already exists
        do {
            $uniqueId = $prefix.'-'.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
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
    public function searchUsers(string $query, ?string $role = null)
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
            'nurses' => User::role('nurse')->count(),
            'patients' => User::role('patient')->count(),
            'admins' => User::role('admin')->count(),
            'staff' => User::whereIn('role', ['caregiver', 'nurse'])->count(),
        ];
    }

    /**
     * Delete all users except admins
     *
     * @return int Number of users deleted
     */
    public function deleteAllNonAdminUsers(): int
    {
        return User::whereNotIn('role', User::protectedFromBulkUserDeletionRoleSlugs())->delete();
    }

    /**
     * Normalize Indian phone number
     * Accepts 10-digit number and adds +91 prefix
     * Also handles numbers that already have +91 or 91 prefix
     *
     * @return string Normalized phone number in format +91XXXXXXXXXX
     */
    public function normalizePhone(string $phone): string
    {
        // If already in correct format, return as is
        if (strpos($phone, '+91') === 0 && strlen($phone) === 13) {
            return $phone;
        }

        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        // If it starts with 91 and has 12 digits, remove the 91 prefix
        if (strlen($digits) === 12 && substr($digits, 0, 2) === '91') {
            $digits = substr($digits, 2);
        }

        // If it's 10 digits, add +91 prefix
        if (strlen($digits) === 10) {
            return '+91'.$digits;
        }

        // If somehow we have invalid length, try to extract last 10 digits
        if (strlen($digits) > 10) {
            $digits = substr($digits, -10);

            return '+91'.$digits;
        }

        // Return with +91 prefix (will handle edge cases)
        return '+91'.$digits;
    }

    public const INDIAN_MOBILE_TEN_PATTERN = '/^[6-9][0-9]{9}$/';

    /**
     * Parse and validate → 10-digit core, or null if invalid.
     */
    public function parseTenDigitIndianMobile(?string $input): ?string
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        $ten = $this->extractPhoneDigits($input);

        return preg_match(self::INDIAN_MOBILE_TEN_PATTERN, $ten) ? $ten : null;
    }

    /**
     * Display: +91 XXXXXXXXXX
     */
    public function formatPhoneDisplay(?string $phone): string
    {
        $ten = $this->parseTenDigitIndianMobile($phone);
        if ($ten === null) {
            return $phone !== null && trim($phone) !== '' ? trim($phone) : '—';
        }

        return '+91 '.$ten;
    }

    /**
     * Storage for reward/patient forms: +91XXXXXXXXXX
     */
    public function formatPhoneStorage(?string $input): ?string
    {
        $ten = $this->parseTenDigitIndianMobile($input);

        return $ten !== null ? '+91'.$ten : null;
    }

    /**
     * Extract 10-digit phone number from normalized format
     *
     * @param  string  $phone  Normalized phone (e.g., +91XXXXXXXXXX)
     * @return string 10-digit phone number
     */
    public function extractPhoneDigits(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        // Remove 91 prefix if present
        if (strlen($digits) === 12 && substr($digits, 0, 2) === '91') {
            return substr($digits, 2);
        }

        // Return last 10 digits
        return substr($digits, -10);
    }

    /**
     * All common DB storage shapes for the same Indian mobile (10-digit core).
     *
     * @return list<string>
     */
    public function phoneStorageVariants(string $phoneOrDigits): array
    {
        $ten = $this->extractPhoneDigits($phoneOrDigits);

        return array_values(array_unique([
            $ten,
            '+91'.$ten,
            '91'.$ten,
        ]));
    }

    /**
     * Restrict a users query to rows whose phone matches the given input (any legacy format).
     */
    public function applyMatchingPhone(\Illuminate\Database\Eloquent\Builder $query, string $phoneOrDigits): \Illuminate\Database\Eloquent\Builder
    {
        $variants = $this->phoneStorageVariants($phoneOrDigits);

        return $query->where(function ($q) use ($variants) {
            $q->whereIn('phone', $variants);
        });
    }

    public function findActiveUserByPhone(string $phoneOrDigits): ?User
    {
        $ten = $this->extractPhoneDigits($phoneOrDigits);
        if (! preg_match('/^[6-9][0-9]{9}$/', $ten)) {
            return null;
        }

        return $this->applyMatchingPhone(User::query(), $phoneOrDigits)
            ->where('is_active', true)
            ->first();
    }

    public function phoneAlreadyRegistered(string $phoneOrDigits, ?int $exceptUserId = null): bool
    {
        $query = $this->applyMatchingPhone(User::query(), $phoneOrDigits);
        if ($exceptUserId !== null) {
            $query->where('id', '!=', $exceptUserId);
        }

        return $query->exists();
    }

    /**
     * Internal placeholder so DB unique email constraint is satisfied without asking users.
     */
    public function placeholderEmailForPhone(string $phoneOrDigits): string
    {
        $ten = $this->extractPhoneDigits($phoneOrDigits);

        return $ten.'@phone.themmhc.com';
    }

    public function isPlaceholderEmail(?string $email): bool
    {
        return $email !== null && str_ends_with(strtolower($email), '@phone.themmhc.com');
    }

    /**
     * Phone-first self-registration: normalized phone, WhatsApp login flag, synthetic email.
     *
     * @param  array<string, mixed>  $userData
     */
    public function applySelfRegistrationIdentity(array &$userData, string $normalizedPhone): void
    {
        $userData['phone'] = $normalizedPhone;
        $userData['email'] = $this->placeholderEmailForPhone($normalizedPhone);
        $userData['login_via_phone_only'] = true;
        $userData['password'] = $this->randomPhoneOnlyPasswordHash();
    }

    /**
     * Unusable random password for WhatsApp-only accounts (DB column still required).
     */
    public function randomPhoneOnlyPasswordHash(): string
    {
        return Hash::make(Str::random(64));
    }
}

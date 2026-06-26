<?php

namespace App\Modules\Profiles\Services;

use App\Models\Core\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StaffIdCardService
{
    /**
     * Staff ID cards require nurse/caregiver role and OTP-verified mobile.
     */
    public function canIssueIdCard(User $user): bool
    {
        return $user->isStaff() && $user->hasVerifiedPhone();
    }

    /**
     * Human-readable reason when an ID card cannot be issued (staff only).
     */
    public function idCardUnavailableMessage(User $user): ?string
    {
        if (! $user->isStaff()) {
            return null;
        }

        if (! $user->hasVerifiedPhone()) {
            return 'Verify the mobile number on Profile (WhatsApp OTP) before issuing an ID card.';
        }

        return null;
    }

    /**
     * Build display payload for ID card and verification pages (live DB data).
     *
     * @return array<string, mixed>
     */
    public function buildCardData(User $user): array
    {
        $user->loadMissing('profile');

        $role = $user->role;
        $isNurse = $role === 'nurse';

        $avatarPath = $user->profile?->avatar_path;
        $avatarUrl = $avatarPath
            ? url(Storage::disk('public')->url($avatarPath))
            : null;

        return [
            'user' => $user,
            'name' => $user->name,
            'unique_id' => $user->unique_id,
            'phone' => $user->displayPhone(),
            'address' => trim((string) $user->address) !== '' ? $user->address : 'Not provided',
            'date_of_birth' => $user->getFormattedDateOfBirth(),
            'role' => $role,
            'role_label' => $isNurse ? 'Nurse' : 'Caregiver',
            'role_tag' => strtoupper($isNurse ? 'NURSE' : 'CAREGIVER'),
            'accent' => $isNurse ? 'nurse' : 'caregiver',
            'avatar_url' => $avatarUrl,
            'initials' => $this->initialsFromName($user->name),
            'verify_url' => route('staff.verify', ['uniqueId' => $user->unique_id]),
            'is_active' => (bool) $user->is_active,
        ];
    }

    public function initialsFromName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($parts === []) {
            return '?';
        }

        if (count($parts) === 1) {
            return Str::upper(Str::substr($parts[0], 0, 2));
        }

        return Str::upper(Str::substr($parts[0], 0, 1).Str::substr(end($parts), 0, 1));
    }
}

<?php

namespace App\Services\AccountDeletion;

use App\Models\Core\User;
use Illuminate\Support\Str;

class UserContactRelease
{
    /**
     * Free unique phone/email so the same contact can register again.
     *
     * @return array{phone: ?string, email: ?string}
     */
    public function tombstoneContacts(User $user): array
    {
        $originalPhone = $user->phone;
        $originalEmail = $user->email;

        $updates = [
            'pending_phone' => null,
            'pending_email' => null,
            'contact_update_channel' => null,
            'contact_update_otp_hash' => null,
            'contact_update_otp_expires_at' => null,
            'contact_update_otp_attempts' => 0,
            'contact_update_otp_sent_to' => null,
            'contact_update_otp_sent_at' => null,
            'contact_update_verified_at' => null,
            'is_active' => false,
            'name' => $this->anonymizedName($user),
        ];

        if ($originalPhone) {
            $updates['phone'] = $this->tombstonePhone($user->id, $originalPhone);
        }

        if ($originalEmail) {
            $updates['email'] = $this->tombstoneEmail($user->id, $originalEmail);
        }

        $user->forceFill($updates)->save();

        return [
            'phone' => $originalPhone,
            'email' => $originalEmail,
        ];
    }

    private function anonymizedName(User $user): string
    {
        $label = $user->unique_id ?: 'User #'.$user->id;

        return 'Removed account ('.$label.')';
    }

    private function tombstonePhone(int $userId, string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?: 'unknown';

        return '__deleted_'.$userId.'_'.$digits.'__';
    }

    private function tombstoneEmail(int $userId, string $email): string
    {
        $hash = substr(hash('sha256', strtolower($email)), 0, 16);

        return '__deleted_'.$userId.'_'.$hash.'@deleted.mmhc.local';
    }
}

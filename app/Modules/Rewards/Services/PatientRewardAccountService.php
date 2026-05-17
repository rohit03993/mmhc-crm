<?php

namespace App\Modules\Rewards\Services;

use App\Models\Core\User;
use App\Modules\Auth\Services\UserService;
use App\Modules\Rewards\Models\CaregiverReward;
use Illuminate\Support\Facades\DB;

/**
 * Create or link a patient User (P-UID) when a caregiver reward patient OTP is verified.
 */
class PatientRewardAccountService
{
    public function __construct(
        private UserService $userService
    ) {}

    public function provisionFromVerifiedReward(CaregiverReward $reward): User
    {
        return DB::transaction(function () use ($reward) {
            $locked = CaregiverReward::query()->lockForUpdate()->findOrFail($reward->id);

            if ($locked->patient_user_id) {
                $existing = User::query()->find($locked->patient_user_id);
                if ($existing && $existing->isPatient()) {
                    $this->markPhoneVerified($existing);
                    $this->syncProfileFromReward($existing, $locked);

                    return $existing->fresh();
                }
            }

            $ten = $this->userService->extractPhoneDigits((string) $locked->patient_phone);
            if (! preg_match('/^[6-9][0-9]{9}$/', $ten)) {
                throw new \InvalidArgumentException('Invalid patient mobile on reward record.');
            }

            $staffOnPhone = $this->userService->applyMatchingPhone(
                User::query()->whereIn('role', ['nurse', 'caregiver', 'admin']),
                $ten
            )->exists();
            if ($staffOnPhone) {
                throw new \RuntimeException('This mobile belongs to a staff account and cannot be used as a patient.');
            }

            $patient = $this->userService->applyMatchingPhone(
                User::query()->where('role', 'patient'),
                $ten
            )->first();

            if ($patient) {
                $locked->forceFill(['patient_user_id' => $patient->id])->save();
                $this->syncProfileFromReward($patient, $locked);
                $this->markPhoneVerified($patient);

                return $patient->fresh();
            }

            $normalized = '91'.$ten;
            $userData = [
                'name' => (string) $locked->patient_name,
                'role' => 'patient',
                'address' => $locked->patient_address,
                'pincode' => $locked->patient_pincode,
                'is_active' => true,
                'password' => bcrypt(str()->random(32)),
            ];

            $this->userService->applySelfRegistrationIdentity($userData, $normalized);
            $patient = $this->userService->createUser($userData);
            $this->markPhoneVerified($patient);

            $locked->forceFill(['patient_user_id' => $patient->id])->save();

            return $patient->fresh();
        });
    }

    private function syncProfileFromReward(User $patient, CaregiverReward $reward): void
    {
        $updates = [];
        if (! empty($reward->patient_name) && trim((string) $patient->name) === '') {
            $updates['name'] = $reward->patient_name;
        }
        if (! empty($reward->patient_address) && empty($patient->address)) {
            $updates['address'] = $reward->patient_address;
        }
        if (! empty($reward->patient_pincode) && empty($patient->pincode)) {
            $updates['pincode'] = $reward->patient_pincode;
        }
        if ($updates !== []) {
            $patient->forceFill($updates)->save();
        }
    }

    private function markPhoneVerified(User $patient): void
    {
        if ($patient->phone_verified_at) {
            return;
        }

        $patient->forceFill([
            'phone_verified_at' => now(),
            'phone_verified_source' => 'patient_reward',
        ])->save();
    }
}

<?php

namespace App\Modules\Academics\Services;

use App\Models\Core\User;
use App\Modules\Academics\Models\Mentorship;
use Illuminate\Validation\ValidationException;

class MentorshipService
{
    /** @return array<int, string> */
    public static function menteeRoleSlugs(): array
    {
        return ['student', 'nurse', 'caregiver'];
    }

    public function assertMenteeEligible(User $user): void
    {
        if (! in_array($user->role, self::menteeRoleSlugs(), true)) {
            throw ValidationException::withMessages([
                'mentor_id' => 'Only students, nurses, and caregivers can request mentorship.',
            ]);
        }
    }

    public function assertMentorEligible(User $user): void
    {
        if ($user->role !== 'faculty' || ! $user->is_active) {
            throw ValidationException::withMessages([
                'mentor_id' => 'Selected faculty is not available for mentorship.',
            ]);
        }
    }

    public function request(User $mentee, User $mentor, ?string $message = null): Mentorship
    {
        $this->assertMenteeEligible($mentee);
        $this->assertMentorEligible($mentor);

        if ($mentee->id === $mentor->id) {
            throw ValidationException::withMessages(['mentor_id' => 'You cannot mentor yourself.']);
        }

        $existing = Mentorship::query()
            ->where('mentee_id', $mentee->id)
            ->where('mentor_id', $mentor->id)
            ->first();

        if ($existing && in_array($existing->status, [Mentorship::STATUS_PENDING, Mentorship::STATUS_ACTIVE], true)) {
            throw ValidationException::withMessages(['mentor_id' => 'You already have a pending or active request with this faculty.']);
        }

        if ($existing) {
            $existing->update([
                'status' => Mentorship::STATUS_PENDING,
                'request_message' => $message,
                'response_message' => null,
                'responded_at' => null,
            ]);

            return $existing->fresh();
        }

        return Mentorship::create([
            'mentee_id' => $mentee->id,
            'mentor_id' => $mentor->id,
            'status' => Mentorship::STATUS_PENDING,
            'request_message' => $message,
        ]);
    }

    public function respond(Mentorship $mentorship, User $mentor, bool $accept, ?string $message = null): Mentorship
    {
        if ((int) $mentorship->mentor_id !== (int) $mentor->id) {
            abort(403);
        }
        if (! $mentorship->isPending()) {
            throw ValidationException::withMessages(['status' => 'This request has already been handled.']);
        }

        $mentorship->update([
            'status' => $accept ? Mentorship::STATUS_ACTIVE : Mentorship::STATUS_DECLINED,
            'response_message' => $message,
            'responded_at' => now(),
        ]);

        return $mentorship->fresh();
    }

    public function activeMentorIdsFor(User $mentee): array
    {
        return Mentorship::query()
            ->where('mentee_id', $mentee->id)
            ->where('status', Mentorship::STATUS_ACTIVE)
            ->pluck('mentor_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function activeMenteeCountFor(User $mentor): int
    {
        return Mentorship::query()
            ->where('mentor_id', $mentor->id)
            ->where('status', Mentorship::STATUS_ACTIVE)
            ->count();
    }

    public function activeMentorCountFor(User $mentee): int
    {
        return Mentorship::query()
            ->where('mentee_id', $mentee->id)
            ->where('status', Mentorship::STATUS_ACTIVE)
            ->count();
    }
}

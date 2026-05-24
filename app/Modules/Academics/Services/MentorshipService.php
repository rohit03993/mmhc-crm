<?php

namespace App\Modules\Academics\Services;

use App\Models\Core\User;
use App\Modules\Academics\Models\Mentorship;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Models\SubmissionMentorShare;
use Illuminate\Support\Facades\DB;
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

    /**
     * Whether the viewer may open the limited mentorship profile (not admin CRM view).
     */
    public function canViewLimitedProfile(User $viewer, User $target): bool
    {
        if ($viewer->id === $target->id) {
            return false;
        }

        if (in_array($viewer->role, ['super_admin', 'admin', 'institution_admin'], true)) {
            return false;
        }

        if (in_array($viewer->role, self::menteeRoleSlugs(), true) && $target->role === 'faculty') {
            return (bool) $target->is_active;
        }

        if ($viewer->role === 'faculty' && in_array($target->role, self::menteeRoleSlugs(), true)) {
            return $this->facultyHasMentorshipContextWith($viewer, $target);
        }

        return false;
    }

    public function facultyHasMentorshipContextWith(User $faculty, User $mentee): bool
    {
        $hasMentorship = Mentorship::query()
            ->where('mentor_id', $faculty->id)
            ->where('mentee_id', $mentee->id)
            ->whereIn('status', [
                Mentorship::STATUS_PENDING,
                Mentorship::STATUS_ACTIVE,
                Mentorship::STATUS_DECLINED,
            ])
            ->exists();

        if ($hasMentorship) {
            return true;
        }

        return SubmissionMentorShare::query()
            ->where('mentor_id', $faculty->id)
            ->whereHas('submission', fn ($q) => $q->where('user_id', $mentee->id))
            ->exists();
    }

    public function menteeHasMentorshipLinkWith(User $mentee, User $faculty): bool
    {
        return Mentorship::query()
            ->where('mentee_id', $mentee->id)
            ->where('mentor_id', $faculty->id)
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function buildLimitedProfile(User $target): array
    {
        $target->load(['profile', 'academicInstitution']);

        $batches = $target->academicBatches()
            ->with('institution')
            ->orderBy('name')
            ->get();

        $data = [
            'person' => $target,
            'profile' => $target->profile,
            'institution' => $target->academicInstitution,
            'batches' => $batches,
            'subjectsTeaching' => collect(),
            'activeMenteeCount' => null,
            'activeMentorCount' => null,
            'mentorshipStatus' => null,
        ];

        if ($target->role === 'faculty') {
            $subjectIds = DB::table('academic_subject_faculty')
                ->where('user_id', $target->id)
                ->pluck('subject_id');
            $data['subjectsTeaching'] = Subject::query()
                ->whereIn('id', $subjectIds)
                ->with('batch.institution')
                ->orderBy('name')
                ->limit(8)
                ->get();
            $data['activeMenteeCount'] = $this->activeMenteeCountFor($target);
        }

        if (in_array($target->role, self::menteeRoleSlugs(), true)) {
            $data['activeMentorCount'] = $this->activeMentorCountFor($target);
        }

        $viewer = auth()->user();
        if ($viewer && in_array($viewer->role, self::menteeRoleSlugs(), true) && $target->role === 'faculty') {
            $data['mentorshipStatus'] = Mentorship::query()
                ->where('mentee_id', $viewer->id)
                ->where('mentor_id', $target->id)
                ->value('status');
        } elseif ($viewer && $viewer->role === 'faculty' && in_array($target->role, self::menteeRoleSlugs(), true)) {
            $data['mentorshipStatus'] = Mentorship::query()
                ->where('mentor_id', $viewer->id)
                ->where('mentee_id', $target->id)
                ->value('status');
        }

        return $data;
    }
}

<?php

namespace App\Modules\Academics\Services;

use App\Models\Core\User;
use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Mentorship;
use App\Modules\Academics\Models\Submission;
use App\Modules\Academics\Models\SubmissionMentorReview;
use App\Modules\Academics\Models\SubmissionMentorShare;

/**
 * Mentor share/review verification for submissions and SPI credit.
 */
class MentorVerificationService
{
    public function shareCount(Submission $submission): int
    {
        return SubmissionMentorShare::query()->where('submission_id', $submission->id)->count();
    }

    public function reviewedMentorIds(Submission $submission): array
    {
        return SubmissionMentorReview::query()
            ->where('submission_id', $submission->id)
            ->pluck('mentor_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function sharedMentorIds(Submission $submission): array
    {
        return SubmissionMentorShare::query()
            ->where('submission_id', $submission->id)
            ->pluck('mentor_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** No mentor shares, or every shared mentor has submitted a review. */
    public function isFullyVerified(Submission $submission): bool
    {
        $shared = $this->sharedMentorIds($submission);
        if ($shared === []) {
            return true;
        }

        $reviewed = $this->reviewedMentorIds($submission);

        return count(array_diff($shared, $reviewed)) === 0;
    }

    /** Stamp mentor_verified_at when complete; clear when pending. */
    public function syncVerificationTimestamp(Submission $submission): Submission
    {
        $verified = $this->isFullyVerified($submission);
        $submission->update([
            'mentor_verified_at' => $verified ? ($submission->mentor_verified_at ?? now()) : null,
        ]);

        return $submission->fresh();
    }

    /**
     * @return 'none'|'verified'|'pending'|'not_submitted'
     */
    public function assignmentMentorStatus(User $student, Assignment $assignment, ?Submission $submission = null): string
    {
        if (! AcademicScoreService::assignmentIsInstituteSubmitted($student, $assignment)) {
            return 'not_submitted';
        }

        $submission ??= Submission::query()
            ->where('user_id', $student->id)
            ->where('assignment_id', $assignment->id)
            ->first();

        if (! $submission) {
            return 'none';
        }

        if ($this->shareCount($submission) === 0) {
            return 'none';
        }

        return $this->isFullyVerified($submission) ? 'verified' : 'pending';
    }
}

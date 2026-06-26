<?php

namespace App\Modules\Academics\Support;

use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Submission;
use App\Modules\Academics\Services\MentorVerificationService;

class StudentAssignmentStatus
{
    /**
     * @return array{key: string, label: string, pill: string, detail: ?string}
     */
    public static function for(Assignment $assignment, ?Submission $submission, ?MentorVerificationService $mentorVerification = null): array
    {
        $mentorVerification ??= app(MentorVerificationService::class);

        if (! $submission) {
            if ($assignment->isPastDue()) {
                return [
                    'key' => 'overdue',
                    'label' => 'Overdue',
                    'pill' => 'warn',
                    'detail' => 'Due date has passed. Submit as soon as you can.',
                ];
            }

            return [
                'key' => 'not_started',
                'label' => 'Not started',
                'pill' => 'pending',
                'detail' => null,
            ];
        }

        if ($mentorVerification->shareCount($submission) > 0 && ! $mentorVerification->isFullyVerified($submission)) {
            $shared = $mentorVerification->shareCount($submission);
            $reviewed = count($mentorVerification->reviewedMentorIds($submission));

            return [
                'key' => 'mentor_pending',
                'label' => 'Awaiting mentor',
                'pill' => 'pending-status',
                'detail' => $reviewed.' of '.$shared.' mentor rating(s) received.',
            ];
        }

        if ($submission->isLate()) {
            return [
                'key' => 'submitted_late',
                'label' => 'Submitted · Late',
                'pill' => 'warn',
                'detail' => 'Submitted on '.$submission->submitted_at->format('M j, Y g:i A').'.',
            ];
        }

        return [
            'key' => 'submitted',
            'label' => 'Submitted',
            'pill' => 'ok',
            'detail' => 'Submitted on '.$submission->submitted_at->format('M j, Y g:i A').'.',
        ];
    }
}

<?php

namespace App\Modules\Academics\Services;

use App\Modules\Academics\Models\AcademicExamAttempt;
use App\Modules\Academics\Models\Assignment;

class TopicCompletionService
{
    /**
     * After a submission is saved or a linked quiz is submitted: if completion % >= threshold, mark the topic completed.
     */
    public static function checkAndCompleteTopic(Assignment $assignment): void
    {
        $assignment->load(['topic', 'exams']);
        $topic = $assignment->topic;
        if (! $topic) {
            return;
        }

        $eligibleIds = $assignment->eligibleStudentIds();
        $eligibleCount = count($eligibleIds);
        if ($eligibleCount === 0) {
            return;
        }

        if ($assignment->assignment_type === Assignment::TYPE_QUIZ && $assignment->exams->isNotEmpty()) {
            $examIds = $assignment->exams->pluck('id');
            $submittedCount = 0;
            foreach ($eligibleIds as $uid) {
                if ($assignment->submissions()->where('user_id', $uid)->exists()) {
                    $submittedCount++;

                    continue;
                }
                if (AcademicExamAttempt::whereIn('exam_id', $examIds)
                    ->where('user_id', $uid)
                    ->where('status', AcademicExamAttempt::STATUS_SUBMITTED)
                    ->exists()) {
                    $submittedCount++;
                }
            }
        } else {
            $submittedCount = $assignment->submissions()->count();
        }

        $percentage = (int) round(($submittedCount / $eligibleCount) * 100);
        $threshold = (int) config('academics.completion_threshold', 70);

        if ($percentage >= $threshold) {
            $topic->update(['is_completed' => true]);
        }
    }
}

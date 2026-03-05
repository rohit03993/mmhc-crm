<?php

namespace App\Modules\Academics\Services;

use App\Modules\Academics\Models\Assignment;

class TopicCompletionService
{
    /**
     * After a submission is saved: if submission % >= threshold, mark the assignment's topic as completed.
     */
    public static function checkAndCompleteTopic(Assignment $assignment): void
    {
        $assignment->load('topic');
        $topic = $assignment->topic;
        if (!$topic) {
            return;
        }

        $eligibleIds = $assignment->eligibleStudentIds();
        $eligibleCount = count($eligibleIds);
        if ($eligibleCount === 0) {
            return;
        }

        $submittedCount = $assignment->submissions()->count();
        $percentage = (int) round(($submittedCount / $eligibleCount) * 100);
        $threshold = (int) config('academics.completion_threshold', 70);

        if ($percentage >= $threshold) {
            $topic->update(['is_completed' => true]);
        }
    }
}

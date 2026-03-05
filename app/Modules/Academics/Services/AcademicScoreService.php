<?php

namespace App\Modules\Academics\Services;

use App\Models\Core\User;
use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Models\Submission;
use App\Modules\Academics\Models\Topic;

/**
 * Computes SPI (Student), FPI (Faculty), ICR (Institution) for the academics module.
 */
class AcademicScoreService
{
    /**
     * SPI = Student Professional Index.
     * Percentage of eligible assignments the student has submitted (0–100).
     */
    public static function getSpi(User $user): int
    {
        if ($user->role !== 'student') {
            return 0;
        }
        $eligibleAssignmentIds = Assignment::whereHas('topic.subject.batch.students', fn ($q) => $q->where('users.id', $user->id))
            ->pluck('id')
            ->toArray();
        if (empty($eligibleAssignmentIds)) {
            return 0;
        }
        $submittedCount = Submission::where('user_id', $user->id)
            ->whereIn('assignment_id', $eligibleAssignmentIds)
            ->count();
        return (int) round(($submittedCount / count($eligibleAssignmentIds)) * 100);
    }

    /**
     * FPI = Faculty Performance Index.
     * Percentage of topics completed in subjects the faculty is assigned to (0–100).
     */
    public static function getFpi(User $user): int
    {
        if ($user->role !== 'faculty') {
            return 0;
        }
        $topicQuery = Topic::whereHas('subject.faculty', fn ($q) => $q->where('user_id', $user->id));
        $total = $topicQuery->count();
        if ($total === 0) {
            return 0;
        }
        $completed = (clone $topicQuery)->where('is_completed', true)->count();
        return (int) round(($completed / $total) * 100);
    }

    /**
     * ICR = Institution Clinical Readiness.
     * Percentage of topics completed across all subjects in the institution (0–100).
     */
    public static function getIcr(Institution $institution): int
    {
        $topicQuery = Topic::whereHas('subject.batch', fn ($q) => $q->where('institution_id', $institution->id));
        $total = $topicQuery->count();
        if ($total === 0) {
            return 0;
        }
        $completed = (clone $topicQuery)->where('is_completed', true)->count();
        return (int) round(($completed / $total) * 100);
    }
}

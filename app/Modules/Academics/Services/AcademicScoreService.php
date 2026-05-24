<?php

namespace App\Modules\Academics\Services;

use App\Models\Core\User;
use App\Modules\Academics\Models\AcademicExamAttempt;
use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Models\Mentorship;
use App\Modules\Academics\Models\Submission;
use App\Modules\Academics\Models\SubmissionMentorReview;
use App\Modules\Academics\Models\Topic;

/**
 * Computes SPI (Student), FPI (Faculty), MEI (Mentor Engagement), ICR (Institution).
 */
class AcademicScoreService
{
    /**
     * SPI = Student Professional Index.
     * Percentage of eligible assignments fully credited (institute submitted + all shared mentors rated).
     */
    public static function getSpi(User $user): int
    {
        return self::getSpiBreakdown($user)['percent'];
    }

    /**
     * @return array{total: int, verified: int, submitted_pending_mentor: int, not_submitted: int, percent: int}
     */
    public static function getSpiBreakdown(User $user): array
    {
        if ($user->role !== 'student') {
            return ['total' => 0, 'verified' => 0, 'submitted_pending_mentor' => 0, 'not_submitted' => 0, 'percent' => 0];
        }

        $assignments = Assignment::with('exams')
            ->whereHas('topic.subject.batch.students', fn ($q) => $q->where('users.id', $user->id))
            ->get();

        $total = $assignments->count();
        if ($total === 0) {
            return ['total' => 0, 'verified' => 0, 'submitted_pending_mentor' => 0, 'not_submitted' => 0, 'percent' => 0];
        }

        $verification = app(MentorVerificationService::class);
        $verified = 0;
        $submittedPendingMentor = 0;
        $notSubmitted = 0;

        foreach ($assignments as $assignment) {
            if (! self::assignmentIsInstituteSubmitted($user, $assignment)) {
                $notSubmitted++;

                continue;
            }

            $submission = Submission::query()
                ->where('user_id', $user->id)
                ->where('assignment_id', $assignment->id)
                ->first();

            if ($submission && $verification->shareCount($submission) > 0 && ! $verification->isFullyVerified($submission)) {
                $submittedPendingMentor++;

                continue;
            }

            $verified++;
        }

        return [
            'total' => $total,
            'verified' => $verified,
            'submitted_pending_mentor' => $submittedPendingMentor,
            'not_submitted' => $notSubmitted,
            'percent' => (int) round(($verified / $total) * 100),
        ];
    }

    /** Institute path: file submission exists or linked quiz attempt submitted. */
    public static function assignmentIsInstituteSubmitted(User $user, Assignment $assignment): bool
    {
        if (Submission::query()->where('user_id', $user->id)->where('assignment_id', $assignment->id)->exists()) {
            return true;
        }

        if ($assignment->assignment_type === Assignment::TYPE_QUIZ && $assignment->exams->isNotEmpty()) {
            $examIds = $assignment->exams->pluck('id');

            return AcademicExamAttempt::query()
                ->whereIn('exam_id', $examIds)
                ->where('user_id', $user->id)
                ->where('status', AcademicExamAttempt::STATUS_SUBMITTED)
                ->exists();
        }

        return false;
    }

    /** Count assignments that count toward SPI for a student (optionally filtered assignment IDs). */
    public static function countVerifiedAssignments(User $student, array $assignmentIds): array
    {
        $assignments = Assignment::with('exams')->whereIn('id', $assignmentIds)->get();
        $verification = app(MentorVerificationService::class);
        $verified = 0;
        $submittedPendingMentor = 0;
        $instituteSubmitted = 0;

        foreach ($assignments as $assignment) {
            if (! self::assignmentIsInstituteSubmitted($student, $assignment)) {
                continue;
            }
            $instituteSubmitted++;

            $submission = Submission::query()
                ->where('user_id', $student->id)
                ->where('assignment_id', $assignment->id)
                ->first();

            if ($submission && $verification->shareCount($submission) > 0 && ! $verification->isFullyVerified($submission)) {
                $submittedPendingMentor++;

                continue;
            }

            $verified++;
        }

        $total = $assignments->count();

        return [
            'total' => $total,
            'institute_submitted' => $instituteSubmitted,
            'verified' => $verified,
            'submitted_pending_mentor' => $submittedPendingMentor,
            'percent' => $total > 0 ? (int) round(($verified / $total) * 100) : 0,
        ];
    }

    /**
     * FPI = Faculty Performance Index (profile score).
     * Combines institute teaching (topic completion) with mentorship (mentees + assignment ratings).
     */
    public static function getFpi(User $user): int
    {
        return self::getFpiBreakdown($user)['percent'];
    }

    /**
     * Topic completion only — institute teaching track (0–100).
     */
    public static function getFpiTeachingScore(User $user): int
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
     * @return array{
     *     percent: int,
     *     teaching_percent: int,
     *     mentorship_percent: int,
     *     active_mentees: int,
     *     reviews_given: int,
     *     avg_rating: float,
     *     pending_reviews: int,
     *     mentee_score: int,
     *     review_score: int,
     *     rating_score: int
     * }
     */
    public static function getFpiBreakdown(User $user): array
    {
        if ($user->role !== 'faculty') {
            return [
                'percent' => 0,
                'teaching_percent' => 0,
                'mentorship_percent' => 0,
                'active_mentees' => 0,
                'reviews_given' => 0,
                'avg_rating' => 0.0,
                'pending_reviews' => 0,
                'mentee_score' => 0,
                'review_score' => 0,
                'rating_score' => 0,
            ];
        }

        $teaching = self::getFpiTeachingScore($user);
        $mei = self::getMeiBreakdown($user);
        $mentorship = $mei['percent'];

        $hasMentorshipActivity = $mei['active_mentees'] > 0 || $mei['reviews_given'] > 0;

        if ($hasMentorshipActivity && $teaching > 0) {
            $combined = (int) round($teaching * 0.4 + $mentorship * 0.6);
        } elseif ($hasMentorshipActivity) {
            $combined = $mentorship;
        } else {
            $combined = $teaching;
        }

        return array_merge($mei, [
            'percent' => min(100, $combined),
            'teaching_percent' => $teaching,
            'mentorship_percent' => $mentorship,
        ]);
    }

    /**
     * MEI = Mentor Engagement Index (0–100).
     * Grows with each student who chooses this faculty as mentor and each assignment rated.
     */
    public static function getMei(User $user): int
    {
        return self::getMeiBreakdown($user)['percent'];
    }

    /**
     * @return array{
     *     active_mentees: int,
     *     reviews_given: int,
     *     avg_rating: float,
     *     pending_reviews: int,
     *     percent: int,
     *     mentee_score: int,
     *     review_score: int,
     *     rating_score: int
     * }
     */
    public static function getMeiBreakdown(User $user): array
    {
        if ($user->role !== 'faculty') {
            return [
                'active_mentees' => 0,
                'reviews_given' => 0,
                'avg_rating' => 0.0,
                'pending_reviews' => 0,
                'percent' => 0,
                'mentee_score' => 0,
                'review_score' => 0,
                'rating_score' => 0,
            ];
        }

        $activeMentees = Mentorship::query()
            ->where('mentor_id', $user->id)
            ->where('status', Mentorship::STATUS_ACTIVE)
            ->count();

        $reviewsGiven = SubmissionMentorReview::query()->where('mentor_id', $user->id)->count();
        $avgRating = round((float) (SubmissionMentorReview::query()->where('mentor_id', $user->id)->avg('rating') ?? 0), 1);

        $reviewedIds = SubmissionMentorReview::query()->where('mentor_id', $user->id)->pluck('submission_id');
        $pendingReviews = \App\Modules\Academics\Models\SubmissionMentorShare::query()
            ->where('mentor_id', $user->id)
            ->whereNotIn('submission_id', $reviewedIds)
            ->count();

        // Each active mentee +10 (cap 50); each rating given +8 (cap 40); quality up to +10
        $menteeScore = min(50, $activeMentees * 10);
        $reviewScore = min(40, $reviewsGiven * 8);
        $ratingScore = $reviewsGiven > 0 ? (int) round(($avgRating / 5) * 10) : 0;
        $percent = min(100, $menteeScore + $reviewScore + $ratingScore);

        return [
            'active_mentees' => $activeMentees,
            'reviews_given' => $reviewsGiven,
            'avg_rating' => $avgRating,
            'pending_reviews' => $pendingReviews,
            'mentee_score' => $menteeScore,
            'review_score' => $reviewScore,
            'rating_score' => $ratingScore,
            'percent' => $percent,
        ];
    }

    /**
     * ICR = Institution Clinical Readiness.
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

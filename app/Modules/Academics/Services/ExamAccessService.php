<?php

namespace App\Modules\Academics\Services;

use App\Models\Core\User;
use App\Modules\Academics\Models\AcademicExam;
use App\Modules\Academics\Models\Subject;
use Illuminate\Support\Facades\DB;

/**
 * Who may take or manage exams — see docs/ACADEMICS-QUIZ-EXAM-VISIBILITY-DESIGN.md
 */
class ExamAccessService
{
    public function canTake(User $user, AcademicExam $exam): bool
    {
        if (! $user->is_active || $user->role !== 'student') {
            return false;
        }

        if (! $exam->is_published || ! $exam->isWithinSchedule()) {
            return false;
        }

        return match ($exam->audience_type) {
            AcademicExam::AUDIENCE_SUBJECT_COHORT => $this->studentInSubjectCohort($user, $exam),
            AcademicExam::AUDIENCE_BATCH => $this->studentInBatch($user, (int) $exam->batch_id),
            AcademicExam::AUDIENCE_INSTITUTION_OPEN => $this->studentInInstitution($user, (int) $exam->institution_id),
            AcademicExam::AUDIENCE_COMMUNITY => $this->studentForCommunityExam($user, $exam),
            default => false,
        };
    }

    public function canManage(User $user, AcademicExam $exam): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if (in_array($user->role, ['super_admin', 'admin'], true)) {
            return true;
        }

        if ($user->role === 'institution_admin' && (int) $user->academic_institution_id === (int) $exam->institution_id) {
            return true;
        }

        if ($user->role !== 'faculty') {
            return false;
        }

        if ((int) $user->academic_institution_id !== (int) $exam->institution_id) {
            return false;
        }

        return match ($exam->audience_type) {
            AcademicExam::AUDIENCE_SUBJECT_COHORT => $exam->subject_id && $this->facultyOnSubject($user, (int) $exam->subject_id),
            AcademicExam::AUDIENCE_BATCH => $exam->batch_id && $this->facultyOnBatch($user, (int) $exam->batch_id),
            AcademicExam::AUDIENCE_INSTITUTION_OPEN, AcademicExam::AUDIENCE_COMMUNITY => true,
            default => false,
        };
    }

    protected function studentForCommunityExam(User $user, AcademicExam $exam): bool
    {
        if ($exam->allows_cross_institution) {
            return $user->role === 'student';
        }

        return $this->studentInInstitution($user, (int) $exam->institution_id);
    }

    protected function studentInSubjectCohort(User $user, AcademicExam $exam): bool
    {
        if (! $exam->subject_id) {
            return false;
        }

        $subject = Subject::query()->find($exam->subject_id);
        if (! $subject) {
            return false;
        }

        return $this->studentInBatch($user, (int) $subject->batch_id);
    }

    protected function studentInBatch(User $user, ?int $batchId): bool
    {
        if (! $batchId) {
            return false;
        }

        return DB::table('academic_batch_users')
            ->where('batch_id', $batchId)
            ->where('user_id', $user->id)
            ->where('type', 'student')
            ->exists();
    }

    protected function studentInInstitution(User $user, int $institutionId): bool
    {
        return (int) ($user->academic_institution_id ?? 0) === $institutionId;
    }

    protected function facultyOnSubject(User $user, int $subjectId): bool
    {
        return DB::table('academic_subject_faculty')
            ->where('subject_id', $subjectId)
            ->where('user_id', $user->id)
            ->exists();
    }

    protected function facultyOnBatch(User $user, int $batchId): bool
    {
        return DB::table('academic_batch_users')
            ->where('batch_id', $batchId)
            ->where('user_id', $user->id)
            ->where('type', 'faculty')
            ->exists();
    }
}

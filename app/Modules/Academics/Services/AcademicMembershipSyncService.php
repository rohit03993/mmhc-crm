<?php

namespace App\Modules\Academics\Services;

use App\Models\Core\User;
use App\Modules\Academics\Models\Batch;

/**
 * Keeps users.academic_institution_id aligned with batch membership so exams,
 * reports, and filters behave consistently after admins assign students/faculty.
 */
class AcademicMembershipSyncService
{
    public function syncInstitutionForBatchMembers(Batch $batch): void
    {
        $institutionId = (int) $batch->institution_id;
        if ($institutionId < 1) {
            return;
        }

        $userIds = $batch->users()
            ->wherePivotIn('type', ['student', 'faculty'])
            ->pluck('users.id')
            ->unique()
            ->all();

        if ($userIds === []) {
            return;
        }

        User::query()
            ->whereIn('id', $userIds)
            ->whereIn('role', ['student', 'faculty'])
            ->update(['academic_institution_id' => $institutionId]);
    }

    /** When faculty are assigned to a subject, align their institution with the subject's college. */
    public function syncFacultyInstitutionFromSubject(int $institutionId, array $facultyUserIds): void
    {
        if ($institutionId < 1 || $facultyUserIds === []) {
            return;
        }

        User::query()
            ->whereIn('id', $facultyUserIds)
            ->where('role', 'faculty')
            ->update(['academic_institution_id' => $institutionId]);
    }
}

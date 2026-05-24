<?php

namespace App\Modules\Academics\Services;

use App\Models\Core\User;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\EnrollmentApplication;
use Illuminate\Support\Facades\DB;

class EnrollmentService
{
    public function createPendingApplication(User $user, int $institutionId, array $batchIds): EnrollmentApplication
    {
        $user->update(['academic_enrollment_status' => EnrollmentApplication::STATUS_PENDING]);

        return EnrollmentApplication::create([
            'user_id' => $user->id,
            'institution_id' => $institutionId,
            'status' => EnrollmentApplication::STATUS_PENDING,
            'requested_batch_ids' => array_values(array_unique(array_map('intval', $batchIds))),
        ]);
    }

    public function approve(EnrollmentApplication $application, User $reviewer, array $batchIds, ?string $notes = null): void
    {
        DB::transaction(function () use ($application, $reviewer, $batchIds, $notes) {
            $student = $application->user;
            $institutionId = (int) $application->institution_id;
            $validIds = Batch::query()
                ->where('institution_id', $institutionId)
                ->whereIn('id', array_map('intval', $batchIds))
                ->pluck('id')
                ->all();

            $sync = [];
            foreach ($validIds as $bid) {
                $sync[$bid] = ['type' => 'student'];
            }
            $student->academicBatches()->sync($sync);
            $student->update([
                'academic_enrollment_status' => EnrollmentApplication::STATUS_APPROVED,
                'academic_institution_id' => $institutionId,
            ]);

            $application->update([
                'status' => EnrollmentApplication::STATUS_APPROVED,
                'approved_batch_ids' => $validIds,
                'reviewer_id' => $reviewer->id,
                'reviewer_notes' => $notes,
                'reviewed_at' => now(),
            ]);
        });
    }

    public function reject(EnrollmentApplication $application, User $reviewer, ?string $notes = null): void
    {
        DB::transaction(function () use ($application, $reviewer, $notes) {
            $application->user->update(['academic_enrollment_status' => EnrollmentApplication::STATUS_REJECTED]);
            $application->user->academicBatches()->detach();

            $application->update([
                'status' => EnrollmentApplication::STATUS_REJECTED,
                'reviewer_id' => $reviewer->id,
                'reviewer_notes' => $notes,
                'reviewed_at' => now(),
            ]);
        });
    }

    public function syncStudentBatches(User $student, int $institutionId, array $batchIds): void
    {
        $validIds = Batch::query()
            ->where('institution_id', $institutionId)
            ->whereIn('id', array_map('intval', $batchIds))
            ->pluck('id');

        $sync = [];
        foreach ($validIds as $bid) {
            $sync[$bid] = ['type' => 'student'];
        }
        $student->academicBatches()->sync($sync);
    }

    public function pendingCountForInstitution(int $institutionId): int
    {
        return EnrollmentApplication::query()
            ->where('institution_id', $institutionId)
            ->where('status', EnrollmentApplication::STATUS_PENDING)
            ->count();
    }
}

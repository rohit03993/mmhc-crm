<?php

namespace Database\Seeders;

use App\Models\Core\User;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Institution;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Sets users.academic_institution_id from academic_batch_users + academic_batches
 * so institution “People” lists and CRM joins stay aligned with batch membership.
 *
 * Run after bulk/academic seeders or when fixing legacy demo data:
 *   php artisan db:seed --class=SyncAcademicUsersInstitutionFromBatchesSeeder
 */
class SyncAcademicUsersInstitutionFromBatchesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Institution::query()->orderBy('id')->cursor() as $institution) {
            $batchIds = Batch::query()->where('institution_id', $institution->id)->pluck('id');
            if ($batchIds->isEmpty()) {
                continue;
            }

            $userIds = DB::table('academic_batch_users')
                ->whereIn('batch_id', $batchIds)
                ->whereIn('type', ['student', 'faculty'])
                ->pluck('user_id')
                ->unique()
                ->values()
                ->all();

            if ($userIds !== []) {
                User::query()->whereIn('id', $userIds)->update([
                    'academic_institution_id' => $institution->id,
                ]);
            }
        }

        $this->command?->info('Synced academic_institution_id for users tied to batches (students & faculty).');
    }
}

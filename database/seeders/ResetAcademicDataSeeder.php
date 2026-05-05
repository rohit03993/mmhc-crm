<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes all Academics module rows (institutions through exam attempts) in FK-safe order.
 * Clears users.academic_institution_id so no user points at deleted colleges.
 *
 * Typical use after bulk demo pollution:
 *   php artisan db:seed --class=ResetAcademicDataSeeder
 *   php artisan db:seed --class=AcademicDemoSeeder
 *   php artisan db:seed --class=SyncAcademicUsersInstitutionFromBatchesSeeder
 *
 * Or: php artisan db:seed --class=FreshAcademicDemoSeeder (15 colleges + platform super)
 */
class ResetAcademicDataSeeder extends Seeder
{
    /** @var list<string> */
    protected array $tablesInDeleteOrder = [
        'academic_exam_attempt_answers',
        'academic_exam_attempts',
        'academic_exam_options',
        'academic_exam_questions',
        'academic_exams',
        'academic_submissions',
        'academic_topic_resources',
        'academic_assignments',
        'academic_topics',
        'academic_subject_faculty',
        'academic_subjects',
        'academic_attendance',
        'academic_batch_users',
        'academic_batches',
        'academic_institutions',
    ];

    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            foreach ($this->tablesInDeleteOrder as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }
                DB::table($table)->truncate();
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'academic_institution_id')) {
            DB::table('users')->whereNotNull('academic_institution_id')->update(['academic_institution_id' => null]);
        }

        $this->command?->info('Academic module data cleared (all academic_* tables truncated; user institution links cleared).');
    }
}

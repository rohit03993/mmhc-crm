<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Wipes academics data then seeds 15 demo colleges (5 faculty, 5 students, subjects/topics/assignments/exams each).
 * Platform-wide academics access is via CRM admin (read-only college overview); college ops use institute_admin.
 *
 *   php artisan db:seed --class=FreshAcademicDemoSeeder
 *
 * Single flagship college only (MMCN-BPL + medmiracle.com users): use Reset then AcademicDemoSeeder instead.
 */
class FreshAcademicDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ResetAcademicDataSeeder::class);
        $this->call(AcademicBulkDemoSeeder::class);
        $this->call(EnsureAcademicPlatformSuperSeeder::class);
        $this->call(SyncAcademicUsersInstitutionFromBatchesSeeder::class);
    }
}

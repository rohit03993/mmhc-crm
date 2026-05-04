<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Healthcare Plans first (for landing page and plans module)
        $this->call(HealthcarePlansSeeder::class);

        // Demo images for Achievements & Media carousel (landing page)
        $this->call(AchievementMediaSeeder::class);

        // Healthcare / CRM demo: single orchestrated seeder (service types, plans, users, services, incentives, payments)
        $this->call(HealthcareCrmDemoSeeder::class);

        // Academics demo users/data (institution admin, faculty, students)
        $this->call(AcademicDemoSeeder::class);

        // Large multi-college academics (~15 colleges). Optional — can take ~1–3 minutes:
        // php artisan db:seed --class=AcademicBulkDemoSeeder
        // $this->call(AcademicBulkDemoSeeder::class);

        // Align users.academic_institution_id with batch membership (fixes empty “People” on institution pages)
        $this->call(SyncAcademicUsersInstitutionFromBatchesSeeder::class);
    }
}

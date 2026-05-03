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

        // Seed Subscription Plans (for patient subscriptions)
        $this->call(SubscriptionPlansSeeder::class);

        // Seed Service Types (required for service requests)
        $this->call(ServiceTypesSeeder::class);

        // Then seed demo data (nurses, caregivers, patients, service requests)
        $this->call(DemoDataSeeder::class);

        // Academics demo users/data (institution admin, faculty, students)
        $this->call(AcademicDemoSeeder::class);

        // Large multi-college academics (~15 colleges, 10 faculty & 30 students each): run when needed — can take ~1 min.
        // $this->call(AcademicBulkDemoSeeder::class);

        $this->call(IncentiveRuleSetSeeder::class);
        $this->call(IncentiveNetworkDemoSeeder::class);
        $this->call(PaymentGatewayDemoSeeder::class);
    }
}

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
        
        // Seed Service Types (required for service requests)
        $this->call(ServiceTypesSeeder::class);
        
        // Clean existing service requests data (optional - removes invalid references)
        // $this->call(ResetServiceRequestsSeeder::class);
        
        // Then seed demo data (nurses, caregivers, patients, service requests)
        $this->call(DemoDataSeeder::class);
    }
}

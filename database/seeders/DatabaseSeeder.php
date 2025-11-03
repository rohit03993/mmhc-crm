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
        // Seed Service Types first (required for service requests)
        $this->call(ServiceTypesSeeder::class);
        
        // Then seed demo data (nurses, caregivers, patients, service requests)
        $this->call(DemoDataSeeder::class);
    }
}

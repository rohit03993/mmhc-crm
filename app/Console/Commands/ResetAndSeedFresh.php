<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ResetAndSeedFresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:fresh-bihar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset database and seed fresh Bihar data (3 nurses, 3 caregivers, 2 patients, 1 admin)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Resetting database and seeding fresh Bihar data...');
        $this->info('');

        // Confirm before proceeding
        if (!$this->confirm('This will delete ALL existing data. Are you sure?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        try {
            // Step 1: Delete all existing data first
            $this->info('🗑️  Step 1: Deleting all existing data...');
            Artisan::call('db:delete-all', ['--force' => true]);
            $this->info('✅ All existing data deleted');

            // Step 2: Run fresh migrations (to ensure clean state)
            $this->info('');
            $this->info('📦 Step 2: Running fresh migrations...');
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->info('✅ Migrations completed');

            // Step 3: Seed service types first (required)
            $this->info('');
            $this->info('📋 Step 3: Seeding service types...');
            Artisan::call('db:seed', ['--class' => 'ServiceTypesSeeder', '--force' => true]);
            $this->info('✅ Service types seeded');

            // Step 4: Seed fresh Bihar data
            $this->info('');
            $this->info('🌱 Step 4: Seeding fresh Bihar data...');
            Artisan::call('db:seed', ['--class' => 'FreshBiharDataSeeder', '--force' => true]);
            $this->info('✅ Fresh Bihar data seeded');

            $this->info('');
            $this->info('✅ Database reset and seeded successfully!');
            $this->info('');
            $this->displayCredentials();

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    protected function displayCredentials()
    {
        $this->info('=== LOGIN CREDENTIALS ===');
        $this->info('Admin: admin@mmhc.com / password123');
        $this->info('');
        $this->info('Nurses:');
        $this->info('  1. nurse1@mmhc.com / password123 (Patna - 800001)');
        $this->info('  2. nurse2@mmhc.com / password123 (Gaya - 823001)');
        $this->info('  3. nurse3@mmhc.com / password123 (Muzaffarpur - 842001)');
        $this->info('');
        $this->info('Caregivers:');
        $this->info('  1. caregiver1@mmhc.com / password123 (Patna - 801101)');
        $this->info('  2. caregiver2@mmhc.com / password123 (Gaya - 823002)');
        $this->info('  3. caregiver3@mmhc.com / password123 (Muzaffarpur - 842002)');
        $this->info('');
        $this->info('Patients:');
        $this->info('  1. patient1@mmhc.com / password123 (Patna - 800001)');
        $this->info('  2. patient2@mmhc.com / password123 (Gaya - 823001)');
        $this->info('');
    }
}

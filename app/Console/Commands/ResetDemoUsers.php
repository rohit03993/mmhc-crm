<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Core\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ResetDemoUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:reset-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old demo users and create simplified demo users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Resetting demo users...');
        
        // Delete old demo users (except admin)
        $this->info('Deleting old demo users...');
        
        // Delete old nurses, caregivers, and patients by email OR unique_id pattern
        $deleted = User::where('role', '!=', 'admin')
            ->where(function($query) {
                $query->where('email', 'like', '%@example.com')
                      ->orWhere('email', 'like', '%@demo.com')
                      ->orWhere('unique_id', 'like', 'N-UID-%')
                      ->orWhere('unique_id', 'like', 'C-UID-%')
                      ->orWhere('unique_id', 'like', 'P-UID-%');
            })
            ->delete();
        
        $this->info("Deleted {$deleted} old demo users");
        
        // Create simplified demo users
        $this->info('Creating simplified demo users...');
        
        $password = 'password123';
        
        // Create Nurse
        $nurse = User::firstOrCreate(
            ['email' => 'nurse@demo.com'],
            [
                'name' => 'Dr. Priya Sharma',
                'phone' => '9876543210',
                'password' => $password,
                'role' => 'nurse',
                'unique_id' => 'N-UID-000001',
                'qualification' => 'B.Sc Nursing',
                'experience' => '5-10',
                'address' => 'Sector 15, Noida, Uttar Pradesh 201301',
                'date_of_birth' => '1985-03-15',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        
        // Update password if user exists
        if (!$nurse->wasRecentlyCreated) {
            DB::table('users')->where('id', $nurse->id)->update(['password' => Hash::make($password)]);
        }
        
        // Create Caregiver
        $caregiver = User::firstOrCreate(
            ['email' => 'caregiver@demo.com'],
            [
                'name' => 'Ram Prasad Yadav',
                'phone' => '9876543211',
                'password' => $password,
                'role' => 'caregiver',
                'unique_id' => 'C-UID-000001',
                'qualification' => 'General Care',
                'experience' => '3-5',
                'address' => 'Village: Dumra, District: Patna, Bihar 801101',
                'date_of_birth' => '1985-12-03',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        
        // Update password if user exists
        if (!$caregiver->wasRecentlyCreated) {
            DB::table('users')->where('id', $caregiver->id)->update(['password' => Hash::make($password)]);
        }
        
        // Create Patient - check by email first, then by unique_id
        $patient = User::where('email', 'patient@demo.com')
            ->orWhere('unique_id', 'P-UID-000001')
            ->first();
        
        if ($patient) {
            // Update existing patient
            $patient->update([
                'name' => 'Shri Ram Kumar Singh',
                'email' => 'patient@demo.com',
                'phone' => '9876543220',
                'role' => 'patient',
                'unique_id' => 'P-UID-000001',
                'address' => 'House No. 45, Gandhi Nagar, Patna, Bihar 800001',
                'date_of_birth' => '1965-03-10',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            DB::table('users')->where('id', $patient->id)->update(['password' => Hash::make($password)]);
        } else {
            // Create new patient
            $patient = User::create([
                'name' => 'Shri Ram Kumar Singh',
                'email' => 'patient@demo.com',
                'phone' => '9876543220',
                'password' => $password,
                'role' => 'patient',
                'unique_id' => 'P-UID-000001',
                'address' => 'House No. 45, Gandhi Nagar, Patna, Bihar 800001',
                'date_of_birth' => '1965-03-10',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
        
        $this->info('');
        $this->info('✅ Demo users reset successfully!');
        $this->info('');
        $this->info('=== LOGIN CREDENTIALS ===');
        $this->info('Admin: mantu@themmhc.com / password123');
        $this->info('Nurse: nurse@demo.com / password123');
        $this->info('Caregiver: caregiver@demo.com / password123');
        $this->info('Patient: patient@demo.com / password123');
        $this->info('');
        
        return 0;
    }
}


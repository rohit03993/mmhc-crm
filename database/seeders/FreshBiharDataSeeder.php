<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Core\User;
use App\Modules\Profiles\Models\Profile;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class FreshBiharDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting fresh Bihar data seeding...');
        $this->command->info('');

        // ============================================
        // 1. CREATE ADMIN
        // ============================================
        $this->command->info('👤 Creating Admin...');
        $admin = User::updateOrCreate(
            ['email' => 'admin@mmhc.com'],
            [
                'name' => 'Mantu Kumar',
                'phone' => '9113311256',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'unique_id' => 'M-UID-000001',
                'address' => 'Udgam Incubation Centre, Rohit Nagar, Phase 1, Bhopal 462023',
                'pincode' => '462023',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $this->command->info("   ✅ Admin created: {$admin->name} ({$admin->email})");

        // ============================================
        // 2. CREATE 3 NURSES (Different Pincodes in Bihar)
        // ============================================
        $this->command->info('');
        $this->command->info('👩‍⚕️ Creating 3 Nurses in Bihar...');
        
        $nurses = [
            [
                'name' => 'Dr. Priya Sharma',
                'email' => 'nurse1@mmhc.com',
                'phone' => '9876543210',
                'unique_id' => 'N-UID-000001',
                'qualification' => 'B.Sc Nursing',
                'experience' => '5-10',
                'address' => 'House No. 45, Gandhi Nagar, Patna, Bihar',
                'pincode' => '800001', // Patna
                'date_of_birth' => '1985-03-15',
            ],
            [
                'name' => 'Dr. Anjali Kumari',
                'email' => 'nurse2@mmhc.com',
                'phone' => '9876543211',
                'unique_id' => 'N-UID-000002',
                'qualification' => 'M.Sc Nursing',
                'experience' => '10-15',
                'address' => 'Sector 12, Gaya, Bihar',
                'pincode' => '823001', // Gaya
                'date_of_birth' => '1980-07-20',
            ],
            [
                'name' => 'Dr. Sunita Devi',
                'email' => 'nurse3@mmhc.com',
                'phone' => '9876543212',
                'unique_id' => 'N-UID-000003',
                'qualification' => 'B.Sc Nursing',
                'experience' => '3-5',
                'address' => 'Ward No. 5, Muzaffarpur, Bihar',
                'pincode' => '842001', // Muzaffarpur
                'date_of_birth' => '1990-11-10',
            ],
        ];

        foreach ($nurses as $nurseData) {
            // Extract qualification and experience for User table
            $qualification = $nurseData['qualification'];
            $experience = $nurseData['experience'];
            unset($nurseData['qualification'], $nurseData['experience']);
            
            $nurse = User::updateOrCreate(
                ['email' => $nurseData['email']],
                array_merge($nurseData, [
                    'qualification' => $qualification,
                    'experience' => $experience,
                    'password' => Hash::make('password123'),
                    'role' => 'nurse',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ])
            );

            // Create profile for nurse
            Profile::updateOrCreate(
                ['user_id' => $nurse->id],
                [
                    'availability_status' => 'available',
                    'specialization' => $qualification,
                    'experience_years' => $this->parseExperience($experience),
                ]
            );

            $this->command->info("   ✅ Nurse created: {$nurse->name} (Pincode: {$nurse->pincode})");
        }

        // ============================================
        // 3. CREATE 3 CAREGIVERS (Different Pincodes in Bihar)
        // ============================================
        $this->command->info('');
        $this->command->info('👨‍⚕️ Creating 3 Caregivers in Bihar...');
        
        $caregivers = [
            [
                'name' => 'Ram Prasad Yadav',
                'email' => 'caregiver1@mmhc.com',
                'phone' => '9876543220',
                'unique_id' => 'C-UID-000001',
                'qualification' => 'General Care',
                'experience' => '3-5',
                'address' => 'Village: Dumra, District: Patna, Bihar',
                'pincode' => '801101', // Patna District
                'date_of_birth' => '1985-12-03',
            ],
            [
                'name' => 'Krishna Kumar Singh',
                'email' => 'caregiver2@mmhc.com',
                'phone' => '9876543221',
                'unique_id' => 'C-UID-000002',
                'qualification' => 'Elderly Care Specialist',
                'experience' => '5-10',
                'address' => 'Bodh Gaya Road, Gaya, Bihar',
                'pincode' => '823002', // Gaya
                'date_of_birth' => '1982-05-18',
            ],
            [
                'name' => 'Mohan Lal',
                'email' => 'caregiver3@mmhc.com',
                'phone' => '9876543222',
                'unique_id' => 'C-UID-000003',
                'qualification' => 'Home Care Assistant',
                'experience' => '2-3',
                'address' => 'Tilak Maidan Road, Muzaffarpur, Bihar',
                'pincode' => '842002', // Muzaffarpur
                'date_of_birth' => '1992-08-25',
            ],
        ];

        foreach ($caregivers as $caregiverData) {
            // Extract qualification and experience for User table
            $qualification = $caregiverData['qualification'];
            $experience = $caregiverData['experience'];
            unset($caregiverData['qualification'], $caregiverData['experience']);
            
            $caregiver = User::updateOrCreate(
                ['email' => $caregiverData['email']],
                array_merge($caregiverData, [
                    'qualification' => $qualification,
                    'experience' => $experience,
                    'password' => Hash::make('password123'),
                    'role' => 'caregiver',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ])
            );

            // Create profile for caregiver
            Profile::updateOrCreate(
                ['user_id' => $caregiver->id],
                [
                    'availability_status' => 'available',
                    'specialization' => $qualification,
                    'experience_years' => $this->parseExperience($experience),
                ]
            );

            $this->command->info("   ✅ Caregiver created: {$caregiver->name} (Pincode: {$caregiver->pincode})");
        }

        // ============================================
        // 4. CREATE 2 PATIENTS
        // ============================================
        $this->command->info('');
        $this->command->info('🏥 Creating 2 Patients...');
        
        $patients = [
            [
                'name' => 'Shri Ram Kumar Singh',
                'email' => 'patient1@mmhc.com',
                'phone' => '9876543300',
                'unique_id' => 'P-UID-000001',
                'address' => 'House No. 45, Gandhi Nagar, Patna, Bihar 800001',
                'pincode' => '800001',
                'date_of_birth' => '1965-03-10',
            ],
            [
                'name' => 'Smt. Geeta Devi',
                'email' => 'patient2@mmhc.com',
                'phone' => '9876543301',
                'unique_id' => 'P-UID-000002',
                'address' => 'Sector 8, Gaya, Bihar 823001',
                'pincode' => '823001',
                'date_of_birth' => '1970-09-15',
            ],
        ];

        foreach ($patients as $patientData) {
            $patient = User::updateOrCreate(
                ['email' => $patientData['email']],
                array_merge($patientData, [
                    'password' => Hash::make('password123'),
                    'role' => 'patient',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ])
            );

            // Create profile for patient
            Profile::updateOrCreate(
                ['user_id' => $patient->id],
                [
                    'availability_status' => 'available', // Required field, default value
                ]
            );

            $this->command->info("   ✅ Patient created: {$patient->name} (Pincode: {$patient->pincode})");
        }

        // ============================================
        // SUMMARY
        // ============================================
        $this->command->info('');
        $this->command->info('✅ Fresh Bihar data seeded successfully!');
        $this->command->info('');
        $this->command->info('=== LOGIN CREDENTIALS ===');
        $this->command->info('Admin: admin@mmhc.com / password123');
        $this->command->info('');
        $this->command->info('Nurses:');
        $this->command->info('  1. nurse1@mmhc.com / password123 (Patna - 800001)');
        $this->command->info('  2. nurse2@mmhc.com / password123 (Gaya - 823001)');
        $this->command->info('  3. nurse3@mmhc.com / password123 (Muzaffarpur - 842001)');
        $this->command->info('');
        $this->command->info('Caregivers:');
        $this->command->info('  1. caregiver1@mmhc.com / password123 (Patna - 801101)');
        $this->command->info('  2. caregiver2@mmhc.com / password123 (Gaya - 823002)');
        $this->command->info('  3. caregiver3@mmhc.com / password123 (Muzaffarpur - 842002)');
        $this->command->info('');
        $this->command->info('Patients:');
        $this->command->info('  1. patient1@mmhc.com / password123 (Patna - 800001)');
        $this->command->info('  2. patient2@mmhc.com / password123 (Gaya - 823001)');
        $this->command->info('');
    }

    /**
     * Parse experience string to integer years
     * Examples: "5-10" -> 7, "3-5" -> 4, "10-15" -> 12, "2-3" -> 2
     */
    protected function parseExperience(string $experience): int
    {
        if (preg_match('/(\d+)-(\d+)/', $experience, $matches)) {
            return (int) round(($matches[1] + $matches[2]) / 2);
        }
        if (preg_match('/(\d+)/', $experience, $matches)) {
            return (int) $matches[1];
        }
        return 0;
    }
}


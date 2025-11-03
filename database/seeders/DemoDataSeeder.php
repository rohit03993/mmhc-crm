<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Core\User;
use App\Modules\Services\Models\ServiceType;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Services\Models\DailyService;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User (if not exists) - Check by email OR unique_id to avoid conflicts
        $admin = User::where('email', 'mantu@themmhc.com')
            ->orWhere('unique_id', 'M-UID-000001')
            ->first();
            
        if (!$admin) {
            $admin = User::create([
                'name' => 'Mantu Kumar',
                'email' => 'mantu@themmhc.com',
                'phone' => '9113311256',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'unique_id' => 'M-UID-000001',
                'address' => 'Udgam Incubation Centre, Rohit Nagar, Phase 1, Bhopal 462023',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        // Create Demo Nurses
        $nurses = [
            [
                'name' => 'Dr. Priya Sharma',
                'email' => 'priya.sharma@example.com',
                'phone' => '9876543210',
                'qualification' => 'B.Sc Nursing',
                'experience' => '5-10',
                'address' => 'Sector 15, Noida, Uttar Pradesh 201301',
                'date_of_birth' => '1985-03-15',
            ],
            [
                'name' => 'Sister Meera Singh',
                'email' => 'meera.singh@example.com',
                'phone' => '9876543211',
                'qualification' => 'GNM',
                'experience' => '3-5',
                'address' => 'Patna Medical College Area, Patna, Bihar 800004',
                'date_of_birth' => '1990-07-22',
            ],
            [
                'name' => 'Nurse Rajesh Kumar',
                'email' => 'rajesh.kumar@example.com',
                'phone' => '9876543212',
                'qualification' => 'ANM',
                'experience' => '1-3',
                'address' => 'Civil Lines, Bhopal, Madhya Pradesh 462001',
                'date_of_birth' => '1992-11-08',
            ],
            [
                'name' => 'Sister Anjali Gupta',
                'email' => 'anjali.gupta@example.com',
                'phone' => '9876543213',
                'qualification' => 'M.Sc Nursing',
                'experience' => '10+',
                'address' => 'Gurgaon Sector 29, Haryana 122001',
                'date_of_birth' => '1980-05-12',
            ],
            [
                'name' => 'Nurse Sunita Devi',
                'email' => 'sunita.devi@example.com',
                'phone' => '9876543214',
                'qualification' => 'GNM',
                'experience' => '3-5',
                'address' => 'Ranchi Medical College, Ranchi, Jharkhand 834009',
                'date_of_birth' => '1988-09-18',
            ],
        ];

        // Store created nurses with their unique IDs for reference
        $createdNurses = [];
        foreach ($nurses as $index => $nurseData) {
            $uniqueId = 'N-UID-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT);
            
            // Check if user already exists by email or unique_id
            $nurse = User::where('email', $nurseData['email'])
                ->orWhere('unique_id', $uniqueId)
                ->first();
            
            if (!$nurse) {
                $nurse = User::create([
                    'name' => $nurseData['name'],
                    'email' => $nurseData['email'],
                    'phone' => $nurseData['phone'],
                    'password' => Hash::make('password123'),
                    'role' => 'nurse',
                    'unique_id' => $uniqueId,
                    'qualification' => $nurseData['qualification'],
                    'experience' => $nurseData['experience'],
                    'address' => $nurseData['address'],
                    'date_of_birth' => $nurseData['date_of_birth'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            }
            
            // Store nurse by unique_id for service request assignment
            $createdNurses[$uniqueId] = $nurse;
        }

        // Create Demo Caregivers
        $caregivers = [
            [
                'name' => 'Ram Prasad Yadav',
                'email' => 'ram.yadav@example.com',
                'phone' => '9876543215',
                'qualification' => 'General Care',
                'experience' => '1-3',
                'address' => 'Village: Dumra, District: Patna, Bihar 801101',
                'date_of_birth' => '1985-12-03',
            ],
            [
                'name' => 'Sita Devi',
                'email' => 'sita.devi@example.com',
                'phone' => '9876543216',
                'qualification' => 'Home Care',
                'experience' => '3-5',
                'address' => 'Bhopal Old City, Madhya Pradesh 462001',
                'date_of_birth' => '1982-04-25',
            ],
            [
                'name' => 'Mohan Lal',
                'email' => 'mohan.lal@example.com',
                'phone' => '9876543217',
                'qualification' => 'Elderly Care',
                'experience' => '5-10',
                'address' => 'Noida Sector 62, Uttar Pradesh 201301',
                'date_of_birth' => '1978-08-14',
            ],
            [
                'name' => 'Kavita Singh',
                'email' => 'kavita.singh@example.com',
                'phone' => '9876543218',
                'qualification' => 'Child Care',
                'experience' => '1-3',
                'address' => 'Gurgaon Sector 14, Haryana 122001',
                'date_of_birth' => '1990-06-30',
            ],
            [
                'name' => 'Babu Ram',
                'email' => 'babu.ram@example.com',
                'phone' => '9876543219',
                'qualification' => 'General Care',
                'experience' => '0-1',
                'address' => 'Ranchi Main Road, Jharkhand 834001',
                'date_of_birth' => '1995-01-20',
            ],
        ];

        // Store created caregivers with their unique IDs for reference
        $createdCaregivers = [];
        foreach ($caregivers as $index => $caregiverData) {
            $uniqueId = 'C-UID-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT);
            
            // Check if user already exists by email or unique_id
            $caregiver = User::where('email', $caregiverData['email'])
                ->orWhere('unique_id', $uniqueId)
                ->first();
            
            if (!$caregiver) {
                $caregiver = User::create([
                    'name' => $caregiverData['name'],
                    'email' => $caregiverData['email'],
                    'phone' => $caregiverData['phone'],
                    'password' => Hash::make('password123'),
                    'role' => 'caregiver',
                    'unique_id' => $uniqueId,
                    'qualification' => $caregiverData['qualification'],
                    'experience' => $caregiverData['experience'],
                    'address' => $caregiverData['address'],
                    'date_of_birth' => $caregiverData['date_of_birth'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            }
            
            // Store caregiver by unique_id for service request assignment
            $createdCaregivers[$uniqueId] = $caregiver;
        }

        // Create Demo Patients
        $patients = [
            [
                'name' => 'Shri Ram Kumar Singh',
                'email' => 'ram.singh@example.com',
                'phone' => '9876543220',
                'address' => 'House No. 45, Gandhi Nagar, Patna, Bihar 800001',
                'date_of_birth' => '1965-03-10',
            ],
            [
                'name' => 'Smt. Geeta Devi',
                'email' => 'geeta.devi@example.com',
                'phone' => '9876543221',
                'address' => 'Flat 302, Tower A, Noida Sector 15, Uttar Pradesh 201301',
                'date_of_birth' => '1970-07-15',
            ],
            [
                'name' => 'Shri Rajesh Kumar',
                'email' => 'rajesh.patient@example.com',
                'phone' => '9876543222',
                'address' => 'B-42, Arera Colony, Bhopal, Madhya Pradesh 462016',
                'date_of_birth' => '1958-11-22',
            ],
            [
                'name' => 'Smt. Sunita Sharma',
                'email' => 'sunita.sharma@example.com',
                'phone' => '9876543223',
                'address' => 'Sector 29, Gurgaon, Haryana 122001',
                'date_of_birth' => '1975-05-08',
            ],
            [
                'name' => 'Shri Mohan Prasad',
                'email' => 'mohan.prasad@example.com',
                'phone' => '9876543224',
                'address' => 'Main Road, Ranchi, Jharkhand 834001',
                'date_of_birth' => '1962-09-12',
            ],
            [
                'name' => 'Smt. Kamla Devi',
                'email' => 'kamla.devi@example.com',
                'phone' => '9876543225',
                'address' => 'Village: Rampur, District: Patna, Bihar 801101',
                'date_of_birth' => '1945-12-25',
            ],
        ];

        // Store created patients with their unique IDs for reference
        $createdPatients = [];
        foreach ($patients as $index => $patientData) {
            $uniqueId = 'P-UID-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT);
            
            // Check if user already exists by email or unique_id
            $patient = User::where('email', $patientData['email'])
                ->orWhere('unique_id', $uniqueId)
                ->first();
            
            if (!$patient) {
                $patient = User::create([
                    'name' => $patientData['name'],
                    'email' => $patientData['email'],
                    'phone' => $patientData['phone'],
                    'password' => Hash::make('password123'),
                    'role' => 'patient',
                    'unique_id' => $uniqueId,
                    'address' => $patientData['address'],
                    'date_of_birth' => $patientData['date_of_birth'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            }
            
            // Store patient by unique_id for service request assignment
            $createdPatients[$uniqueId] = $patient;
        }

        // Get service types
        $serviceTypes = ServiceType::all();

        // Create Demo Service Requests - Use unique_id references instead of hardcoded IDs
        $serviceRequests = [
            [
                'patient_unique_id' => 'P-UID-000001', // Ram Kumar Singh
                'service_type_id' => 1, // 24 Hours Care
                'preferred_staff_type' => 'nurse',
                'start_date' => Carbon::now()->addDays(1),
                'duration_days' => 7,
                'location' => 'House No. 45, Gandhi Nagar, Patna, Bihar 800001',
                'contact_person' => 'Shri Ram Kumar Singh',
                'contact_phone' => '9876543220',
                'notes' => 'Post-surgery care needed for knee replacement',
                'special_requirements' => 'Experienced nurse for post-operative care, physiotherapy support',
                'status' => 'assigned',
                'assigned_staff_unique_id' => 'N-UID-000002', // Sister Meera Singh
                'assigned_at' => Carbon::now()->subHours(2),
            ],
            [
                'patient_unique_id' => 'P-UID-000002', // Geeta Devi
                'service_type_id' => 2, // 12 Hours Care
                'preferred_staff_type' => 'caregiver',
                'start_date' => Carbon::now()->addDays(2),
                'duration_days' => 10,
                'location' => 'Flat 302, Tower A, Noida Sector 15, Uttar Pradesh 201301',
                'contact_person' => 'Smt. Geeta Devi',
                'contact_phone' => '9876543221',
                'notes' => 'Elderly care for 75-year-old patient',
                'special_requirements' => 'Patient has diabetes, needs medication monitoring',
                'status' => 'in_progress',
                'assigned_staff_unique_id' => 'C-UID-000002', // Sita Devi
                'assigned_at' => Carbon::now()->subDays(1),
                'started_at' => Carbon::now()->subHours(6),
            ],
            [
                'patient_unique_id' => 'P-UID-000003', // Rajesh Kumar
                'service_type_id' => 3, // 8 Hours Care
                'preferred_staff_type' => 'nurse',
                'start_date' => Carbon::now()->addDays(3),
                'duration_days' => 14,
                'location' => 'B-42, Arera Colony, Bhopal, Madhya Pradesh 462016',
                'contact_person' => 'Shri Rajesh Kumar',
                'contact_phone' => '9876543222',
                'notes' => 'Recovery care after heart surgery',
                'special_requirements' => 'Cardiac care specialist, monitoring equipment knowledge',
                'status' => 'pending',
            ],
            [
                'patient_unique_id' => 'P-UID-000004', // Sunita Sharma
                'service_type_id' => 4, // Single Visit
                'preferred_staff_type' => 'any',
                'start_date' => Carbon::now()->addDays(1),
                'duration_days' => 1,
                'location' => 'Sector 29, Gurgaon, Haryana 122001',
                'contact_person' => 'Smt. Sunita Sharma',
                'contact_phone' => '9876543223',
                'notes' => 'Health checkup and consultation',
                'special_requirements' => 'General health assessment',
                'status' => 'completed',
                'assigned_staff_unique_id' => 'N-UID-000004', // Sister Anjali Gupta
                'assigned_at' => Carbon::now()->subDays(2),
                'started_at' => Carbon::now()->subDays(1),
                'completed_at' => Carbon::now()->subHours(2),
            ],
            [
                'patient_unique_id' => 'P-UID-000005', // Mohan Prasad
                'service_type_id' => 1, // 24 Hours Care
                'preferred_staff_type' => 'nurse',
                'start_date' => Carbon::now()->addDays(5),
                'duration_days' => 21,
                'location' => 'Main Road, Ranchi, Jharkhand 834001',
                'contact_person' => 'Shri Mohan Prasad',
                'contact_phone' => '9876543224',
                'notes' => 'Critical care for stroke patient',
                'special_requirements' => 'ICU trained nurse, physiotherapy, speech therapy',
                'status' => 'assigned',
                'assigned_staff_unique_id' => 'N-UID-000005', // Nurse Sunita Devi
                'assigned_at' => Carbon::now()->subHours(1),
            ],
            [
                'patient_unique_id' => 'P-UID-000006', // Kamla Devi
                'service_type_id' => 2, // 12 Hours Care
                'preferred_staff_type' => 'caregiver',
                'start_date' => Carbon::now()->addDays(7),
                'duration_days' => 30,
                'location' => 'Village: Rampur, District: Patna, Bihar 801101',
                'contact_person' => 'Smt. Kamla Devi',
                'contact_phone' => '9876543225',
                'notes' => 'Long-term elderly care for 80-year-old',
                'special_requirements' => 'Patient has dementia, needs 24/7 supervision',
                'status' => 'pending',
            ],
        ];

        foreach ($serviceRequests as $requestData) {
            // Look up patient by unique_id
            $patient = $createdPatients[$requestData['patient_unique_id']] ?? null;
            if (!$patient) {
                $this->command->warn("Patient with unique_id {$requestData['patient_unique_id']} not found, skipping service request.");
                continue;
            }

            $serviceType = ServiceType::find($requestData['service_type_id']);
            if (!$serviceType) {
                $this->command->warn("Service type {$requestData['service_type_id']} not found, skipping service request.");
                continue;
            }

            $endDate = Carbon::parse($requestData['start_date'])->addDays($requestData['duration_days'] - 1);
            $totalAmount = $serviceType->patient_charge * $requestData['duration_days'];

            // Calculate staff payout if staff is assigned
            $totalStaffPayout = null;
            $prepaidAmount = 0.00;
            $paymentStatus = 'pending';
            $assignedStaffId = null;
            
            if (isset($requestData['assigned_staff_unique_id']) && $requestData['assigned_staff_unique_id']) {
                // Look up staff by unique_id from created nurses or caregivers
                $staff = $createdNurses[$requestData['assigned_staff_unique_id']] 
                    ?? $createdCaregivers[$requestData['assigned_staff_unique_id']] 
                    ?? null;
                
                if ($staff) {
                    $assignedStaffId = $staff->id;
                    $dailyStaffPayout = $staff->isNurse() ? $serviceType->nurse_payout : $serviceType->caregiver_payout;
                    $totalStaffPayout = $requestData['duration_days'] * $dailyStaffPayout;
                    
                    // Set prepayment (minimum 7 days worth, or full amount if less than 7 days)
                    $minPrepaidDays = min(7, $requestData['duration_days']);
                    $prepaidAmount = $serviceType->patient_charge * $minPrepaidDays;
                    
                    // Payment status based on prepayment
                    if ($prepaidAmount >= $totalAmount) {
                        $paymentStatus = 'paid';
                    } elseif ($prepaidAmount > 0) {
                        $paymentStatus = 'partially_paid';
                    }
                } else {
                    $this->command->warn("Staff with unique_id {$requestData['assigned_staff_unique_id']} not found for service request.");
                }
            }

            $serviceRequest = ServiceRequest::firstOrCreate(
                [
                    'patient_id' => $patient->id,
                    'service_type_id' => $requestData['service_type_id'],
                    'start_date' => $requestData['start_date'],
                ],
                [
                    'preferred_staff_type' => $requestData['preferred_staff_type'],
                    'end_date' => $endDate,
                    'duration_days' => $requestData['duration_days'],
                    'total_amount' => $totalAmount,
                    'total_staff_payout' => $totalStaffPayout,
                    'prepaid_amount' => $prepaidAmount,
                    'payment_status' => $paymentStatus,
                    'status' => $requestData['status'],
                    'notes' => $requestData['notes'],
                    'special_requirements' => $requestData['special_requirements'],
                    'location' => $requestData['location'],
                    'contact_person' => $requestData['contact_person'],
                    'contact_phone' => $requestData['contact_phone'],
                    'assigned_staff_id' => $assignedStaffId,
                    'assigned_at' => $requestData['assigned_at'] ?? null,
                    'started_at' => $requestData['started_at'] ?? null,
                    'completed_at' => $requestData['completed_at'] ?? null,
                ]
            );

            // Create daily service records for assigned requests
            if ($serviceRequest->assigned_staff_id && in_array($serviceRequest->status, ['assigned', 'in_progress', 'completed'])) {
                $this->createDailyServiceRecords($serviceRequest);
            }
        }

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- 1 Admin (Mantu Kumar)');
        $this->command->info('- 5 Nurses with Indian names and qualifications');
        $this->command->info('- 5 Caregivers with Indian names and experience');
        $this->command->info('- 6 Patients with Indian names and addresses');
        $this->command->info('- 6 Service requests with realistic scenarios');
        $this->command->info('');
        $this->command->info('Login credentials for all users: password123');
        $this->command->info('Admin: mantu@themmhc.com');
        $this->command->info('Nurses: priya.sharma@example.com, meera.singh@example.com, etc.');
        $this->command->info('Caregivers: ram.yadav@example.com, sita.devi@example.com, etc.');
        $this->command->info('Patients: ram.singh@example.com, geeta.devi@example.com, etc.');
    }

    /**
     * Create daily service records for a service request
     */
    protected function createDailyServiceRecords(ServiceRequest $serviceRequest)
    {
        $startDate = $serviceRequest->start_date;
        $endDate = $serviceRequest->end_date;
        $serviceType = $serviceRequest->serviceType;
        $staff = $serviceRequest->assignedStaff;

        // Determine payout based on staff type
        $staffPayout = $staff->isNurse() ? $serviceType->nurse_payout : $serviceType->caregiver_payout;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $status = 'scheduled';
            $startTime = $date->copy()->setTime(8, 0);
            $endTime = $date->copy()->setTime(20, 0);

            // Adjust timing based on service type
            if ($serviceType->duration_hours == 24) {
                $endTime = $date->copy()->addDay()->setTime(8, 0);
            } elseif ($serviceType->duration_hours == 12) {
                $endTime = $date->copy()->setTime(20, 0);
            } elseif ($serviceType->duration_hours == 8) {
                $endTime = $date->copy()->setTime(16, 0);
            } else { // Single visit
                $endTime = $date->copy()->setTime(9, 0);
            }

            // Set status based on date
            if ($date->isPast()) {
                $status = $serviceRequest->status === 'completed' ? 'completed' : 'in_progress';
            }

            DailyService::create([
                'service_request_id' => $serviceRequest->id,
                'staff_id' => $staff->id,
                'service_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'patient_charge' => $serviceType->patient_charge,
                'staff_payout' => $staffPayout,
                'platform_profit' => $serviceType->patient_charge - $staffPayout,
                'status' => $status,
            ]);
        }
    }
}

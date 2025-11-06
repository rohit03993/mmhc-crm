<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Core\User;
use App\Modules\Services\Models\ServiceType;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Services\Models\DailyService;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User (if not exists)
        $admin = User::where('email', 'mantu@themmhc.com')
            ->orWhere('unique_id', 'M-UID-000001')
            ->first();
            
        if (!$admin) {
            $admin = User::create([
                'name' => 'Mantu Kumar',
                'email' => 'mantu@themmhc.com',
                'phone' => '9113311256',
                'password' => 'password123', // Laravel's 'hashed' cast will automatically hash this
                'role' => 'admin',
                'unique_id' => 'M-UID-000001',
                'address' => 'Udgam Incubation Centre, Rohit Nagar, Phase 1, Bhopal 462023',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        } else {
            // Update password in case it was double-hashed before
            $admin->password = 'password123';
            $admin->save();
        }

        // Create ONE Demo Nurse
        $nurseUniqueId = 'N-UID-000001';
        $nurse = User::where('email', 'nurse@demo.com')
            ->orWhere('unique_id', $nurseUniqueId)
            ->first();
            
        if (!$nurse) {
            $nurse = User::create([
                'name' => 'Dr. Priya Sharma',
                'email' => 'nurse@demo.com',
                'phone' => '9876543210',
                'password' => 'password123', // Laravel's 'hashed' cast will automatically hash this
                'role' => 'nurse',
                'unique_id' => $nurseUniqueId,
                'qualification' => 'B.Sc Nursing',
                'experience' => '5-10',
                'address' => 'Sector 15, Noida, Uttar Pradesh 201301',
                'date_of_birth' => '1985-03-15',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        } else {
            // Update password in case it was double-hashed before
            $nurse->password = 'password123';
            $nurse->save();
        }

        // Create ONE Demo Caregiver
        $caregiverUniqueId = 'C-UID-000001';
        $caregiver = User::where('email', 'caregiver@demo.com')
            ->orWhere('unique_id', $caregiverUniqueId)
            ->first();
            
        if (!$caregiver) {
            $caregiver = User::create([
                'name' => 'Ram Prasad Yadav',
                'email' => 'caregiver@demo.com',
                'phone' => '9876543211',
                'password' => 'password123', // Laravel's 'hashed' cast will automatically hash this
                'role' => 'caregiver',
                'unique_id' => $caregiverUniqueId,
                'qualification' => 'General Care',
                'experience' => '3-5',
                'address' => 'Village: Dumra, District: Patna, Bihar 801101',
                'date_of_birth' => '1985-12-03',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        } else {
            // Update password in case it was double-hashed before
            $caregiver->password = 'password123';
            $caregiver->save();
        }

        // Create ONE Demo Patient
        $patientUniqueId = 'P-UID-000001';
        $patient = User::where('email', 'patient@demo.com')
            ->orWhere('unique_id', $patientUniqueId)
            ->first();
            
        if (!$patient) {
            $patient = User::create([
                'name' => 'Shri Ram Kumar Singh',
                'email' => 'patient@demo.com',
                'phone' => '9876543220',
                'password' => 'password123', // Laravel's 'hashed' cast will automatically hash this
                'role' => 'patient',
                'unique_id' => $patientUniqueId,
                'address' => 'House No. 45, Gandhi Nagar, Patna, Bihar 800001',
                'date_of_birth' => '1965-03-10',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        } else {
            // Update password in case it was double-hashed before
            $patient->password = 'password123';
            $patient->save();
        }

        // Get service types
        $serviceTypes = ServiceType::all();
        if ($serviceTypes->isEmpty()) {
            $this->command->warn('No service types found. Please run ServiceTypesSeeder first.');
            return;
        }

        $serviceType24Hr = $serviceTypes->firstWhere('name', '24 Hours Care') ?? $serviceTypes->first();
        $serviceType12Hr = $serviceTypes->firstWhere('name', '12 Hours Care') ?? $serviceTypes->skip(1)->first() ?? $serviceTypes->first();
        $serviceType8Hr = $serviceTypes->firstWhere('name', '8 Hours Care') ?? $serviceTypes->skip(2)->first() ?? $serviceTypes->first();

        // Create Demo Service Requests for Testing
        $demoRequests = [
            [
                'patient' => $patient,
                'service_type' => $serviceType24Hr,
                'preferred_staff_type' => 'nurse',
                'preferred_staff_id' => $nurse->id,
                'start_date' => Carbon::now()->addDays(1),
                'duration_days' => 7,
                'status' => 'pending',
                'notes' => 'Post-surgery care needed for knee replacement',
                'special_requirements' => 'Experienced nurse for post-operative care',
            ],
            [
                'patient' => $patient,
                'service_type' => $serviceType12Hr,
                'preferred_staff_type' => 'caregiver',
                'preferred_staff_id' => $caregiver->id,
                'start_date' => Carbon::now()->addDays(2),
                'duration_days' => 5,
                'status' => 'assigned',
                'assigned_staff' => $caregiver,
                'assigned_at' => Carbon::now()->subHours(2),
                'notes' => 'Elderly care for daily assistance',
                'special_requirements' => 'Patient needs medication monitoring',
            ],
            [
                'patient' => $patient,
                'service_type' => $serviceType8Hr,
                'preferred_staff_type' => 'nurse',
                'start_date' => Carbon::now()->addDays(3),
                'duration_days' => 3,
                'status' => 'in_progress',
                'assigned_staff' => $nurse,
                'assigned_at' => Carbon::now()->subDays(1),
                'started_at' => Carbon::now()->subHours(6),
                'notes' => 'Recovery care after minor surgery',
                'special_requirements' => 'General nursing care',
            ],
        ];

        foreach ($demoRequests as $requestData) {
            $serviceType = $requestData['service_type'];
            $dailyCharge = $serviceType->patient_charge;
            $totalAmount = $dailyCharge * $requestData['duration_days'];
            
            // Calculate staff payout
            $assignedStaff = $requestData['assigned_staff'] ?? null;
            $totalStaffPayout = null;
            if ($assignedStaff) {
                $dailyStaffPayout = $assignedStaff->isNurse() 
                    ? $serviceType->nurse_payout 
                    : $serviceType->caregiver_payout;
                $totalStaffPayout = $requestData['duration_days'] * $dailyStaffPayout;
            }

            $endDate = Carbon::parse($requestData['start_date'])->addDays($requestData['duration_days'] - 1);

            $serviceRequest = ServiceRequest::firstOrCreate(
                [
                    'patient_id' => $requestData['patient']->id,
                    'service_type_id' => $serviceType->id,
                    'start_date' => $requestData['start_date'],
                ],
                [
                    'preferred_staff_type' => $requestData['preferred_staff_type'],
                    'preferred_staff_id' => $requestData['preferred_staff_id'] ?? null,
                    'end_date' => $endDate,
                    'duration_days' => $requestData['duration_days'],
                    'total_amount' => $totalAmount,
                    'total_staff_payout' => $totalStaffPayout,
                    'prepaid_amount' => 0.00,
                    'payment_status' => 'pending',
                    'status' => $requestData['status'],
                    'notes' => $requestData['notes'],
                    'special_requirements' => $requestData['special_requirements'],
                    'location' => $requestData['patient']->address,
                    'contact_person' => $requestData['patient']->name,
                    'contact_phone' => $requestData['patient']->phone,
                    'assigned_staff_id' => $assignedStaff->id ?? null,
                    'assigned_at' => $requestData['assigned_at'] ?? null,
                    'started_at' => $requestData['started_at'] ?? null,
                    'completed_at' => $requestData['completed_at'] ?? null,
                ]
            );

            // Create daily service records for assigned/in_progress services
            if (in_array($serviceRequest->status, ['assigned', 'in_progress', 'completed']) && $serviceRequest->assigned_staff_id) {
                // Reload service request with relationships
                $serviceRequest->refresh();
                $serviceRequest->load(['serviceType', 'assignedStaff']);
                $serviceType = $serviceRequest->serviceType;
                $assignedStaff = $serviceRequest->assignedStaff;
                $durationHours = $serviceType->duration_hours;
                
                // Calculate daily charges and payouts
                $dailyPatientCharge = $serviceType->patient_charge;
                $dailyStaffPayout = $assignedStaff->isNurse() 
                    ? $serviceType->nurse_payout 
                    : $serviceType->caregiver_payout;
                $dailyPlatformProfit = $dailyPatientCharge - $dailyStaffPayout;
                
                for ($day = 0; $day < $serviceRequest->duration_days; $day++) {
                    $serviceDate = Carbon::parse($serviceRequest->start_date)->addDays($day);
                    
                    // Calculate start and end times based on service type duration
                    $startTime = Carbon::parse($serviceDate)->startOfDay();
                    $endTime = Carbon::parse($startTime)->addHours($durationHours);
                    
                    // Determine status based on date and service request status
                    $dailyStatus = 'scheduled'; // Default status
                    if ($serviceDate->isPast() && $serviceRequest->status === 'in_progress') {
                        $dailyStatus = 'in_progress';
                    } elseif ($serviceDate->isPast() && $serviceRequest->status === 'completed') {
                        $dailyStatus = 'completed';
                    } elseif ($serviceRequest->status === 'assigned' && $serviceDate->isToday()) {
                        $dailyStatus = 'in_progress';
                    }

                    DailyService::firstOrCreate(
                        [
                            'service_request_id' => $serviceRequest->id,
                            'service_date' => $serviceDate->format('Y-m-d'),
                        ],
                        [
                            'staff_id' => $serviceRequest->assigned_staff_id,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'patient_charge' => $dailyPatientCharge,
                            'staff_payout' => $dailyStaffPayout,
                            'platform_profit' => $dailyPlatformProfit,
                            'status' => $dailyStatus,
                            'completed_at' => $dailyStatus === 'completed' ? now() : null,
                        ]
                    );
                }
            }
        }

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('');
        $this->command->info('=== LOGIN CREDENTIALS ===');
        $this->command->info('Admin: mantu@themmhc.com / password123');
        $this->command->info('Nurse: nurse@demo.com / password123');
        $this->command->info('Caregiver: caregiver@demo.com / password123');
        $this->command->info('Patient: patient@demo.com / password123');
        $this->command->info('');
    }
}

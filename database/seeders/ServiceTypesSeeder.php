<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Services\Models\ServiceType;

class ServiceTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceTypes = [
            [
                'name' => '24 Hours Care',
                'description' => 'Round-the-clock nursing care for critical patients',
                'duration_hours' => 24,
                'patient_charge' => 2000.00,
                'nurse_payout' => 2000.00,
                'caregiver_payout' => 1500.00,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => '12 Hours Care',
                'description' => 'Day or night shift nursing care',
                'duration_hours' => 12,
                'patient_charge' => 1200.00,
                'nurse_payout' => 1200.00,
                'caregiver_payout' => 900.00,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => '8 Hours Care',
                'description' => 'Standard working hours nursing care',
                'duration_hours' => 8,
                'patient_charge' => 800.00,
                'nurse_payout' => 800.00,
                'caregiver_payout' => 700.00,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Single Visit',
                'description' => 'One-hour consultation or check-up',
                'duration_hours' => 1,
                'patient_charge' => 500.00,
                'nurse_payout' => 500.00,
                'caregiver_payout' => 250.00,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($serviceTypes as $serviceTypeData) {
            ServiceType::create($serviceTypeData);
        }
    }
}

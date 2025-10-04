<?php

namespace App\Modules\Plans\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Plans\Models\Plan;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic Plan',
                'description' => 'Essential healthcare services for basic needs',
                'price' => 500.00,
                'currency' => 'INR',
                'duration_days' => 30,
                'features' => [
                    'Basic health checkup',
                    'General consultation',
                    'Email support',
                    'Basic reports access'
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Standard Plan',
                'description' => 'Comprehensive healthcare with advanced features',
                'price' => 1000.00,
                'currency' => 'INR',
                'duration_days' => 30,
                'features' => [
                    'Complete health checkup',
                    'Specialist consultation',
                    'Priority support',
                    'Advanced reports',
                    'Telemedicine access',
                    'Medication reminders'
                ],
                'is_active' => true,
                'is_popular' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Premium Plan',
                'description' => 'Premium healthcare with all features included',
                'price' => 2000.00,
                'currency' => 'INR',
                'duration_days' => 30,
                'features' => [
                    'Comprehensive health checkup',
                    'Unlimited specialist consultations',
                    '24/7 premium support',
                    'Advanced analytics',
                    'Full telemedicine suite',
                    'Smart medication management',
                    'Health coaching sessions',
                    'Emergency support'
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Family Plan',
                'description' => 'Complete healthcare solution for the whole family',
                'price' => 3500.00,
                'currency' => 'INR',
                'duration_days' => 30,
                'features' => [
                    'Family health checkups (up to 4 members)',
                    'Unlimited consultations for all members',
                    'Priority family support',
                    'Family health dashboard',
                    'Child health monitoring',
                    'Senior care services',
                    'Emergency family support',
                    'Health records for all family members'
                ],
                'is_active' => true,
                'is_popular' => true,
                'sort_order' => 4,
            ]
        ];

        foreach ($plans as $planData) {
            Plan::create($planData);
        }
    }
}

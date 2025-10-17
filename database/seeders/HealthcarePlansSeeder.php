<?php

namespace Database\Seeders;

use App\Models\HealthcarePlan;
use Illuminate\Database\Seeder;

class HealthcarePlansSeeder extends Seeder
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
                'icon_class' => 'fa-heartbeat',
                'color_theme' => 'blue',
                'is_popular' => false,
                'popular_label' => null,
                'is_active' => true,
                'sort_order' => 1,
                'button_text' => 'Get Started',
                'button_link' => null,
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
                'icon_class' => 'fa-stethoscope',
                'color_theme' => 'green',
                'is_popular' => true,
                'popular_label' => 'Most Popular',
                'is_active' => true,
                'sort_order' => 2,
                'button_text' => 'Get Started',
                'button_link' => null,
            ],
            [
                'name' => 'Premium Plan',
                'description' => 'Premium healthcare with all features included',
                'price' => 2000.00,
                'currency' => 'INR',
                'duration_days' => 30,
                'features' => [
                    'Comprehensive checkup',
                    'Unlimited consultations',
                    '24/7 premium support',
                    'Advanced analytics',
                    'Full telemedicine suite',
                    'Health coaching sessions'
                ],
                'icon_class' => 'fa-crown',
                'color_theme' => 'purple',
                'is_popular' => false,
                'popular_label' => null,
                'is_active' => true,
                'sort_order' => 3,
                'button_text' => 'Get Started',
                'button_link' => null,
            ],
            [
                'name' => 'Family Plan',
                'description' => 'Complete healthcare solution for the whole family',
                'price' => 3500.00,
                'currency' => 'INR',
                'duration_days' => 30,
                'features' => [
                    'Family checkups (up to 4)',
                    'Unlimited consultations',
                    'Family dashboard',
                    'Child monitoring',
                    'Senior care services',
                    'Emergency support'
                ],
                'icon_class' => 'fa-users',
                'color_theme' => 'orange',
                'is_popular' => true,
                'popular_label' => 'Family Choice',
                'is_active' => true,
                'sort_order' => 4,
                'button_text' => 'Get Started',
                'button_link' => null,
            ],
        ];

        foreach ($plans as $plan) {
            HealthcarePlan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}

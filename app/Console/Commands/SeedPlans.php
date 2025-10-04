<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Plans\Models\Plan;

class SeedPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed sample healthcare plans data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Seeding healthcare plans...');

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
            $this->line("✓ Created plan: {$planData['name']}");
        }

        $this->info('Successfully seeded ' . count($plans) . ' healthcare plans!');
    }
}

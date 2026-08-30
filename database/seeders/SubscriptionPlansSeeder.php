<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Plans\Models\Plan;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding Subscription Plans...');
        $this->command->info('');

        $plans = [
            [
                'name' => 'Individual Subscription Plan',
                'description' => 'Perfect for single adult or child. Get comprehensive healthcare services at home.',
                'monthly_price' => 999.00,
                'members_included' => '1 adult/child',
                'price' => 999.00, // Base monthly price
                'currency' => 'INR',
                'duration_days' => 30, // Monthly
                'payment_options' => [
                    'half_yearly' => [
                        'price' => 5994.00, // 999 * 6
                        'label' => '6 Months',
                        'description' => 'Pay 6 months. Coverage 6 months. No extra years.',
                        'payable_years' => 0.5,
                        'care_benefits_years' => 0
                    ],
                    'annually' => [
                        'price' => 11988.00, // 999 * 12
                        'label' => '1 Year',
                        'description' => 'Pay 12 months. Five consecutive years unlock 10 years of service.',
                        'payable_years' => 5,
                        'care_benefits_years' => 5
                    ],
                    'full_payment' => [
                        'price' => 35964.00, // 999 * 36
                        'label' => '3 Years',
                        'description' => 'Pay 36 months once. Get 10 years of service (7 extra years).',
                        'payable_years' => 3,
                        'care_benefits_years' => 7
                    ]
                ],
                'features' => [
                    '24x7 Home Nursing Care',
                    'Regular Health Checkups',
                    'Body-Mind Relaxation Sessions',
                    'Expert Nursing Staff',
                    'All Medical Equipment Provided',
                    '10 Years Total Care Coverage'
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Parent Care Pack',
                'description' => 'Ideal for couples. Comprehensive healthcare for 2 family members.',
                'monthly_price' => 1699.00,
                'members_included' => '2 members in the family',
                'price' => 1699.00,
                'currency' => 'INR',
                'duration_days' => 30,
                'payment_options' => [
                    'half_yearly' => [
                        'price' => 10194.00, // 1699 * 6
                        'label' => '6 Months',
                        'description' => 'Pay 6 months. Coverage 6 months. No extra years.',
                        'payable_years' => 0.5,
                        'care_benefits_years' => 0
                    ],
                    'annually' => [
                        'price' => 20388.00, // 1699 * 12
                        'label' => '1 Year',
                        'description' => 'Pay 12 months. Five consecutive years unlock 10 years of service.',
                        'payable_years' => 5,
                        'care_benefits_years' => 5
                    ],
                    'full_payment' => [
                        'price' => 61164.00, // 1699 * 36
                        'label' => '3 Years',
                        'description' => 'Pay 36 months once. Get 10 years of service (7 extra years).',
                        'payable_years' => 3,
                        'care_benefits_years' => 7
                    ]
                ],
                'features' => [
                    '24x7 Home Nursing Care for 2 Members',
                    'Regular Health Checkups',
                    'Body-Mind Relaxation Sessions',
                    'Expert Nursing Staff',
                    'All Medical Equipment Provided',
                    '10 Years Total Care Coverage'
                ],
                'is_active' => true,
                'is_popular' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Family Care Pack',
                'description' => 'Perfect for small families. Healthcare for 2 adults and 1 child.',
                'monthly_price' => 2199.00,
                'members_included' => '2 adults + 1 child in the family',
                'price' => 2199.00,
                'currency' => 'INR',
                'duration_days' => 30,
                'payment_options' => [
                    'half_yearly' => [
                        'price' => 13194.00, // 2199 * 6
                        'label' => '6 Months',
                        'description' => 'Pay 6 months. Coverage 6 months. No extra years.',
                        'payable_years' => 0.5,
                        'care_benefits_years' => 0
                    ],
                    'annually' => [
                        'price' => 26388.00, // 2199 * 12
                        'label' => '1 Year',
                        'description' => 'Pay 12 months. Five consecutive years unlock 10 years of service.',
                        'payable_years' => 5,
                        'care_benefits_years' => 5
                    ],
                    'full_payment' => [
                        'price' => 79164.00, // 2199 * 36
                        'label' => '3 Years',
                        'description' => 'Pay 36 months once. Get 10 years of service (7 extra years).',
                        'payable_years' => 3,
                        'care_benefits_years' => 7
                    ]
                ],
                'features' => [
                    '24x7 Home Nursing Care for 3 Members',
                    'Regular Health Checkups',
                    'Body-Mind Relaxation Sessions',
                    'Expert Nursing Staff',
                    'All Medical Equipment Provided',
                    '10 Years Total Care Coverage'
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Premium Family Pack',
                'description' => 'Complete healthcare solution for large families. Covers 4 family members.',
                'monthly_price' => 2999.00,
                'members_included' => '4 family members',
                'price' => 2999.00,
                'currency' => 'INR',
                'duration_days' => 30,
                'payment_options' => [
                    'half_yearly' => [
                        'price' => 17994.00, // 2999 * 6
                        'label' => '6 Months',
                        'description' => 'Pay 6 months. Coverage 6 months. No extra years.',
                        'payable_years' => 0.5,
                        'care_benefits_years' => 0
                    ],
                    'annually' => [
                        'price' => 35988.00, // 2999 * 12
                        'label' => '1 Year',
                        'description' => 'Pay 12 months. Five consecutive years unlock 10 years of service.',
                        'payable_years' => 5,
                        'care_benefits_years' => 5
                    ],
                    'full_payment' => [
                        'price' => 107964.00, // 2999 * 36
                        'label' => '3 Years',
                        'description' => 'Pay 36 months once. Get 10 years of service (7 extra years).',
                        'payable_years' => 3,
                        'care_benefits_years' => 7
                    ]
                ],
                'features' => [
                    '24x7 Home Nursing Care for 4 Members',
                    'Regular Health Checkups',
                    'Body-Mind Relaxation Sessions',
                    'Expert Nursing Staff',
                    'All Medical Equipment Provided',
                    '10 Years Total Care Coverage'
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $planData) {
            $plan = Plan::updateOrCreate(
                ['name' => $planData['name']],
                $planData
            );
            $this->command->info("   ✅ Plan created: {$plan->name} (₹{$plan->monthly_price}/month)");
        }

        $this->command->info('');
        $this->command->info('✅ Subscription plans seeded successfully!');
    }
}


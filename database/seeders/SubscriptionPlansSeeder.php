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
                    'monthly' => [
                        'price' => 999.00,
                        'label' => 'Monthly',
                        'description' => 'Pay monthly'
                    ],
                    'half_yearly' => [
                        'price' => 5994.00, // 999 * 6 months
                        'label' => 'Half Yearly',
                        'description' => '7-year payable, 3 years extra care benefits',
                        'payable_years' => 7,
                        'care_benefits_years' => 3
                    ],
                    'annually' => [
                        'price' => 9990.00, // 999 * 10 months (discounted)
                        'label' => 'Annually',
                        'description' => '5-year payable, 5 years extra care benefits',
                        'payable_years' => 5,
                        'care_benefits_years' => 5
                    ],
                    'full_payment' => [
                        'price' => 29970.00, // 999 * 30 months (3 years)
                        'label' => 'Full Payment',
                        'description' => '3-year payment, 7 years extra care benefits',
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
                    'monthly' => [
                        'price' => 1699.00,
                        'label' => 'Monthly',
                        'description' => 'Pay monthly'
                    ],
                    'half_yearly' => [
                        'price' => 10194.00, // 1699 * 6 months
                        'label' => 'Half Yearly',
                        'description' => '7-year payable, 3 years extra care benefits',
                        'payable_years' => 7,
                        'care_benefits_years' => 3
                    ],
                    'annually' => [
                        'price' => 16990.00, // 1699 * 10 months (discounted)
                        'label' => 'Annually',
                        'description' => '5-year payable, 5 years extra care benefits',
                        'payable_years' => 5,
                        'care_benefits_years' => 5
                    ],
                    'full_payment' => [
                        'price' => 50970.00, // 1699 * 30 months (3 years)
                        'label' => 'Full Payment',
                        'description' => '3-year payment, 7 years extra care benefits',
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
                    'monthly' => [
                        'price' => 2199.00,
                        'label' => 'Monthly',
                        'description' => 'Pay monthly'
                    ],
                    'half_yearly' => [
                        'price' => 13194.00, // 2199 * 6 months
                        'label' => 'Half Yearly',
                        'description' => '7-year payable, 3 years extra care benefits',
                        'payable_years' => 7,
                        'care_benefits_years' => 3
                    ],
                    'annually' => [
                        'price' => 21990.00, // 2199 * 10 months (discounted)
                        'label' => 'Annually',
                        'description' => '5-year payable, 5 years extra care benefits',
                        'payable_years' => 5,
                        'care_benefits_years' => 5
                    ],
                    'full_payment' => [
                        'price' => 65970.00, // 2199 * 30 months (3 years)
                        'label' => 'Full Payment',
                        'description' => '3-year payment, 7 years extra care benefits',
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
                    'monthly' => [
                        'price' => 2999.00,
                        'label' => 'Monthly',
                        'description' => 'Pay monthly'
                    ],
                    'half_yearly' => [
                        'price' => 17994.00, // 2999 * 6 months
                        'label' => 'Half Yearly',
                        'description' => '7-year payable, 3 years extra care benefits',
                        'payable_years' => 7,
                        'care_benefits_years' => 3
                    ],
                    'annually' => [
                        'price' => 29990.00, // 2999 * 10 months (discounted)
                        'label' => 'Annually',
                        'description' => '5-year payable, 5 years extra care benefits',
                        'payable_years' => 5,
                        'care_benefits_years' => 5
                    ],
                    'full_payment' => [
                        'price' => 89970.00, // 2999 * 30 months (3 years)
                        'label' => 'Full Payment',
                        'description' => '3-year payment, 7 years extra care benefits',
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


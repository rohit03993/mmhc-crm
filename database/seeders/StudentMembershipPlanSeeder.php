<?php

namespace Database\Seeders;

use App\Modules\Plans\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class StudentMembershipPlanSeeder extends Seeder
{
    public function run(): void
    {
        $launchPrice = (float) config('student_subscription.display.launch_price_inr', 1200);
        $years = (int) config('student_subscription.display.duration_years', 10);
        $slug = (string) config('student_subscription.plan_slug', 'student-journey-launch');
        $frequency = (string) config('student_subscription.payment_frequency', 'student_launch');

        $attributes = [
            'name' => 'Student Journey Membership',
            'description' => '10-year student academics & healthcare journey membership. Launch offer: one-time payment for students only.',
            'price' => $launchPrice,
            'monthly_price' => (float) config('student_subscription.display.monthly_reference_inr', 100),
            'members_included' => '1 student',
            'currency' => 'INR',
            'duration_days' => $years * 365,
            'payment_options' => [
                $frequency => [
                    'price' => $launchPrice,
                    'label' => 'Launch offer — one-time',
                    'description' => "{$years}-year student membership (launch pricing)",
                    'payable_years' => $years,
                    'care_benefits_years' => 0,
                    'price_includes_gst' => true,
                ],
            ],
            'features' => [
                'Full academics portal access',
                'Assignments, exams & learning resources',
                'Cross-institute mentorship',
                'Part of MMHC\'s future-shaping student community',
                "{$years}-year membership validity",
            ],
            'icon_class' => 'fa-graduation-cap',
            'color_theme' => 'indigo',
            'is_active' => true,
            'is_popular' => false,
            'sort_order' => 100,
            'button_text' => 'Subscribe now',
        ];

        if (Schema::hasColumn('plans', 'slug')) {
            $attributes['slug'] = $slug;
            $attributes['audience'] = 'student';
            Plan::updateOrCreate(['slug' => $slug], $attributes);
        } else {
            Plan::updateOrCreate(['name' => 'Student Journey Membership'], $attributes);
        }

        $this->command?->info('Student Journey Membership plan seeded (slug: '.$slug.').');
    }
}

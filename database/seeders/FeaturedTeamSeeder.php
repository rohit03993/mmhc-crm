<?php

namespace Database\Seeders;

use App\Models\FeaturedTeam;
use Illuminate\Database\Seeder;

class FeaturedTeamSeeder extends Seeder
{
    public function run(): void
    {
        if (FeaturedTeam::exists()) {
            return;
        }

        $members = [
            [
                'name' => 'Dr. Sarah Johnson',
                'title' => 'Senior Caregiver & RN',
                'rating' => 4.9,
                'reviews_count' => 127,
                'bio' => 'Specializing in elderly care with 8 years of experience. Passionate about providing compassionate healthcare and building meaningful relationships with patients.',
                'skills' => 'Elderly Care, Diabetes Management, Medication Admin',
            ],
            [
                'name' => 'Dr. Michael Chen',
                'title' => 'Cardiac Care Specialist',
                'rating' => 4.8,
                'reviews_count' => 89,
                'bio' => 'Expert in cardiac care and rehabilitation. 12 years of experience helping patients recover and maintain heart health through personalized care plans.',
                'skills' => 'Cardiac Care, Rehabilitation, Health Coaching',
            ],
            [
                'name' => 'Nurse Emily Rodriguez',
                'title' => 'Pediatric Care Specialist',
                'rating' => 5.0,
                'reviews_count' => 156,
                'bio' => 'Specializing in pediatric care with 6 years of experience. Known for her gentle approach and ability to make children feel comfortable during medical care.',
                'skills' => 'Pediatric Care, Child Development, Vaccination',
            ],
            [
                'name' => 'Dr. James Wilson',
                'title' => 'Emergency Care Specialist',
                'rating' => 4.9,
                'reviews_count' => 203,
                'bio' => 'Emergency care expert with 15 years of experience. Available 24/7 for urgent medical situations and providing immediate, life-saving care.',
                'skills' => 'Emergency Care, Trauma Care, 24/7 Available',
            ],
        ];

        foreach ($members as $i => $data) {
            FeaturedTeam::create(array_merge($data, ['sort_order' => $i]));
        }
    }
}

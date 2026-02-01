<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Sarah Johnson',
                'quote' => 'MMHC has completely transformed how I manage my health. The caregivers are incredibly professional and caring. The medication reminders have been a lifesaver, and I love how easy it is to book appointments. I recommend MMHC to everyone!',
                'rating' => 5.0,
                'patient_since' => 'Patient since 2022',
                'sort_order' => 1,
            ],
            [
                'name' => 'Robert Chen',
                'quote' => "As a senior citizen, I was worried about managing my medications and appointments. MMHC made everything so simple! Dr. Sarah is amazing - she's patient, knowledgeable, and always available when I need help. The 24/7 support gives me peace of mind.",
                'rating' => 5.0,
                'patient_since' => 'Patient since 2021',
                'sort_order' => 2,
            ],
            [
                'name' => 'Maria Rodriguez',
                'quote' => "I'm a busy working mother and finding time for healthcare was always a challenge. MMHC's telemedicine feature is incredible! I can consult with doctors from home, and the medication delivery service is so convenient. The family plan covers all of us perfectly.",
                'rating' => 5.0,
                'patient_since' => 'Patient since 2023',
                'sort_order' => 3,
            ],
            [
                'name' => 'David Wilson',
                'quote' => "After my heart surgery, I needed continuous care and monitoring. MMHC's cardiac care program has been exceptional. Dr. Michael is not just a doctor, he's become like family. The recovery tracking and health coaching have helped me get back to my normal life faster than expected.",
                'rating' => 5.0,
                'patient_since' => 'Patient since 2022',
                'sort_order' => 4,
            ],
        ];

        foreach ($items as $item) {
            Testimonial::updateOrCreate(
                ['name' => $item['name'], 'sort_order' => $item['sort_order']],
                array_merge($item, ['image_path' => null])
            );
        }
    }
}

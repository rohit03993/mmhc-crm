<?php

namespace Database\Seeders;

use App\Models\PageContent;
use Illuminate\Database\Seeder;

class PageContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'section' => 'hero',
                'title' => 'Your Health is Our Priority',
                'subtitle' => 'India\'s newest home nursing subscription with 24x7 care, expert nursing staff, and body-mind relaxation therapy. Making quality healthcare accessible and affordable.',
                'content' => [
                    'button_text' => 'Subscribe Now',
                    'secondary_button_text' => 'View Plans',
                    'tagline_hindi' => 'आपके अपनों की देखभाल, हमारे अनुभवी हाथों में',
                ],
                'is_active' => true,
            ],
            [
                'section' => 'plans',
                'title' => 'Subscription Plans',
                'subtitle' => 'Affordable monthly subscription plans for your family\'s healthcare needs. Starting at just Rs 999/month.',
                'content' => [
                    'tagline_hindi' => 'आपकी सेहत, हमारी जिम्मेदारी',
                ],
                'is_active' => true,
            ],
            [
                'section' => 'star_performers',
                'title' => 'Meet Our Expert Nursing Team',
                'subtitle' => 'Trained & verified nurses and attendants providing all-round medical support with compassion and professionalism.',
                'content' => [
                    'tagline_hindi' => 'हम समझते हैं आपकी हर जरूरत',
                ],
                'is_active' => true,
            ],
            [
                'section' => 'why_choose',
                'title' => 'Why Choose Med Miracle Health Care?',
                'subtitle' => 'India\'s first largest superhero nursing association providing comprehensive home healthcare with an empathetic approach.',
                'content' => [
                    'features' => [
                        [
                            'icon' => 'fa-clock',
                            'title' => '24x7 Home Health Care',
                            'description' => 'Round-the-clock personal nursing staff assistance at your doorstep. Free services with quick & easy booking.',
                        ],
                        [
                            'icon' => 'fa-spa',
                            'title' => 'Body-Mind Relaxation Therapy',
                            'description' => 'Unique holistic approach with advanced wellness equipment including full body massager, foot reflexology, brain & heart function monitoring at home.',
                        ],
                        [
                            'icon' => 'fa-user-nurse',
                            'title' => 'Expert Nursing Staff',
                            'description' => 'Well-experienced, trained & verified nursing staff for critical patients who understand psychological needs and provide all-round medical support.',
                        ],
                        [
                            'icon' => 'fa-hands-helping',
                            'title' => 'Professional Caretaker Services',
                            'description' => 'Compassionate caretaker services at home with an empathetic approach, ensuring personal attention to every detail of patient care.',
                        ],
                        [
                            'icon' => 'fa-baby',
                            'title' => 'Special Care Programs',
                            'description' => 'Dedicated support for pregnant ladies & newborns, plus regular check-ups and specialized care for senior citizens.',
                        ],
                        [
                            'icon' => 'fa-rupee-sign',
                            'title' => 'Affordable Subscriptions',
                            'description' => 'Starting at just Rs 999/month. All-inclusive subscription plans with no expensive equipment costs - everything provided by us.',
                        ],
                    ],
                    'tagline_hindi' => 'घर बैठे संपूर्ण सेवा, 24x7 देखभाल',
                ],
                'is_active' => true,
            ],
            [
                'section' => 'about',
                'title' => 'About Med Miracle Health Care',
                'subtitle' => 'India\'s newest home nursing subscription service, founded with a vision to make quality healthcare accessible and affordable for everyone.',
                'content' => [
                    'story' => 'Meet our founder, Mantu Kumar, the visionary behind Med Miracle Health Care. With a deep commitment to holistic well-being, Mantu Kumar has built the company into a leader in personalized home healthcare. His dedication to providing compassionate, evidence-based care has led to over 10,000 successful patient outcomes, establishing Med Miracle Health Care as a trusted name in the industry. Today, MMHC proudly stands as India\'s first largest superhero nursing association, operating across Patna, Ranchi, Bhopal, Noida, and Gurgaon.',
                    'mission' => 'We at Med Miracle Healthcare are committed to making quality healthcare accessible and affordable. With our monthly subscription plans, we provide our customers with nursing care at home, regular checkups, mind-body relaxation sessions and much more. Our mission is to solve the problems of non-empathetic healthcare, expensive medical equipment, poor medical environment, and incompetent staff by providing personalized, compassionate care at your doorstep.',
                    'vision' => 'To become India\'s leading home healthcare provider, expanding our services to more cities and touching more lives. We envision a future where every family has access to affordable, professional healthcare services at home, with trained nursing staff who understand both the medical and psychological needs of patients.',
                    'achievements' => '10,000+ successful patient outcomes, Multiple award-winning service including Indian Icon of the Year, India Excellence Award, International Excellence Awards, and Best Home Nursing Healthcare Services. NACH Powered and proud member of BNI.',
                ],
                'is_active' => true,
            ],
            [
                'section' => 'testimonials',
                'title' => 'What Our Patients Say',
                'subtitle' => 'Don\'t just take our word for it. Here\'s what our patients have to say about their experience with MMHC.',
                'content' => [],
                'is_active' => true,
            ],
            [
                'section' => 'contact',
                'title' => 'Get In Touch With Us',
                'subtitle' => 'Have questions about our services? Ready to subscribe? We\'re here 24x7 to help you with all your home healthcare needs.',
                'content' => [
                    'address' => 'Udgam Incubation Centre, Rohit Nagar, Phase 1 (Near Surya Children School), Bhopal 462023',
                    'phone' => '9113311256',
                    'emergency_phone' => '9113311256',
                    'email' => 'Care@themmhc.com',
                    'support_email' => 'Care@themmhc.com',
                    'website' => 'www.themmhc.com',
                    'service_locations' => 'Patna | Ranchi | Bhopal | Noida | Gurgaon',
                    'business_hours' => [
                        'availability' => '24x7 Home Healthcare Services Available',
                        'subscription' => 'Monthly Subscription Plans Starting Rs 999',
                        'response' => 'Quick Response Time - Free Doorstep Service',
                    ],
                    'social_media' => [
                        'facebook' => '#',
                        'twitter' => '#',
                        'instagram' => '#',
                        'linkedin' => '#',
                        'youtube' => '#',
                    ],
                    'tagline_hindi' => 'आपकी सेहत, हमारा मिशन। संपर्क करें आज ही',
                ],
                'is_active' => true,
            ],
            [
                'section' => 'footer',
                'title' => 'Med Miracle Health Care',
                'subtitle' => 'India\'s newest home nursing subscription service.',
                'content' => [
                    'description' => 'Dedicated to making quality healthcare accessible and affordable. With 24x7 care, expert nursing staff, and body-mind relaxation therapy, we bring comprehensive healthcare to your doorstep. Operating in Patna, Ranchi, Bhopal, Noida, and Gurgaon.',
                    'copyright' => '© 2024 Med Miracle Health Care (MMHC). All rights reserved.',
                    'tagline' => 'Your Health is Our Priority',
                    'founded_by' => 'Founded by Mantu Kumar',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($sections as $section) {
            PageContent::updateOrCreate(
                ['section' => $section['section']],
                $section
            );
        }
    }
}


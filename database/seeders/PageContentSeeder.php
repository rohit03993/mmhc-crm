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
                'title' => 'Your Health, Our Priority',
                'subtitle' => 'Experience comprehensive healthcare with our expert caregivers, advanced technology, and personalized care plans.',
                'content' => [
                    'button_text' => 'Get Started Today',
                    'secondary_button_text' => 'Learn More',
                ],
                'is_active' => true,
            ],
            [
                'section' => 'plans',
                'title' => 'Choose Your Healthcare Plan',
                'subtitle' => 'Flexible healthcare plans designed to meet your needs. From basic checkups to comprehensive family care.',
                'content' => [],
                'is_active' => true,
            ],
            [
                'section' => 'star_performers',
                'title' => 'Meet Our Star Performers',
                'subtitle' => 'Our expert caregivers and nursing staff are dedicated to providing exceptional healthcare services with compassion and expertise.',
                'content' => [],
                'is_active' => true,
            ],
            [
                'section' => 'why_choose',
                'title' => 'Why Choose MMHC?',
                'subtitle' => 'We\'re committed to providing exceptional healthcare services with cutting-edge technology, compassionate care, and unmatched expertise.',
                'content' => [
                    'features' => [
                        [
                            'icon' => 'fa-clock',
                            'title' => '24/7 Availability',
                            'description' => 'Healthcare doesn\'t wait for business hours. Our team is available around the clock to provide immediate care and support whenever you need it.',
                        ],
                        [
                            'icon' => 'fa-pills',
                            'title' => 'Smart Medication Management',
                            'description' => 'Never miss a dose again. Our advanced medication tracking system sends reminders and helps manage complex medication schedules.',
                        ],
                        [
                            'icon' => 'fa-mobile-alt',
                            'title' => 'Easy Appointment Booking',
                            'description' => 'Book appointments with just a few clicks. Our intuitive platform makes scheduling healthcare visits simple and convenient.',
                        ],
                        [
                            'icon' => 'fa-graduation-cap',
                            'title' => 'Certified Professionals',
                            'description' => 'All our caregivers are licensed, certified, and continuously trained to provide the highest quality healthcare services.',
                        ],
                        [
                            'icon' => 'fa-rupee-sign',
                            'title' => 'Affordable Plans',
                            'description' => 'Quality healthcare shouldn\'t break the bank. Our flexible pricing plans make professional healthcare accessible to everyone.',
                        ],
                        [
                            'icon' => 'fa-ambulance',
                            'title' => 'Emergency Support',
                            'description' => 'When emergencies strike, we\'re here. Our rapid response team provides immediate medical assistance and coordination.',
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            [
                'section' => 'about',
                'title' => 'About MMHC',
                'subtitle' => 'Founded with a vision to revolutionize healthcare delivery, MMHC has been at the forefront of providing compassionate, accessible, and innovative healthcare services.',
                'content' => [
                    'story' => 'MMHC (Modern Medical Healthcare Center) was founded in 2018 with a simple yet powerful mission: to make quality healthcare accessible to everyone, everywhere. What started as a small team of dedicated healthcare professionals has grown into a comprehensive healthcare platform serving thousands of patients across the region.',
                    'mission' => 'To provide accessible, affordable, and high-quality healthcare services through innovative technology and compassionate care, ensuring that every patient receives the medical attention they deserve, when they need it most.',
                    'vision' => 'To become the leading healthcare platform that transforms how people access and experience healthcare, creating a world where quality medical care is available to everyone, regardless of their location or circumstances.',
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
                'title' => 'Get In Touch',
                'subtitle' => 'Have questions about our services? Ready to start your healthcare journey? We\'re here to help you every step of the way.',
                'content' => [
                    'address' => '123 Healthcare Street, Medical District, Mumbai 400001, Maharashtra, India',
                    'phone' => '+91 98765 43210',
                    'emergency_phone' => '+91 98765 43211',
                    'email' => 'info@themmhc.com',
                    'support_email' => 'support@themmhc.com',
                    'business_hours' => [
                        'weekdays' => 'Monday - Friday: 8:00 AM - 8:00 PM',
                        'saturday' => 'Saturday: 9:00 AM - 6:00 PM',
                        'sunday' => 'Sunday: Emergency Services Only',
                    ],
                    'social_media' => [
                        'facebook' => '#',
                        'twitter' => '#',
                        'instagram' => '#',
                        'linkedin' => '#',
                        'youtube' => '#',
                    ],
                ],
                'is_active' => true,
            ],
            [
                'section' => 'footer',
                'title' => 'MMHC',
                'subtitle' => 'Modern Medical Healthcare Center - Your trusted partner in healthcare.',
                'content' => [
                    'description' => 'Providing compassionate, accessible, and innovative healthcare services to communities across India.',
                    'copyright' => '© 2024 MMHC (Modern Medical Healthcare Center). All rights reserved.',
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


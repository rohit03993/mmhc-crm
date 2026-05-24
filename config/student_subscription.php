<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Student membership (academics) — launch offer
    |--------------------------------------------------------------------------
    | Shown after phone verification. Standard framing: ₹100/month × 12 × 10 years.
    | Launch: one-time ₹1,200 (GST-inclusive when price_includes_gst is set on plan).
    */
    'enabled' => env('STUDENT_SUBSCRIPTION_ENABLED', true),

    'plan_slug' => env('STUDENT_SUBSCRIPTION_PLAN_SLUG', 'student-journey-launch'),

    'payment_frequency' => 'student_launch',

    'display' => [
        'monthly_reference_inr' => 100,
        'duration_years' => 10,
        'list_value_inr' => 12000,
        'launch_price_inr' => 1200,
        'headline' => 'Join the Student Journey',
        'subheadline' => 'Be part of a unique, future-shaping healthcare & academics experience — students only.',
    ],
];

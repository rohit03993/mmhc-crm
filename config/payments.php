<?php

return [
    'provider' => env('PAYMENT_PROVIDER', 'manual'),
    'mode' => env('PAYMENT_MODE', 'test'),
    /*
    | Subscription checkout (patients / healthcare plans): use Razorpay (payments.razorpay).
    | Manual UPI + screenshot is a fallback when Razorpay is off, or if manual_with_razorpay is true.
    */
    'subscription' => [
        'manual_enabled' => env('SUBSCRIPTION_MANUAL_PAYMENT_ENABLED', false),
        'manual_with_razorpay' => env('SUBSCRIPTION_MANUAL_WITH_RAZORPAY', false),
    ],
    'staff_payout' => [
        'manual_enabled' => env('STAFF_PAYOUT_MANUAL_ENABLED', true),
        // MMHC policy: staff payouts are recorded manually (bank/UPI + proof). RazorpayX stays off unless explicitly enabled.
        'razorpayx_allowed' => env('STAFF_PAYOUT_RAZORPAYX_ALLOWED', false),
    ],

    'razorpay' => [
        'enabled' => env('RAZORPAY_ENABLED', false),
        'key_id' => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
        'currency' => env('RAZORPAY_CURRENCY', 'INR'),
    ],

    'razorpayx' => [
        'enabled' => env('RAZORPAYX_ENABLED', false),
        'key_id' => env('RAZORPAYX_KEY_ID', env('RAZORPAY_KEY_ID')),
        'key_secret' => env('RAZORPAYX_KEY_SECRET', env('RAZORPAY_KEY_SECRET')),
        'base_url' => env('RAZORPAYX_BASE_URL', 'https://api.razorpay.com/v1'),
        'verify_ssl' => env('RAZORPAYX_VERIFY_SSL', true),
    ],
];

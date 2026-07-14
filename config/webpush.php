<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PWA Web Push (VAPID) — no Capacitor / Play Store required
    |--------------------------------------------------------------------------
    |
    | Generate keys: php artisan webpush:vapid
    | Then copy VAPID_PUBLIC_KEY / VAPID_PRIVATE_KEY / VAPID_SUBJECT into .env
    |
    */
    'enabled' => (bool) env('WEBPUSH_ENABLED', true),

    'vapid' => [
        'subject' => env('VAPID_SUBJECT', env('APP_URL', 'mailto:admin@themmhc.com')),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],

    'ttl' => (int) env('WEBPUSH_TTL', 60 * 60 * 24),
];

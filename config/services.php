<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | OTP storage (HMAC in cache). Use Redis cache store in production.
    */
    'phone_otp' => [
        'ttl_seconds' => (int) env('PHONE_LOGIN_OTP_TTL_SECONDS', 600),
        'bind_ttl_seconds' => (int) env('PHONE_BIND_OTP_TTL_SECONDS', 300),
        'pepper' => env('OTP_PEPPER', env('APP_KEY')),
    ],

    /*
    | Pal Digital — WhatsApp OTP (mmhc_verification_code2 authentication template).
    | All OTP flows (login, profile, rewards, referrals, service completion) use this campaign.
    | Campaign trigger: POST /api/v1/integrations/campaigns/{id}/trigger
    */
    'pal_digital' => [
        'integration_key' => env('PAL_DIGITAL_INTEGRATION_KEY'),
        'campaign_id' => env('PAL_DIGITAL_CAMPAIGN_ID'),
        'base_url' => env('PAL_DIGITAL_BASE_URL', 'https://wa.paldigital.in'),
        'default_contact_name' => env('PAL_DIGITAL_DEFAULT_CONTACT_NAME', 'MMHC User'),
        'include_button_parameters' => filter_var(env('PAL_DIGITAL_INCLUDE_BUTTON_PARAMETERS', true), FILTER_VALIDATE_BOOLEAN),
        'ca_bundle' => env('PAL_DIGITAL_CA_BUNDLE'),
        'http_verify' => filter_var(env('PAL_DIGITAL_HTTP_VERIFY', true), FILTER_VALIDATE_BOOLEAN),
    ],

];

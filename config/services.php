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
    | AiSensy / Sensy WhatsApp Business API (OTP via WhatsApp)
    | Create an Authentication template in AiSensy, then an API Campaign using it.
    | destination = +91 followed by 10-digit Indian number.
    | Use SENSY_API_URL for host, or AISENSY_BASE_URL for full endpoint URL.
    */
    'aisensy' => [
        'api_key' => env('AISENSY_API_KEY'),
        'campaign_name' => env('AISENSY_CAMPAIGN_NAME', ''),
        'base_url' => env('AISENSY_BASE_URL') ?: (rtrim(env('SENSY_API_URL', 'https://backend.aisensy.com'), '/') . '/campaign/t1/api/v2'),
        // Campaign expects 1 param; same value (OTP) is used for body {{1}} and copy-code button {{1}}.
        'template_param_count' => min(2, max(1, (int) env('AISENSY_TEMPLATE_PARAM_COUNT', 1))),
    ],

];

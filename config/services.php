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
    | Sent.dm — SMS OTP (template message)
    | https://docs.sent.dm/reference/api/messages/SentDmServicesEndpointsCustomerAPIv3MessagesSendMessageV3Endpoint
    | SENT_DM_OTP_PARAMETER_NAME must match your template placeholder (e.g. code, otp).
    */
    'sent_dm' => [
        'api_key' => env('SENT_DM_API_KEY'),
        'template_id' => env('SENT_DM_TEMPLATE_ID'),
        'otp_parameter_name' => env('SENT_DM_OTP_PARAMETER_NAME', 'code'),
        'base_url' => env('SENT_DM_BASE_URL', 'https://api.sent.dm/v3/messages'),
        'sandbox' => filter_var(env('SENT_DM_SANDBOX', false), FILTER_VALIDATE_BOOLEAN),
        // Windows cURL error 60: set SENT_DM_CA_BUNDLE to path of https://curl.se/ca/cacert.pem
        'ca_bundle' => env('SENT_DM_CA_BUNDLE'),
        'http_verify' => filter_var(env('SENT_DM_HTTP_VERIFY', true), FILTER_VALIDATE_BOOLEAN),
    ],

    /*
    | Login OTP storage (HMAC in cache). Use Redis cache store in production.
    */
        'phone_otp' => [
        'ttl_seconds' => (int) env('PHONE_LOGIN_OTP_TTL_SECONDS', 600),
        'bind_ttl_seconds' => (int) env('PHONE_BIND_OTP_TTL_SECONDS', 300),
        'pepper' => env('OTP_PEPPER', env('APP_KEY')),
    ],

    /*
    | OTP delivery: whatsapp (Pal Digital) or sent_dm_sms (legacy SMS).
    */
    'otp_delivery' => [
        'channel' => env('OTP_DELIVERY_CHANNEL', 'whatsapp'),
    ],

    /*
    | Pal Digital — WhatsApp API (mmhc_verification_code2 authentication template).
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

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription GST Rate
    |--------------------------------------------------------------------------
    |
    | GST rate applied to subscription base amount (in percentage)
    | Default: 18.00 (18%)
    |
    */
    'gst_rate' => env('SUBSCRIPTION_GST_RATE', 18),

    /*
    |--------------------------------------------------------------------------
    | Referral Commission Rate
    |--------------------------------------------------------------------------
    |
    | Commission rate for staff (nurse/caregiver) who refer patients
    | to subscribe (in percentage of base amount)
    | Default: 5.00 (5%)
    | This can be edited by admin in the admin panel
    |
    */
    'referral_commission_rate' => env('SUBSCRIPTION_REFERRAL_COMMISSION_RATE', 5),

    /*
    |--------------------------------------------------------------------------
    | UPI Payment Details
    |--------------------------------------------------------------------------
    |
    | UPI ID for manual payments
    |
    */
    'upi_id' => env('SUBSCRIPTION_UPI_ID', 'rohit03993@icici'),
    'upi_merchant_name' => env('SUBSCRIPTION_UPI_MERCHANT_NAME', 'MMHC'),
];


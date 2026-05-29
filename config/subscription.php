<?php

/*
| Defaults only. Admin saves via Subscription Settings → site_settings table
| (see App\Modules\Plans\Support\SubscriptionSettings). Those values override .env.
*/

return [
    'gst_rate' => env('SUBSCRIPTION_GST_RATE', 18),
    'upi_id' => env('SUBSCRIPTION_UPI_ID', 'mmhc@paytm'),
    'upi_merchant_name' => env('SUBSCRIPTION_UPI_MERCHANT_NAME', 'MMHC'),
];

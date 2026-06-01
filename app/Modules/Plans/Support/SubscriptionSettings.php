<?php

namespace App\Modules\Plans\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-managed subscription payment settings (UPI, GST).
 * Stored in site_settings so .env / config:cache cannot override admin saves.
 */
final class SubscriptionSettings
{
    public const KEY_GST_RATE = 'subscription_gst_rate';

    public const KEY_UPI_ID = 'subscription_upi_id';

    public const KEY_UPI_MERCHANT = 'subscription_upi_merchant_name';

    public const KEY_GST_NUMBER = 'subscription_gst_number';

    public static function gstRate(): float
    {
        return (float) self::get(self::KEY_GST_RATE, config('subscription.gst_rate', 18));
    }

    public static function upiId(): string
    {
        return (string) self::get(self::KEY_UPI_ID, config('subscription.upi_id', 'mmhc@paytm'));
    }

    public static function upiMerchantName(): string
    {
        return (string) self::get(self::KEY_UPI_MERCHANT, config('subscription.upi_merchant_name', 'MMHC'));
    }

    /**
     * GSTIN shown on tax invoices. Empty string in admin = hidden on invoice.
     */
    public static function gstNumber(): ?string
    {
        $value = trim((string) self::get(self::KEY_GST_NUMBER, ''));

        return $value !== '' ? $value : null;
    }

    /**
     * @param  array{gst_rate: float|string, upi_id: string, upi_merchant_name: string, gst_number?: string|null}  $data
     */
    public static function persist(array $data): void
    {
        if (! Schema::hasTable('site_settings')) {
            throw new \RuntimeException('site_settings table is missing. Run migrations.');
        }

        SiteSetting::set(self::KEY_GST_RATE, (string) $data['gst_rate']);
        SiteSetting::set(self::KEY_UPI_ID, trim((string) $data['upi_id']));
        SiteSetting::set(self::KEY_UPI_MERCHANT, trim((string) $data['upi_merchant_name']));
        SiteSetting::set(self::KEY_GST_NUMBER, trim((string) ($data['gst_number'] ?? '')));
    }

    private static function get(string $key, mixed $default): mixed
    {
        if (! Schema::hasTable('site_settings')) {
            return $default;
        }

        $value = SiteSetting::get($key);
        if ($value === null || $value === '') {
            return $default;
        }

        return $value;
    }
}

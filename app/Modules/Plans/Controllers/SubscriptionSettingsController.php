<?php

namespace App\Modules\Plans\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SubscriptionSettingsController extends Controller
{
    /**
     * Display subscription settings page
     */
    public function index()
    {
        $gstRate = config('subscription.gst_rate', 18.00);
        $commissionRate = config('subscription.referral_commission_rate', 5.00);
        $upiId = config('subscription.upi_id', 'mmhc@paytm');
        $merchantName = config('subscription.upi_merchant_name', 'MMHC');
        
        return view('plans::admin.settings.index', compact('gstRate', 'commissionRate', 'upiId', 'merchantName'));
    }

    /**
     * Update subscription settings
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gst_rate' => 'required|numeric|min:0|max:100',
            'referral_commission_rate' => 'required|numeric|min:0|max:100',
            'upi_id' => 'required|string|max:255',
            'upi_merchant_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $validated = $validator->validated();
            $configPath = config_path('subscription.php');

            // Build config content deterministically to avoid regex mismatches and malformed PHP.
            $gstRate = (float) $validated['gst_rate'];
            $referralRate = (float) $validated['referral_commission_rate'];
            $upiId = var_export((string) $validated['upi_id'], true);
            $merchantName = var_export((string) $validated['upi_merchant_name'], true);

            $configContent = <<<PHP
<?php

return [
    'gst_rate' => env('SUBSCRIPTION_GST_RATE', {$gstRate}),
    'referral_commission_rate' => env('SUBSCRIPTION_REFERRAL_COMMISSION_RATE', {$referralRate}),
    'upi_id' => env('SUBSCRIPTION_UPI_ID', {$upiId}),
    'upi_merchant_name' => env('SUBSCRIPTION_UPI_MERCHANT_NAME', {$merchantName}),
];

PHP;

            File::put($configPath, $configContent);

            // Cache clear should not block successful save if command is unavailable.
            try {
                \Artisan::call('config:clear');
            } catch (\Throwable $cacheError) {
                Log::warning('Subscription settings saved, but config cache clear failed', [
                    'message' => $cacheError->getMessage(),
                ]);
            }

            return redirect()->back()
                ->with('success', 'Subscription settings updated successfully!');

        } catch (\Exception $e) {
            Log::error('Subscription settings update failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->with('error', 'Unable to update settings. Please try again.')
                ->withInput();
        }
    }
}


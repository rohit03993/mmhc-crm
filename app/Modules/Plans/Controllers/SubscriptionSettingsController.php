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
            // Read current config file
            $configPath = config_path('subscription.php');
            if (!File::exists($configPath)) {
                return redirect()->back()
                    ->with('error', 'Subscription config file not found. Please ensure config/subscription.php exists.')
                    ->withInput();
            }
            $configContent = File::get($configPath);
            
            // Update GST rate
            $configContent = preg_replace(
                "/'gst_rate' => env\('SUBSCRIPTION_GST_RATE', [\d.]+\)/",
                "'gst_rate' => env('SUBSCRIPTION_GST_RATE', {$request->gst_rate})",
                $configContent
            );
            
            // Update commission rate
            $configContent = preg_replace(
                "/'referral_commission_rate' => env\('SUBSCRIPTION_REFERRAL_COMMISSION_RATE', [\d.]+\)/",
                "'referral_commission_rate' => env('SUBSCRIPTION_REFERRAL_COMMISSION_RATE', {$request->referral_commission_rate})",
                $configContent
            );
            
            // Update UPI ID
            $configContent = preg_replace(
                "/'upi_id' => env\('SUBSCRIPTION_UPI_ID', '[^']+'\)/",
                "'upi_id' => env('SUBSCRIPTION_UPI_ID', '{$request->upi_id}')",
                $configContent
            );
            
            // Update merchant name
            $configContent = preg_replace(
                "/'upi_merchant_name' => env\('SUBSCRIPTION_UPI_MERCHANT_NAME', '[^']+'\)/",
                "'upi_merchant_name' => env('SUBSCRIPTION_UPI_MERCHANT_NAME', '{$request->upi_merchant_name}')",
                $configContent
            );
            
            // Remove QR code line if it exists
            $configContent = preg_replace(
                "/\s*'qr_code' => env\('SUBSCRIPTION_QR_CODE', [^)]+\),?\s*\n?/",
                "",
                $configContent
            );
            
            // Write updated config
            File::put($configPath, $configContent);
            
            // Clear config cache
            \Artisan::call('config:clear');
            
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


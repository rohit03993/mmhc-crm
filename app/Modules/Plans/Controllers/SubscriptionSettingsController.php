<?php

namespace App\Modules\Plans\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Plans\Support\SubscriptionSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubscriptionSettingsController extends Controller
{
    /**
     * Display subscription settings page
     */
    public function index()
    {
        return view('plans::admin.settings.index', [
            'gstRate' => SubscriptionSettings::gstRate(),
            'gstNumber' => SubscriptionSettings::gstNumber() ?? '',
            'upiId' => SubscriptionSettings::upiId(),
            'merchantName' => SubscriptionSettings::upiMerchantName(),
        ]);
    }

    /**
     * Update subscription settings (persisted in site_settings DB).
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gst_rate' => 'required|numeric|min:0|max:100',
            'gst_number' => 'nullable|string|max:20',
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

            SubscriptionSettings::persist([
                'gst_rate' => $validated['gst_rate'],
                'gst_number' => $validated['gst_number'] ?? '',
                'upi_id' => $validated['upi_id'],
                'upi_merchant_name' => $validated['upi_merchant_name'],
            ]);

            return redirect()->back()
                ->with('success', 'Subscription settings saved. UPI, GST rate, and invoice GSTIN will apply immediately.');

        } catch (\Throwable $e) {
            Log::error('Subscription settings update failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()
                ->with('error', 'Unable to update settings. Please try again.')
                ->withInput();
        }
    }
}

<?php

namespace App\Modules\Payments\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Core\User;
use App\Modules\Payments\Models\StaffPayment;

class StaffPaymentController extends Controller
{
    /**
     * Show payment settings page for staff
     */
    public function settings()
    {
        $user = Auth::user();
        return view('payments::staff.settings', compact('user'));
    }

    /**
     * Update payment settings (UPI ID and QR code)
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'upi_id' => 'nullable|string|max:255',
            'qr_code' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Handle QR code upload
        if ($request->hasFile('qr_code')) {
            // Delete old QR code if exists
            if ($user->qr_code_path && Storage::disk('public')->exists($user->qr_code_path)) {
                Storage::disk('public')->delete($user->qr_code_path);
            }

            $qrCodePath = $request->file('qr_code')->store('staff-qr-codes', 'public');
            $user->qr_code_path = $qrCodePath;
        }

        $user->upi_id = $request->upi_id;
        $user->save();

        return redirect()->route('staff.payments.settings')
            ->with('success', 'Payment settings updated successfully.');
    }

    /**
     * Show payment history for staff
     */
    public function history()
    {
        $user = Auth::user();
        $payments = StaffPayment::where('staff_id', $user->id)
            ->with('admin')
            ->orderBy('paid_at', 'desc')
            ->paginate(20);

        return view('payments::staff.history', compact('payments'));
    }
}


<?php

namespace App\Modules\Plans\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Plans\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments for the authenticated user.
     */
    public function index()
    {
        $payments = auth()->user()->payments()->orderBy('created_at', 'desc')->paginate(10);
        return view('plans::payments.index', compact('payments'));
    }

    /**
     * Process a payment.
     */
    public function process(Request $request)
    {
        // Payment processing logic would go here
        return redirect()->route('payments.success');
    }

    /**
     * Show payment success page.
     */
    public function success()
    {
        return view('plans::payments.success');
    }

    /**
     * Show payment failure page.
     */
    public function failure()
    {
        return view('plans::payments.failure');
    }

    /**
     * Generate payment invoice.
     */
    public function invoice(Payment $payment)
    {
        // Invoice generation logic
        return view('plans::payments.invoice', compact('payment'));
    }

    /**
     * Generate payment receipt.
     */
    public function receipt(Payment $payment)
    {
        // Receipt generation logic
        return view('plans::payments.receipt', compact('payment'));
    }

    /**
     * Display admin payments index.
     */
    public function adminIndex()
    {
        $payments = Payment::with(['user', 'subscription.plan'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('plans::admin.payments.index', compact('payments'));
    }

    /**
     * Display admin payment view.
     */
    public function adminView(Payment $payment)
    {
        $payment->load(['user', 'subscription.plan']);
        return view('plans::admin.payments.view', compact('payment'));
    }

    /**
     * Process payment refund.
     */
    public function refund(Request $request, Payment $payment)
    {
        // Refund processing logic
        $payment->update(['status' => 'refunded']);
        
        return redirect()->back()->with('success', 'Payment refunded successfully.');
    }
}

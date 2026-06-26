<?php

namespace App\Modules\Plans\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Plans\Models\Payment;
use App\Modules\Plans\Services\StudentSubscriptionService;
use App\Modules\Plans\Services\SubscriptionPaymentHistoryService;
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
        if ($payment->user_id !== auth()->id() && ! auth()->user()?->isAdmin()) {
            abort(403);
        }

        $subscription = $payment->subscription()->with(['plan', 'user'])->firstOrFail();

        return view('plans::subscriptions.invoice', [
            'subscription' => $subscription,
            'payment' => $payment,
            'continueUrl' => null,
        ]);
    }

    /**
     * Generate payment receipt.
     */
    public function receipt(Payment $payment)
    {
        if ($payment->user_id !== auth()->id() && ! auth()->user()?->isAdmin()) {
            abort(403);
        }

        return view('plans::payments.receipt', compact('payment'));
    }

    /**
     * Admin ledger: money received from customers (subscriptions / membership).
     * Distinct from Staff Payments (money paid out to nurses/caregivers).
     */
    public function adminIndex(Request $request)
    {
        $studentPlanId = app(StudentSubscriptionService::class)->getStudentPlan()?->id;
        $audience = (string) $request->get('audience', 'all');

        $query = Payment::query()
            ->with([
                'user:id,name,email,role',
                'subscription.plan',
                'subscription.paymentVerifiedBy:id,name,role',
            ])
            ->where('status', 'completed')
            ->orderByDesc('paid_at')
            ->orderByDesc('id');

        if ($audience === 'student' && $studentPlanId) {
            $query->whereHas('subscription', fn ($q) => $q->where('plan_id', $studentPlanId));
        } elseif ($audience === 'patient' && $studentPlanId) {
            $query->whereHas('subscription', fn ($q) => $q->where('plan_id', '!=', $studentPlanId));
        }

        $search = trim((string) $request->get('q', ''));
        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('invoice_number', 'like', $like)
                    ->orWhere('receipt_number', 'like', $like)
                    ->orWhere('transaction_id', 'like', $like)
                    ->orWhereHas('user', function ($uq) use ($like) {
                        $uq->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    });
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('paid_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('paid_at', '<=', $request->date('to'));
        }

        $totalFilteredAmount = (float) (clone $query)->sum('amount');
        $payments = $query->paginate(20)->withQueryString();

        $revenueMetrics = app(SubscriptionPaymentHistoryService::class)->getSubscriptionRevenueMetrics();

        return view('plans::admin.payments.index', compact(
            'payments',
            'audience',
            'search',
            'totalFilteredAmount',
            'revenueMetrics',
            'studentPlanId'
        ));
    }

    /**
     * Display admin payment view.
     */
    public function adminView(Payment $payment)
    {
        $payment->load([
            'user',
            'subscription.plan',
            'subscription.paymentVerifiedBy:id,name,role',
            'subscription.coupon',
        ]);

        $historyService = app(SubscriptionPaymentHistoryService::class);
        $paymentRow = $payment->subscription
            ? $historyService->formatHistoryRow($payment->subscription)
            : null;

        return view('plans::admin.payments.view', compact('payment', 'paymentRow', 'historyService'));
    }

    /**
     * Process payment refund.
     */
    public function refund(Request $request, Payment $payment)
    {
        if (! $payment->canBeRefunded()) {
            return redirect()->back()->with('error', 'This payment cannot be marked as refunded.');
        }

        $payment->update(['status' => 'refunded']);

        SubscriptionPaymentHistoryService::bustAdminDashboardCache();

        return redirect()->back()->with('success', 'Payment marked as refunded in CRM. Process the actual refund in Razorpay or your bank if applicable.');
    }
}

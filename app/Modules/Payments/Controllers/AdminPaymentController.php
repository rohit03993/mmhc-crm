<?php

namespace App\Modules\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Incentives\Models\IncentiveLedger;
use App\Modules\Payments\Models\StaffPayment;
use App\Modules\Payments\Services\RazorpayXService;
use App\Modules\Payments\Services\StaffPayoutService;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Services\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminPaymentController extends Controller
{
    const MINIMUM_WITHDRAWAL = 500;

    public function __construct(
        private StaffPayoutService $staffPayoutService,
        private RazorpayXService $razorpayXService
    ) {}

    /**
     * RazorpayX staff payout is opt-in only (manual payouts are the default policy).
     */
    protected function staffPayoutRazorpayXAllowed(): bool
    {
        if (! (bool) config('payments.staff_payout.razorpayx_allowed', false)) {
            return false;
        }

        return $this->razorpayXService->isEnabled();
    }

    /**
     * Show admin payment management dashboard
     */
    public function index(Request $request)
    {
        $filterType = $request->get('type', 'all');
        $pendingPage = max((int) $request->get('page', 1), 1);
        $pendingPerPage = 10;
        $staffPage = max((int) $request->get('staff_page', 1), 1);
        $staffPerPage = 10;

        // Get all staff (nurses and caregivers)
        $allStaffMembers = User::whereIn('role', ['nurse', 'caregiver'])
            ->where('is_active', true)
            ->get();

        $pendingPayments = [];
        $totalPending = 0;
        $totalServiceQueue = 0;
        $staffPaymentOverview = [];

        foreach ($allStaffMembers as $staff) {
            $payments = $this->calculatePendingPayments($staff);
            $serviceQueueAmount = ServiceRequest::where('assigned_staff_id', $staff->id)
                ->whereIn('status', ['assigned', 'in_progress', 'completed'])
                ->whereNotNull('total_staff_payout')
                ->where('total_staff_payout', '>', 0)
                ->where(function ($query) {
                    $query->where('staff_payment_processed', false)
                        ->orWhereNull('staff_payment_processed');
                })
                ->sum('total_staff_payout') ?? 0;

            $staffPaymentOverview[$staff->id] = [
                'payable_now_total' => (float) $payments['total'],
                'service_payable_now' => (float) $payments['service_request']['amount'],
                'service_queue_total' => (float) $serviceQueueAmount,
                'patient_reward' => (float) $payments['patient_reward']['amount'],
                'subscription_referral' => (float) $payments['subscription_referral']['amount'],
            ];
            $totalServiceQueue += (float) $serviceQueueAmount;

            if ($payments['total'] > 0) {
                $pendingPayments[] = [
                    'staff' => $staff,
                    'payments' => $payments,
                ];
                $totalPending += $payments['total'];
            }
        }

        // Sort by total pending amount (descending)
        usort($pendingPayments, function ($a, $b) {
            return $b['payments']['total'] <=> $a['payments']['total'];
        });

        // Filter by specific type if specified
        if ($filterType !== 'all') {
            $pendingPayments = array_filter($pendingPayments, function ($item) use ($filterType) {
                return isset($item['payments'][$filterType]) && $item['payments'][$filterType]['amount'] > 0;
            });
        }

        $pendingPaymentsCollection = collect(array_values($pendingPayments));
        $pendingPayments = new LengthAwarePaginator(
            $pendingPaymentsCollection->forPage($pendingPage, $pendingPerPage)->values(),
            $pendingPaymentsCollection->count(),
            $pendingPerPage,
            $pendingPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'page',
            ]
        );

        $staffMembers = new LengthAwarePaginator(
            $allStaffMembers->forPage($staffPage, $staffPerPage)->values(),
            $allStaffMembers->count(),
            $staffPerPage,
            $staffPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'staff_page',
            ]
        );

        $recentPayments = StaffPayment::with(['staff', 'admin'])
            ->orderBy('paid_at', 'desc')
            ->limit(10)
            ->get();

        $totalPaidOverall = StaffPayment::sum('amount');

        return view('payments::admin.index', compact(
            'pendingPayments',
            'totalPending',
            'filterType',
            'recentPayments',
            'totalPaidOverall',
            'staffMembers',
            'staffPaymentOverview',
            'totalServiceQueue'
        ));
    }

    /**
     * Show payment form for a specific staff member
     */
    public function showPaymentForm($staffId, Request $request)
    {
        $staff = User::findOrFail($staffId);
        $allowedTypes = ['service_request', 'patient_reward', 'staff_referral', 'subscription_referral'];

        $pendingPayments = $this->calculatePendingPayments($staff);
        $paymentType = $request->query('type');

        if ($paymentType === 'all' || ($paymentType !== null && $paymentType !== '' && ! in_array($paymentType, $allowedTypes, true))) {
            return redirect()->route('admin.payments.index')
                ->with('error', 'Please select a valid payment category.');
        }

        if ($paymentType === null || $paymentType === '') {
            $paymentType = null;
            foreach ($allowedTypes as $t) {
                if ((float) ($pendingPayments[$t]['amount'] ?? 0) >= 0.01) {
                    $paymentType = $t;
                    break;
                }
            }
            $paymentType ??= 'service_request';
        }

        $paymentDetails = $this->getPaymentDetails($staff, $paymentType);
        $selectedTypePending = (float) ($pendingPayments[$paymentType]['amount'] ?? 0);
        $canAutoSettle = $selectedTypePending > 0;
        $razorpayXPayoutAllowed = $this->staffPayoutRazorpayXAllowed();
        $manualPayoutEnabled = (bool) config('payments.staff_payout.manual_enabled', true);

        // Ensure paymentDetails is always a collection
        if (! $paymentDetails) {
            $paymentDetails = collect();
        }

        return view('payments::admin.payment-form', compact(
            'staff',
            'pendingPayments',
            'paymentType',
            'paymentDetails',
            'selectedTypePending',
            'canAutoSettle',
            'allowedTypes',
            'razorpayXPayoutAllowed',
            'manualPayoutEnabled'
        ));
    }

    /**
     * Show payment history for admin
     */
    public function history(Request $request)
    {
        $filterType = $request->get('type', 'all');
        $filterStaff = $request->get('staff', 'all');

        $query = StaffPayment::with(['staff', 'admin'])
            ->orderBy('paid_at', 'desc');

        // Filter by payment type
        if ($filterType !== 'all') {
            $query->where('payment_type', $filterType);
        }

        // Filter by staff
        if ($filterStaff !== 'all') {
            $query->where('staff_id', $filterStaff);
        }

        $payments = $query->paginate(10);

        // Get all staff for filter dropdown
        $allStaff = User::whereIn('role', ['nurse', 'caregiver'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Calculate totals
        $totalPaid = StaffPayment::query()
            ->when($filterType !== 'all', function ($q) use ($filterType) {
                return $q->where('payment_type', $filterType);
            })
            ->when($filterStaff !== 'all', function ($q) use ($filterStaff) {
                return $q->where('staff_id', $filterStaff);
            })
            ->sum('amount');

        return view('payments::admin.history', compact('payments', 'filterType', 'filterStaff', 'allStaff', 'totalPaid'));
    }

    /**
     * Process payment submission
     */
    public function processPayment(Request $request, $staffId)
    {
        $request->validate([
            'payment_type' => 'required|in:service_request,patient_reward,staff_referral,subscription_referral',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|in:manual,razorpayx',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'payment_screenshot' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $staff = User::findOrFail($staffId);
        if (! $this->staffPayoutService->staffMayAccumulatePayouts($staff)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'This staff member has not verified their account mobile (SMS OTP). Payouts cannot be processed until mobile is verified in Profile.');
        }

        $admin = Auth::user();
        $requestedAmount = (float) $request->amount;
        $razorpayXPayoutAllowed = $this->staffPayoutRazorpayXAllowed();
        $paymentMode = $request->input('payment_mode', 'manual');
        if (! $razorpayXPayoutAllowed) {
            $paymentMode = 'manual';
        }
        $manualPayoutEnabled = (bool) config('payments.staff_payout.manual_enabled', true);
        $pendingPayments = $this->calculatePendingPayments($staff);
        $maxPayableForType = (float) ($pendingPayments[$request->payment_type]['amount'] ?? 0);

        if ($requestedAmount - $maxPayableForType > 0.0001) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Entered amount exceeds payable pending amount for this category.');
        }

        $usingRazorpayX = $paymentMode === 'razorpayx';
        if ($usingRazorpayX && ! $razorpayXPayoutAllowed) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Automatic Razorpay payouts are disabled. Record the payment manually with transaction ID and screenshot.');
        }

        if ($usingRazorpayX && ! $this->razorpayXService->isEnabled()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'RazorpayX payout is not enabled/configured. Use manual payout mode or configure RazorpayX.');
        }

        if ($usingRazorpayX && empty($staff->upi_id)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Staff UPI ID is missing. Ask staff/admin to add UPI in payment settings first.');
        }

        if (! $usingRazorpayX && ! $manualPayoutEnabled) {
            $request->validate([
                'payment_mode' => 'in:razorpayx',
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Manual payout is disabled. Please configure RazorpayX and staff UPI details.');
        }

        // Handle screenshot upload (manual mode)
        $screenshotPath = null;
        if (! $usingRazorpayX) {
            $request->validate([
                'transaction_id' => 'required|string|max:255',
                'payment_screenshot' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        }
        if (! $usingRazorpayX && $request->hasFile('payment_screenshot')) {
            $screenshotPath = $request->file('payment_screenshot')->store('payment-screenshots', 'public');
        }

        try {
            DB::transaction(function () use ($staff, $admin, $request, $requestedAmount, $screenshotPath, $usingRazorpayX, $paymentMode) {
                $gatewayPayload = null;
                $gatewayStatus = null;
                $gatewayReferenceId = null;
                $transactionId = $request->transaction_id;
                $provider = $usingRazorpayX ? 'razorpayx' : 'manual';

                if ($usingRazorpayX) {
                    $gatewayReferenceId = 'staffpay_'.$staff->id.'_'.now()->timestamp;
                    $gatewayPayload = $this->razorpayXService->createUpiPayout([
                        'staff_name' => $staff->name,
                        'staff_phone' => $staff->phone,
                        'staff_email' => $staff->email,
                        'staff_upi' => $staff->upi_id,
                        'amount' => $requestedAmount,
                        'reference_id' => $gatewayReferenceId,
                        'narration' => ucfirst(str_replace('_', ' ', $request->payment_type)).' payout',
                        'notes' => [
                            'staff_id' => (string) $staff->id,
                            'admin_id' => (string) $admin->id,
                        ],
                    ]);
                    $gatewayStatus = $gatewayPayload['status'] ?? 'created';
                    $transactionId = $gatewayPayload['utr'] ?? ($gatewayPayload['id'] ?? null);
                }

                $record = StaffPayment::create([
                    'staff_id' => $staff->id,
                    'admin_id' => $admin->id,
                    'payment_type' => $request->payment_type,
                    'amount' => $requestedAmount,
                    'payment_provider' => $provider,
                    'payment_mode' => $paymentMode,
                    'gateway_status' => $gatewayStatus,
                    'gateway_reference_id' => $gatewayReferenceId,
                    'gateway_payload' => $gatewayPayload,
                    'beneficiary_upi' => $staff->upi_id,
                    'transaction_id' => $transactionId,
                    'notes' => $request->notes,
                    'payment_screenshot' => $screenshotPath,
                    'paid_at' => now(),
                ]);

                if (! $usingRazorpayX || in_array($gatewayStatus, ['processed', 'processed_with_warning'], true)) {
                    $this->markAsPaid($staff, $request->payment_type, $requestedAmount, $record->id);
                }
            });
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Payout failed: '.$e->getMessage().($manualPayoutEnabled ? ' You can switch to manual mode and record payout proof.' : ''));
        }

        return redirect()->route('admin.payments.index')
            ->with('success', $usingRazorpayX
                ? "Razorpay payout initiated for {$staff->name}. Staff can track status in payment history."
                : "Payment of ₹{$request->amount} processed successfully for {$staff->name}.");
    }

    /**
     * Admin can update staff UPI to enable payout.
     */
    public function updateStaffUpi(Request $request, $staffId)
    {
        $request->validate([
            'upi_id' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z0-9.\-]{2,128}$/'],
        ], [
            'upi_id.required' => 'UPI ID is required.',
            'upi_id.regex' => 'Please enter a valid UPI ID format (example@bank or example@icici).',
        ]);

        $staff = User::whereIn('role', ['nurse', 'caregiver'])->findOrFail($staffId);
        $staff->upi_id = trim((string) $request->upi_id);
        $staff->save();

        return redirect()->back()->with('success', 'Staff UPI ID updated successfully.');
    }

    /**
     * Calculate pending payments for a staff member
     */
    private function calculatePendingPayments(User $staff)
    {
        $payments = $this->staffPayoutService->calculatePendingPayments($staff);
        $payments['patient_reward']['meets_threshold'] = $payments['patient_reward']['amount'] >= self::MINIMUM_WITHDRAWAL;

        return $payments;
    }

    /**
     * Get detailed payment information for a specific type
     */
    private function getPaymentDetails(User $staff, $paymentType)
    {
        switch ($paymentType) {
            case 'service_request':
                return $this->staffPayoutService
                    ->pendingServiceRequestQuery($staff->id)
                    ->with(['patient', 'serviceType'])
                    ->get();

            case 'patient_reward':
                // Admin can see all unpaid rewards, regardless of threshold
                return $this->staffPayoutService->pendingPatientRewardQuery($staff->id)->get();

            case 'staff_referral':
                $ledgers = $this->staffPayoutService
                    ->pendingStaffReferralQuery($staff->id)
                    ->get();
                $legacy = $this->staffPayoutService
                    ->pendingLegacyStaffReferralQuery($staff->id)
                    ->with(['referred'])
                    ->get();

                return $ledgers->values()->merge($legacy->values());

            case 'subscription_referral':
                $ledgers = $this->staffPayoutService
                    ->pendingSubscriptionReferralQuery($staff->id)
                    ->with('sourceSubscription.plan')
                    ->get();
                $ledgerSubIds = $ledgers->pluck('source_id');
                $legacyQ = Subscription::query()
                    ->where('referrer_id', $staff->id)
                    ->where('status', 'active')
                    ->where(function ($q) {
                        $q->where('referral_payment_processed', false)
                            ->orWhereNull('referral_payment_processed');
                    })
                    ->where(function ($q) use ($ledgerSubIds) {
                        if ($ledgerSubIds->isNotEmpty()) {
                            $q->whereNotIn('id', $ledgerSubIds);
                        }
                    });
                $legacy = $legacyQ->with('plan', 'user')->get();

                return $ledgers->values()->merge($legacy->values());

            default:
                return collect();
        }
    }

    /**
     * Mark related records as paid
     */
    private function markAsPaid(User $staff, $paymentType, $amount, ?int $staffPaymentId = null)
    {
        $remaining = (float) $amount;
        $epsilon = 0.0001;

        switch ($paymentType) {
            case 'service_request':
                $services = $this->staffPayoutService
                    ->pendingServiceRequestQuery($staff->id)
                    ->lockForUpdate()
                    ->orderBy('admin_approved_at')
                    ->orderBy('id')
                    ->get();

                foreach ($services as $service) {
                    $payout = (float) ($service->total_staff_payout ?? 0);
                    if ($payout <= 0 || $remaining + $epsilon < $payout) {
                        continue;
                    }
                    $service->update([
                        'staff_payment_processed' => true,
                        'staff_payment_processed_at' => now(),
                    ]);
                    IncentiveLedger::query()
                        ->where('source_type', IncentiveLedger::SOURCE_SERVICE_REQUEST)
                        ->where('source_id', $service->id)
                        ->update([
                            'payment_settled' => true,
                            'settled_at' => now(),
                            'staff_payment_id' => $staffPaymentId,
                        ]);
                    $remaining -= $payout;
                }
                break;

            case 'patient_reward':
                $rewards = $this->staffPayoutService
                    ->pendingPatientRewardQuery($staff->id)
                    ->lockForUpdate()
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->get();

                foreach ($rewards as $reward) {
                    $rewardAmount = (float) ($reward->reward_amount ?? 0);
                    if ($rewardAmount <= 0 || $remaining + $epsilon < $rewardAmount) {
                        continue;
                    }
                    $reward->update([
                        'payment_processed' => true,
                        'payment_processed_at' => now(),
                    ]);
                    $remaining -= $rewardAmount;
                }
                break;

            case 'staff_referral':
                $ledgers = $this->staffPayoutService
                    ->pendingStaffReferralQuery($staff->id)
                    ->lockForUpdate()
                    ->orderBy('id')
                    ->get();

                foreach ($ledgers as $ledger) {
                    $rewardAmount = (float) ($ledger->final_amount ?? 0);
                    if ($rewardAmount <= 0 || $remaining + $epsilon < $rewardAmount) {
                        continue;
                    }
                    $ledger->update([
                        'payment_settled' => true,
                        'settled_at' => now(),
                        'staff_payment_id' => $staffPaymentId,
                    ]);
                    if ($ledger->source_id) {
                        Referral::query()->where('id', $ledger->source_id)->update([
                            'payment_processed' => true,
                            'payment_processed_at' => now(),
                        ]);
                    }
                    $remaining -= $rewardAmount;
                }

                $legacyReferrals = $this->staffPayoutService
                    ->pendingLegacyStaffReferralQuery($staff->id)
                    ->lockForUpdate()
                    ->orderBy('completed_at')
                    ->orderBy('id')
                    ->get();

                foreach ($legacyReferrals as $referral) {
                    $rewardAmount = (float) ($referral->reward_amount ?? 0);
                    if ($rewardAmount <= 0 || $remaining + $epsilon < $rewardAmount) {
                        continue;
                    }
                    $referral->update([
                        'payment_processed' => true,
                        'payment_processed_at' => now(),
                    ]);
                    $remaining -= $rewardAmount;
                }
                break;

            case 'subscription_referral':
                $ledgers = $this->staffPayoutService
                    ->pendingSubscriptionReferralQuery($staff->id)
                    ->lockForUpdate()
                    ->orderBy('id')
                    ->get();

                foreach ($ledgers as $ledger) {
                    $commissionAmount = (float) ($ledger->final_amount ?? 0);
                    if ($commissionAmount <= 0 || $remaining + $epsilon < $commissionAmount) {
                        continue;
                    }
                    $ledger->update([
                        'payment_settled' => true,
                        'settled_at' => now(),
                        'staff_payment_id' => $staffPaymentId,
                    ]);
                    if ($ledger->source_id) {
                        Subscription::query()->where('id', $ledger->source_id)->update([
                            'referral_payment_processed' => true,
                            'referral_payment_processed_at' => now(),
                        ]);
                    }
                    $remaining -= $commissionAmount;
                }

                $ledgerSubIds = IncentiveLedger::query()
                    ->where('staff_id', $staff->id)
                    ->where('source_type', IncentiveLedger::SOURCE_SUBSCRIPTION_SALE)
                    ->pluck('source_id');
                $legacy = Subscription::query()
                    ->where('referrer_id', $staff->id)
                    ->where('status', 'active')
                    ->where(function ($q) {
                        $q->where('referral_payment_processed', false)
                            ->orWhereNull('referral_payment_processed');
                    })
                    ->lockForUpdate()
                    ->orderBy('created_at');
                if ($ledgerSubIds->isNotEmpty()) {
                    $legacy->whereNotIn('id', $ledgerSubIds);
                }
                foreach ($legacy->get() as $subscription) {
                    $commissionAmount = (float) ($subscription->referral_commission_amount ?? 0);
                    if ($commissionAmount <= 0 || $remaining + $epsilon < $commissionAmount) {
                        continue;
                    }
                    $subscription->update([
                        'referral_payment_processed' => true,
                        'referral_payment_processed_at' => now(),
                    ]);
                    $remaining -= $commissionAmount;
                }
                break;
        }
    }
}

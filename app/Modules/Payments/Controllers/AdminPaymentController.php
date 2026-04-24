<?php

namespace App\Modules\Payments\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Core\User;
use App\Modules\Payments\Models\StaffPayment;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Rewards\Models\CaregiverReward;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Payments\Services\StaffPayoutService;

class AdminPaymentController extends Controller
{
    const MINIMUM_WITHDRAWAL = 500;
    
    public function __construct(
        private StaffPayoutService $staffPayoutService
    ) {}

    /**
     * Show admin payment management dashboard
     */
    public function index(Request $request)
    {
        $filterType = $request->get('type', 'all');
        
        // Get all staff (nurses and caregivers)
        $staffMembers = User::whereIn('role', ['nurse', 'caregiver'])
            ->where('is_active', true)
            ->get();

        $pendingPayments = [];
        $totalPending = 0;
        $totalServiceQueue = 0;
        $staffPaymentOverview = [];

        foreach ($staffMembers as $staff) {
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
        usort($pendingPayments, function($a, $b) {
            return $b['payments']['total'] <=> $a['payments']['total'];
        });

        // Filter by type if specified (staff_referral excluded – points only)
        if ($filterType !== 'all' && $filterType !== 'staff_referral') {
            $pendingPayments = array_filter($pendingPayments, function($item) use ($filterType) {
                return isset($item['payments'][$filterType]) && $item['payments'][$filterType]['amount'] > 0;
            });
        }
        if ($filterType === 'staff_referral') {
            $pendingPayments = [];
        }

        $recentPayments = StaffPayment::with(['staff', 'admin'])
            ->when($filterType !== 'all', function ($query) use ($filterType) {
                return $query->where('payment_type', $filterType);
            })
            ->orderBy('paid_at', 'desc')
            ->limit(10)
            ->get();

        $totalPaidOverall = StaffPayment::when($filterType !== 'all', function ($query) use ($filterType) {
            return $query->where('payment_type', $filterType);
        })->sum('amount');

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
        $paymentType = $request->get('type', 'all');
        
        // Don't allow 'all' type; staff_referral is points-only and not paid out
        if ($paymentType === 'staff_referral') {
            return redirect()->route('admin.payments.index')
                ->with('info', 'Staff referrals are points-only and are not paid out.');
        }
        $allowedTypes = ['service_request', 'patient_reward', 'subscription_referral'];
        if ($paymentType === 'all' || !in_array($paymentType, $allowedTypes)) {
            return redirect()->route('admin.payments.index')
                ->with('error', 'Please select a specific payment category.');
        }
        
        $pendingPayments = $this->calculatePendingPayments($staff);
        $paymentDetails = $this->getPaymentDetails($staff, $paymentType);
        $selectedTypePending = (float) ($pendingPayments[$paymentType]['amount'] ?? 0);
        $canAutoSettle = $selectedTypePending > 0;
        
        // Ensure paymentDetails is always a collection
        if (!$paymentDetails) {
            $paymentDetails = collect();
        }

        return view('payments::admin.payment-form', compact(
            'staff',
            'pendingPayments',
            'paymentType',
            'paymentDetails',
            'selectedTypePending',
            'canAutoSettle'
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
        
        $payments = $query->paginate(20);
        
        // Get all staff for filter dropdown
        $allStaff = User::whereIn('role', ['nurse', 'caregiver'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Calculate totals
        $totalPaid = StaffPayment::query()
            ->when($filterType !== 'all', function($q) use ($filterType) {
                return $q->where('payment_type', $filterType);
            })
            ->when($filterStaff !== 'all', function($q) use ($filterStaff) {
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
            'payment_type' => 'required|in:service_request,patient_reward,subscription_referral',
            'amount' => 'required|numeric|min:0.01',
            'transaction_id' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'payment_screenshot' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'manual_payment' => 'nullable|boolean',
        ], [
            'transaction_id.required' => 'Transaction ID is required. Please enter the transaction ID from your payment app.',
            'payment_screenshot.required' => 'Payment screenshot is required. Please upload the payment confirmation screenshot.',
        ]);

        $staff = User::findOrFail($staffId);
        $admin = Auth::user();
        $requestedAmount = (float) $request->amount;
        $isManualPayment = (bool) $request->boolean('manual_payment');
        $pendingPayments = $this->calculatePendingPayments($staff);
        $maxPayableForType = (float) ($pendingPayments[$request->payment_type]['amount'] ?? 0);

        if (!$isManualPayment && $requestedAmount - $maxPayableForType > 0.0001) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Entered amount exceeds payable pending amount for this category.');
        }

        // Handle screenshot upload
        $screenshotPath = null;
        if ($request->hasFile('payment_screenshot')) {
            $screenshotPath = $request->file('payment_screenshot')->store('payment-screenshots', 'public');
        }

        DB::transaction(function () use ($staff, $admin, $request, $requestedAmount, $screenshotPath) {
            StaffPayment::create([
                'staff_id' => $staff->id,
                'admin_id' => $admin->id,
                'payment_type' => $request->payment_type,
                'amount' => $requestedAmount,
                'transaction_id' => $request->transaction_id,
                'notes' => $request->notes,
                'payment_screenshot' => $screenshotPath,
                'paid_at' => now(),
            ]);

            // Mark related records only for auto-settlement flow.
            if (!$isManualPayment) {
                $this->markAsPaid($staff, $request->payment_type, $requestedAmount);
            }
        });

        return redirect()->route('admin.payments.index')
            ->with('success', $isManualPayment
                ? "Manual payment of ₹{$request->amount} recorded successfully for {$staff->name}."
                : "Payment of ₹{$request->amount} processed successfully for {$staff->name}."
            );
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
                // Admin can see all unpaid referrals, regardless of threshold
                return Referral::where('referrer_id', $staff->id)
                    ->where('status', 'completed')
                    ->where('payment_processed', false)
                    ->with(['referred'])
                    ->get();

            case 'subscription_referral':
                return $this->staffPayoutService
                    ->pendingSubscriptionReferralQuery($staff->id)
                    ->with(['user', 'plan'])
                    ->get();

            default:
                return collect();
        }
    }

    /**
     * Mark related records as paid
     */
    private function markAsPaid(User $staff, $paymentType, $amount)
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
                $referrals = Referral::where('referrer_id', $staff->id)
                    ->where('status', 'completed')
                    ->where(function ($query) {
                        $query->where('payment_processed', false)
                            ->orWhereNull('payment_processed');
                    })
                    ->lockForUpdate()
                    ->orderBy('completed_at')
                    ->orderBy('id')
                    ->get();

                foreach ($referrals as $referral) {
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
                $subscriptions = $this->staffPayoutService
                    ->pendingSubscriptionReferralQuery($staff->id)
                    ->lockForUpdate()
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->get();

                foreach ($subscriptions as $subscription) {
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


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
use App\Modules\Rewards\Services\RewardService;

class AdminPaymentController extends Controller
{
    const MINIMUM_WITHDRAWAL = 500;

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

        foreach ($staffMembers as $staff) {
            $payments = $this->calculatePendingPayments($staff);
            
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

        return view('payments::admin.index', compact('pendingPayments', 'totalPending', 'filterType'));
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
        
        // Ensure paymentDetails is always a collection
        if (!$paymentDetails) {
            $paymentDetails = collect();
        }

        return view('payments::admin.payment-form', compact('staff', 'pendingPayments', 'paymentType', 'paymentDetails'));
    }
    
    /**
     * Show payment history for admin
     */
    public function history(Request $request)
    {
        $admin = Auth::user();
        $filterType = $request->get('type', 'all');
        $filterStaff = $request->get('staff', 'all');
        
        $query = StaffPayment::with(['staff', 'admin'])
            ->where('admin_id', $admin->id)
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
        $totalPaid = StaffPayment::where('admin_id', $admin->id)
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
        ], [
            'transaction_id.required' => 'Transaction ID is required. Please enter the transaction ID from your payment app.',
            'payment_screenshot.required' => 'Payment screenshot is required. Please upload the payment confirmation screenshot.',
        ]);

        $staff = User::findOrFail($staffId);
        $admin = Auth::user();

        // Handle screenshot upload
        $screenshotPath = null;
        if ($request->hasFile('payment_screenshot')) {
            $screenshotPath = $request->file('payment_screenshot')->store('payment-screenshots', 'public');
        }

        // Create payment record
        $payment = StaffPayment::create([
            'staff_id' => $staff->id,
            'admin_id' => $admin->id,
            'payment_type' => $request->payment_type,
            'amount' => $request->amount,
            'transaction_id' => $request->transaction_id,
            'notes' => $request->notes,
            'payment_screenshot' => $screenshotPath,
            'paid_at' => now(),
        ]);

        // Mark related records as paid
        $this->markAsPaid($staff, $request->payment_type, $request->amount);

        return redirect()->route('admin.payments.index')
            ->with('success', "Payment of ₹{$request->amount} processed successfully for {$staff->name}.");
    }

    /**
     * Calculate pending payments for a staff member
     */
    private function calculatePendingPayments(User $staff)
    {
        $rewardService = app(RewardService::class);

        // 1. Service Request Earnings (completed and approved, but not paid)
        $serviceEarnings = ServiceRequest::where('assigned_staff_id', $staff->id)
            ->where('status', 'completed')
            ->whereNotNull('admin_approved_at')
            ->where('staff_payment_processed', false)
            ->sum('total_staff_payout') ?? 0;

        // 2. Patient Reward Earnings (show all, not just >= ₹500 for admin)
        $totalRewardPoints = $staff->reward_points ?? 0;
        $totalRewardAmount = $rewardService->calculateRewardAmount($totalRewardPoints);
        $patientRewardEarnings = CaregiverReward::where('user_id', $staff->id)
            ->where('payment_processed', false)
            ->sum('reward_amount') ?? 0;

        // 3. Staff Referral – points only, not paid out (excluded from payments)
        $staffReferralEarnings = 0;

        // 4. Subscription Referral Earnings
        $subscriptionReferralEarnings = Subscription::where('referrer_id', $staff->id)
            ->where('status', 'active')
            ->where('referral_payment_processed', false)
            ->sum('referral_commission_amount') ?? 0;

        return [
            'service_request' => [
                'amount' => $serviceEarnings,
                'count' => ServiceRequest::where('assigned_staff_id', $staff->id)
                    ->where('status', 'completed')
                    ->whereNotNull('admin_approved_at')
                    ->where('staff_payment_processed', false)
                    ->count(),
            ],
            'patient_reward' => [
                'amount' => $patientRewardEarnings,
                'count' => $patientRewardEarnings > 0 ? CaregiverReward::where('user_id', $staff->id)
                    ->where('payment_processed', false)
                    ->count() : 0,
                'meets_threshold' => $patientRewardEarnings >= self::MINIMUM_WITHDRAWAL,
            ],
            'staff_referral' => [
                'amount' => 0,
                'count' => 0,
                'meets_threshold' => false,
            ],
            'subscription_referral' => [
                'amount' => $subscriptionReferralEarnings,
                'count' => Subscription::where('referrer_id', $staff->id)
                    ->where('status', 'active')
                    ->where('referral_payment_processed', false)
                    ->count(),
            ],
            'total' => $serviceEarnings + $patientRewardEarnings + $staffReferralEarnings + $subscriptionReferralEarnings,
        ];
    }

    /**
     * Get detailed payment information for a specific type
     */
    private function getPaymentDetails(User $staff, $paymentType)
    {
        switch ($paymentType) {
            case 'service_request':
                return ServiceRequest::where('assigned_staff_id', $staff->id)
                    ->where('status', 'completed')
                    ->whereNotNull('admin_approved_at')
                    ->where('staff_payment_processed', false)
                    ->with(['patient', 'serviceType'])
                    ->get();

            case 'patient_reward':
                // Admin can see all unpaid rewards, regardless of threshold
                return CaregiverReward::where('user_id', $staff->id)
                    ->where('payment_processed', false)
                    ->get();

            case 'staff_referral':
                // Admin can see all unpaid referrals, regardless of threshold
                return Referral::where('referrer_id', $staff->id)
                    ->where('status', 'completed')
                    ->where('payment_processed', false)
                    ->with(['referred'])
                    ->get();

            case 'subscription_referral':
                return Subscription::where('referrer_id', $staff->id)
                    ->where('status', 'active')
                    ->where('referral_payment_processed', false)
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
        switch ($paymentType) {
            case 'service_request':
                // Mark all unpaid completed services as paid (up to the amount)
                $services = ServiceRequest::where('assigned_staff_id', $staff->id)
                    ->where('status', 'completed')
                    ->whereNotNull('admin_approved_at')
                    ->where('staff_payment_processed', false)
                    ->orderBy('admin_approved_at')
                    ->get();

                $remaining = $amount;
                foreach ($services as $service) {
                    if ($remaining <= 0) break;
                    $service->update([
                        'staff_payment_processed' => true,
                        'staff_payment_processed_at' => now(),
                    ]);
                    $remaining -= $service->total_staff_payout;
                }
                break;

            case 'patient_reward':
                $rewards = CaregiverReward::where('user_id', $staff->id)
                    ->where('payment_processed', false)
                    ->orderBy('created_at')
                    ->get();

                $remaining = $amount;
                foreach ($rewards as $reward) {
                    if ($remaining <= 0) break;
                    $reward->update([
                        'payment_processed' => true,
                        'payment_processed_at' => now(),
                    ]);
                    $remaining -= $reward->reward_amount;
                }
                break;

            case 'staff_referral':
                $referrals = Referral::where('referrer_id', $staff->id)
                    ->where('status', 'completed')
                    ->where('payment_processed', false)
                    ->orderBy('completed_at')
                    ->get();

                $remaining = $amount;
                foreach ($referrals as $referral) {
                    if ($remaining <= 0) break;
                    $referral->update([
                        'payment_processed' => true,
                        'payment_processed_at' => now(),
                    ]);
                    $remaining -= $referral->reward_amount;
                }
                break;

            case 'subscription_referral':
                $subscriptions = Subscription::where('referrer_id', $staff->id)
                    ->where('status', 'active')
                    ->where('referral_payment_processed', false)
                    ->orderBy('created_at')
                    ->get();

                $remaining = $amount;
                foreach ($subscriptions as $subscription) {
                    if ($remaining <= 0) break;
                    $subscription->update([
                        'referral_payment_processed' => true,
                        'referral_payment_processed_at' => now(),
                    ]);
                    $remaining -= $subscription->referral_commission_amount;
                }
                break;
        }
    }
}


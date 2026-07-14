<?php

namespace App\Modules\Services\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Auth\Services\ScopedSmsOtpRedisService;
use App\Modules\Auth\Services\SmsOtpService;
use App\Modules\Incentives\Models\IncentiveLedger;
use App\Modules\Incentives\Services\IncentiveCalculatorService;
use App\Modules\Payments\Services\StaffPayoutService;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Referrals\Services\ReferralService;
use App\Modules\Rewards\Models\CaregiverReward;
use App\Modules\Rewards\Services\RewardService;
use App\Modules\Services\Models\DailyService;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Services\Services\StaffIncentiveDetailsDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StaffDashboardController extends Controller
{
    /**
     * Show staff dashboard with assigned services
     */
    public function index()
    {
        $user = Auth::user();

        // Get assigned services for this staff member (including pending_approval)
        // Hide unpaid pending_approval bookings until the patient pays (or amount is free).
        $assignedServices = ServiceRequest::where('assigned_staff_id', $user->id)
            ->whereIn('status', ['pending_approval', 'assigned', 'in_progress', 'completed'])
            ->where(function ($query) {
                $query->where('status', '!=', 'pending_approval')
                    ->orWhere(function ($q) {
                        $q->where('status', 'pending_approval')
                            ->where(function ($inner) {
                                $inner->where('payment_status', 'paid')
                                    ->orWhere('total_amount', '<=', 0);
                            });
                    });
            })
            ->with(['patient', 'serviceType', 'assignedStaff'])
            ->orderByRaw("CASE WHEN status = 'pending_approval' THEN 0 ELSE 1 END")
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        // Calculate missing payouts for display only (no write during dashboard reads).
        $calc = app(IncentiveCalculatorService::class);
        $subSvc = app(\App\Modules\Plans\Services\SubscriptionService::class);
        foreach ($assignedServices as $service) {
            if (! $service->relationLoaded('assignedStaff')) {
                $service->load('assignedStaff');
            }
            if (! $service->relationLoaded('serviceType')) {
                $service->load('serviceType');
            }
            if (! $service->total_staff_payout && $service->assignedStaff && $service->serviceType) {
                $service->load('patient');
                $hasSub = $service->patient && $subSvc->hasActiveSubscription($service->patient);
                $prov = $calc->estimateProvisionalServicePayout(
                    $service->assignedStaff,
                    $service->serviceType,
                    (int) $service->duration_days,
                    $hasSub
                );
                if ($prov > 0) {
                    $service->total_staff_payout = $prov;
                } else {
                    $dailyStaffPayout = $user->isNurse() ? $service->serviceType->nurse_payout : $service->serviceType->caregiver_payout;
                    $service->total_staff_payout = $service->duration_days * $dailyStaffPayout;
                }
            }
        }

        // Get statistics
        $allServices = ServiceRequest::where('assigned_staff_id', $user->id);

        $stats = [
            'total_assignments' => $allServices->count(),
            'active_assignments' => $allServices->whereIn('status', ['assigned', 'in_progress', 'pending_approval'])->count(),
            'completed_assignments' => $allServices->where('status', 'completed')->count(),
            'pending_assignments' => $allServices->where('status', 'assigned')->count(),
            'pending_booking_count' => $allServices->where('status', 'pending_approval')->count(),
        ];

        // ============================================
        // CALCULATE EARNINGS FROM 4 SOURCES
        // ============================================

        // 1. SERVICE REQUEST EARNINGS (from assigned services)
        $allServicesData = ServiceRequest::where('assigned_staff_id', $user->id)
            ->whereNotNull('total_staff_payout')
            ->get();

        $serviceRequestEarnings = [
            // Count as paid only after admin approval AND payout processing.
            'total_approved' => $allServicesData
                ->whereNotNull('admin_approved_at')
                ->filter(function ($service) {
                    return (bool) $service->staff_payment_processed;
                })
                ->sum('total_staff_payout'),
            'pending_approval' => $allServicesData->where('status', 'completed')->whereNull('admin_approved_at')->sum('total_staff_payout'),
            'approved_unpaid' => $allServicesData
                ->whereNotNull('admin_approved_at')
                ->filter(function ($service) {
                    return ! (bool) $service->staff_payment_processed;
                })
                ->sum('total_staff_payout'),
            'upcoming' => ServiceRequest::where('assigned_staff_id', $user->id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->whereNotNull('total_staff_payout')
                ->sum('total_staff_payout'),
            'this_month' => $allServicesData->whereNotNull('admin_approved_at')
                ->filter(function ($service) {
                    if (! $service->admin_approved_at || ! (bool) $service->staff_payment_processed) {
                        return false;
                    }
                    $approvedDate = $service->admin_approved_at;

                    return $approvedDate->month === now()->month && $approvedDate->year === now()->year;
                })
                ->sum('total_staff_payout'),
            'total_count' => $allServicesData
                ->whereNotNull('admin_approved_at')
                ->filter(function ($service) {
                    return (bool) $service->staff_payment_processed;
                })
                ->count(),
        ];

        // 2. PATIENT REWARD EARNINGS (from submitting patient details)
        $rewardService = app(RewardService::class);
        $payoutService = app(StaffPayoutService::class);
        $staffMobileVerified = $user->hasVerifiedPhone();
        $heldEarningsDueToUnverifiedMobile = $payoutService->calculateHeldDueToUnverifiedMobile($user);
        app(RewardService::class)->syncStaffRewardPoints($user);

        $verifiedRewardsBase = CaregiverReward::query()
            ->where('user_id', $user->id)
            ->verified();
        $totalPoints = (int) (clone $verifiedRewardsBase)->sum('reward_points');
        $patientRewardEarnedAmount = $rewardService->calculateRewardAmount($totalPoints);
        $patientRewardEarnings = [
            'total_points' => $totalPoints,
            'earned_amount' => $patientRewardEarnedAmount,
            'total_amount' => $staffMobileVerified ? $patientRewardEarnedAmount : 0.0,
            'total_submissions' => (clone $verifiedRewardsBase)->count(),
            'this_month' => (clone $verifiedRewardsBase)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('reward_amount'),
        ];

        // 3. STAFF REFERRAL (₹100 base + incentive logic; no experience tier usage)
        $referralService = app(ReferralService::class);
        $referralStats = $referralService->getReferralStats($user);
        $staffReferralBasePerRef = 100;
        $staffReferralCount = (int) $referralStats['completed_referrals'];
        $staffReferralAmount = (float) ($referralStats['total_reward_amount'] ?? 0);
        $staffReferralEarnings = [
            'total_referrals' => $staffReferralCount,
            'total_base_amount' => $staffReferralCount * $staffReferralBasePerRef,
            'earned_amount' => $staffReferralAmount,
            'total_amount' => $staffMobileVerified ? $staffReferralAmount : 0.0,
            'this_month_count' => \App\Modules\Referrals\Models\Referral::where('referrer_id', $user->id)
                ->referralMobileOtpVerified()
                ->where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->whereYear('completed_at', now()->year)
                ->count(),
            'this_month_amount' => 0, // set below
        ];
        $thisMonthReferralIds = \App\Modules\Referrals\Models\Referral::where('referrer_id', $user->id)
            ->referralMobileOtpVerified()
            ->where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->pluck('id');
        $thisMonthAmount = IncentiveLedger::query()
            ->where('staff_id', $user->id)
            ->where('source_type', IncentiveLedger::SOURCE_REFERRAL)
            ->whereIn('source_id', $thisMonthReferralIds)
            ->sum('final_amount');
        if ((float) $thisMonthAmount <= 0) {
            $thisMonthAmount = $staffReferralEarnings['this_month_count'] * $staffReferralBasePerRef;
        }
        $staffReferralEarnings['this_month_amount'] = $staffMobileVerified ? (float) $thisMonthAmount : 0.0;
        $staffReferralEarnings['this_month_earned'] = (float) $thisMonthAmount;

        // 4. SUBSCRIPTION REFERRAL EARNINGS (ledger-first, legacy fallback only where ledger row is missing)
        $subscriptionReferralsBaseQuery = Subscription::query()
            ->where('referrer_id', $user->id);
        $subscriptionTotalReferrals = (clone $subscriptionReferralsBaseQuery)->count();
        $subscriptionActiveReferrals = (clone $subscriptionReferralsBaseQuery)
            ->where('status', 'active')
            ->count();

        $subscriptionLedgerBaseQuery = IncentiveLedger::query()
            ->where('staff_id', $user->id)
            ->where('source_type', IncentiveLedger::SOURCE_SUBSCRIPTION_SALE);
        $subscriptionLedgerSourceIds = (clone $subscriptionLedgerBaseQuery)->pluck('source_id');
        $subscriptionLedgerTotalCommission = (float) (clone $subscriptionLedgerBaseQuery)->sum('final_amount');
        $subscriptionLedgerThisMonthCommission = (float) (clone $subscriptionLedgerBaseQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('final_amount');

        $legacySubscriptionCommissionBaseQuery = Subscription::query()
            ->where('referrer_id', $user->id)
            ->where('status', 'active');
        if ($subscriptionLedgerSourceIds->isNotEmpty()) {
            $legacySubscriptionCommissionBaseQuery->whereNotIn('id', $subscriptionLedgerSourceIds);
        }
        $legacySubscriptionTotalCommission = (float) (clone $legacySubscriptionCommissionBaseQuery)
            ->sum('referral_commission_amount');
        $legacySubscriptionThisMonthCommission = (float) (clone $legacySubscriptionCommissionBaseQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('referral_commission_amount');

        $subscriptionReferralEarnedCommission = $subscriptionLedgerTotalCommission + $legacySubscriptionTotalCommission;
        $subscriptionReferralEarnings = [
            'total_referrals' => $subscriptionTotalReferrals,
            'active_referrals' => $subscriptionActiveReferrals,
            'earned_commission' => $subscriptionReferralEarnedCommission,
            'total_commission' => $staffMobileVerified ? $subscriptionReferralEarnedCommission : 0.0,
            'this_month' => $staffMobileVerified
                ? ($subscriptionLedgerThisMonthCommission + $legacySubscriptionThisMonthCommission)
                : 0.0,
            'this_month_earned' => $subscriptionLedgerThisMonthCommission + $legacySubscriptionThisMonthCommission,
        ];

        // TOTAL OVERALL EARNINGS (payable only — held amounts excluded until mobile verified)
        $totalOverallEarnings =
            $serviceRequestEarnings['total_approved'] +
            $staffReferralEarnings['total_amount'] +
            $patientRewardEarnings['total_amount'] +
            $subscriptionReferralEarnings['total_commission'];
        $totalOverallEarnedAmount =
            $serviceRequestEarnings['total_approved'] +
            ($staffReferralEarnings['earned_amount'] ?? 0) +
            ($patientRewardEarnings['earned_amount'] ?? 0) +
            ($subscriptionReferralEarnings['earned_commission'] ?? 0);

        // Legacy earnings stats (for backward compatibility)
        $earningsStats = [
            'total_earnings' => $serviceRequestEarnings['total_approved'],
            'pending_earnings' => $serviceRequestEarnings['pending_approval'],
            'earnings_this_month' => $serviceRequestEarnings['this_month'],
            'earnings_last_month' => 0, // Can be calculated if needed
            'upcoming_earnings' => $serviceRequestEarnings['upcoming'],
        ];

        // Get recent data for display
        $recentRewards = CaregiverReward::where('user_id', $user->id)->latest()->limit(5)->get();
        $referralService = app(ReferralService::class);
        $referralLink = $referralService->getReferralLink($user);
        $recentReferrals = $referralService->getReferralHistory($user, 5);
        $subscriptionReferralLink = route('plans.index', ['ref' => $user->id]);

        $heldEarningsDueToUnverifiedMobile = $payoutService->calculateHeldDueToUnverifiedMobile($user);

        return view('services::staff.dashboard', compact(
            'assignedServices',
            'stats',
            'earningsStats',
            'serviceRequestEarnings',
            'patientRewardEarnings',
            'staffReferralEarnings',
            'subscriptionReferralEarnings',
            'totalOverallEarnings',
            'totalOverallEarnedAmount',
            'recentRewards',
            'referralLink',
            'referralStats',
            'recentReferrals',
            'subscriptionReferralLink',
            'heldEarningsDueToUnverifiedMobile',
            'staffMobileVerified'
        ));
    }

    /**
     * Show assigned service details
     */
    public function show(ServiceRequest $serviceRequest)
    {
        // Ensure this service is assigned to the current staff member
        if ($serviceRequest->assigned_staff_id !== Auth::id()) {
            abort(403, 'You are not assigned to this service.');
        }

        // Ensure relationships are loaded
        $serviceRequest->load(['assignedStaff', 'serviceType']);

        if (! $serviceRequest->total_staff_payout && $serviceRequest->assigned_staff_id) {
            $staff = $serviceRequest->assignedStaff;
            $serviceType = $serviceRequest->serviceType;
            if (! $staff) {
                abort(404, 'Assigned staff not found.');
            }
            if (! $serviceType) {
                abort(404, 'Service type not found.');
            }
            $serviceRequest->load('patient');
            $patient = $serviceRequest->patient;
            $subSvc = app(\App\Modules\Plans\Services\SubscriptionService::class);
            $hasSub = $patient && $subSvc->hasActiveSubscription($patient);
            $prov = app(IncentiveCalculatorService::class)->estimateProvisionalServicePayout(
                $staff,
                $serviceType,
                (int) $serviceRequest->duration_days,
                $hasSub
            );
            if ($prov > 0) {
                $totalStaffPayout = $prov;
            } else {
                $dailyStaffPayout = $staff->isNurse() ? $serviceType->nurse_payout : $serviceType->caregiver_payout;
                $totalStaffPayout = $serviceRequest->duration_days * $dailyStaffPayout;
            }
            $serviceRequest->update(['total_staff_payout' => $totalStaffPayout]);
            $serviceRequest->refresh();
        }

        $serviceRequest->load(['patient', 'serviceType', 'dailyServices']);

        return view('services::staff.service-details', compact('serviceRequest'));
    }

    /**
     * Start a service (change status from assigned to in_progress)
     */
    public function startService(Request $request, ServiceRequest $serviceRequest)
    {
        // Ensure this service is assigned to the current staff member
        if ($serviceRequest->assigned_staff_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this service.',
            ], 403);
        }

        $staff = Auth::user();
        if ($staff && $staff->staffMustVerifyMobileBeforeRewards()) {
            return response()->json([
                'success' => false,
                'message' => 'Verify your mobile number in Profile before starting services that earn payouts.',
            ], 422);
        }

        // CRITICAL FIX #5: Validate status transition using state machine
        if (! $serviceRequest->canTransitionTo('in_progress')) {
            $validStatuses = implode(', ', $serviceRequest->getValidNextStatuses());

            return response()->json([
                'success' => false,
                'message' => "Service cannot be started from current status '{$serviceRequest->status}'. Valid next statuses: {$validStatuses}",
            ], 400);
        }

        // Additional validation: Check if service start date is valid
        if ($serviceRequest->start_date > now()->startOfDay()) {
            return response()->json([
                'success' => false,
                'message' => 'Service cannot be started before the assigned start date: '.$serviceRequest->start_date->format('M d, Y'),
            ], 400);
        }

        // CRITICAL FIX #3: Wrap in transaction
        try {
            DB::beginTransaction();

            // Update service status
            $serviceRequest->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            // Update daily services status for today and future dates
            $today = now()->startOfDay();
            $serviceRequest->dailyServices()
                ->where('service_date', '>=', $today)
                ->where('status', 'scheduled')
                ->update(['status' => 'in_progress']);

            DB::commit();

            Log::info('Service started', [
                'service_request_id' => $serviceRequest->id,
                'staff_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service started successfully!',
                'service' => $serviceRequest->fresh(['serviceType', 'patient']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Service start failed: '.$e->getMessage(), [
                'service_request_id' => $serviceRequest->id,
                'staff_id' => Auth::id(),
                'error' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start service. Please try again.',
            ], 500);
        }
    }

    /**
     * Send service completion OTP to the patient via WhatsApp.
     */
    public function sendCompletionOtp(Request $request, ServiceRequest $serviceRequest)
    {
        $user = Auth::user();
        if ($user->hasPendingMobileContactVerification()) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your pending profile contact verification first.',
            ], 422);
        }

        if ($serviceRequest->assigned_staff_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this service.',
            ], 403);
        }

        $staff = Auth::user();
        if ($staff && $staff->staffMustVerifyMobileBeforeRewards()) {
            return response()->json([
                'success' => false,
                'message' => 'Verify your mobile number in Profile before sending completion OTP or earning payouts.',
            ], 422);
        }

        if (! $serviceRequest->canTransitionTo('completed')) {
            return response()->json([
                'success' => false,
                'message' => 'Service is not eligible for completion right now.',
            ], 400);
        }

        if (! $serviceRequest->isReadyForStaffCompletion()) {
            return response()->json([
                'success' => false,
                'message' => 'Service can be completed on or after '.$serviceRequest->end_date->format('M d, Y').'.',
            ], 400);
        }

        $serviceRequest->load('patient');
        if ($this->staffMayCompleteWithoutPatientOtp($staff, $serviceRequest)) {
            return response()->json([
                'success' => true,
                'message' => 'Patient mobile matches your verified account. Use Complete — no separate patient OTP needed.',
                'skip_patient_otp' => true,
            ]);
        }

        $patient = $serviceRequest->patient;
        if (! $patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient record not found for this service.',
            ], 404);
        }

        if ($serviceRequest->completion_otp_sent_at && $serviceRequest->completion_otp_sent_at->gt(now()->subMinutes(15))) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait 15 minutes before requesting OTP again.',
            ], 429);
        }

        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        $normalizedPhone = $this->normalizeIndianPhone((string) ($serviceRequest->patientContactPhone() ?? ''));
        if (! $normalizedPhone) {
            return response()->json([
                'success' => false,
                'message' => 'Patient mobile number is missing or invalid.',
            ], 422);
        }

        $smsResult = app(SmsOtpService::class)->sendCustomOtp($normalizedPhone, $otp, $patient->name);
        if (! ($smsResult['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $smsResult['message'] ?? 'Could not send OTP to patient mobile.',
            ], 422);
        }

        $scopedOtp = app(ScopedSmsOtpRedisService::class);
        $scopedOtp->store(
            ScopedSmsOtpRedisService::PURPOSE_SERVICE_COMPLETION,
            (int) $serviceRequest->id,
            $otp
        );
        $otpDigest = $scopedOtp->buildDigest(
            ScopedSmsOtpRedisService::PURPOSE_SERVICE_COMPLETION,
            (int) $serviceRequest->id,
            $otp
        );

        $channelLabel = app(SmsOtpService::class)->deliveryChannelLabel();

        $serviceRequest->update([
            'completion_otp_hash' => $otpDigest,
            'completion_otp_expires_at' => $expiresAt,
            'completion_otp_attempts' => 0,
            'completion_otp_channel' => strtolower($channelLabel),
            'completion_otp_sent_to' => $channelLabel.': '.$this->maskPhone($normalizedPhone),
            'completion_otp_sent_at' => now(),
            'completion_verified_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to the patient via '.$channelLabel.'. Ask them for the code to complete the visit.',
            'channel' => $serviceRequest->completion_otp_channel,
            'sent_to' => $serviceRequest->completion_otp_sent_to,
            'expires_at' => optional($serviceRequest->completion_otp_expires_at)->toISOString(),
        ]);
    }

    /**
     * Complete a service (change status from in_progress to completed)
     */
    public function completeService(Request $request, ServiceRequest $serviceRequest)
    {
        $user = Auth::user();
        if ($user->hasPendingMobileContactVerification()) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your pending profile contact verification first.',
            ], 422);
        }

        // Ensure this service is assigned to the current staff member
        if ($serviceRequest->assigned_staff_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this service.',
            ], 403);
        }

        $staff = Auth::user();
        if ($staff && $staff->staffMustVerifyMobileBeforeRewards()) {
            return response()->json([
                'success' => false,
                'message' => 'Verify your mobile number in Profile before completing services that earn payouts.',
            ], 422);
        }

        // CRITICAL FIX #5: Validate status transition using state machine
        if (! $serviceRequest->canTransitionTo('completed')) {
            $validStatuses = implode(', ', $serviceRequest->getValidNextStatuses());

            return response()->json([
                'success' => false,
                'message' => "Service cannot be completed from current status '{$serviceRequest->status}'. Valid next statuses: {$validStatuses}",
            ], 400);
        }

        // Additional validation: Check if service end date is valid
        if (! $serviceRequest->isReadyForStaffCompletion()) {
            return response()->json([
                'success' => false,
                'message' => 'Service can be completed on or after the assigned end date: '.$serviceRequest->end_date->format('M d, Y'),
            ], 400);
        }

        $skipPatientOtp = $serviceRequest->staffMayCompleteWithoutPatientOtp($staff);

        $validator = Validator::make($request->all(), [
            'otp_code' => $skipPatientOtp ? ['nullable', 'digits:6'] : ['required', 'digits:6'],
        ], [
            'otp_code.required' => 'Patient completion OTP is required.',
            'otp_code.digits' => 'Completion OTP must be 6 digits.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('otp_code'),
            ], 422);
        }

        if (! $skipPatientOtp) {
        if (! $serviceRequest->completion_otp_expires_at) {
            return response()->json([
                'success' => false,
                'message' => 'Please request OTP first.',
            ], 422);
        }
        if (! $serviceRequest->completion_otp_sent_at) {
            return response()->json([
                'success' => false,
                'message' => 'Please request OTP first.',
            ], 422);
        }
        if (now()->greaterThan($serviceRequest->completion_otp_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired. Please request a new OTP.',
            ], 422);
        }
        if ((int) $serviceRequest->completion_otp_attempts >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum OTP attempts reached. Request a new OTP.',
            ], 422);
        }

        $otpCode = (string) $request->input('otp_code');
        $otpValid = app(ScopedSmsOtpRedisService::class)->verifyWithDbFallback(
            ScopedSmsOtpRedisService::PURPOSE_SERVICE_COMPLETION,
            (int) $serviceRequest->id,
            $otpCode,
            (string) ($serviceRequest->completion_otp_hash ?? '')
        );

        if (! $otpValid) {
            $serviceRequest->increment('completion_otp_attempts');
            $remainingAttempts = max(0, 3 - (int) $serviceRequest->fresh()->completion_otp_attempts);

            return response()->json([
                'success' => false,
                'message' => $remainingAttempts > 0
                    ? "Invalid OTP. {$remainingAttempts} attempt(s) left."
                    : 'Invalid OTP. No attempts left. Request a new OTP.',
            ], 422);
        }
        }

        // CRITICAL FIX #3: Wrap in transaction
        try {
            DB::beginTransaction();

            // Ensure relationships are loaded
            $serviceRequest->load(['assignedStaff', 'serviceType']);

            // Ensure payout is calculated before completing
            if (! $serviceRequest->total_staff_payout) {
                $staff = $serviceRequest->assignedStaff;
                $serviceType = $serviceRequest->serviceType;

                // Null checks before accessing properties
                if (! $staff) {
                    throw new \Exception("Assigned staff not found for service request #{$serviceRequest->id}");
                }
                if (! $serviceType) {
                    throw new \Exception("Service type not found for service request #{$serviceRequest->id}");
                }

                $subSvc = app(\App\Modules\Plans\Services\SubscriptionService::class);
                $serviceRequest->load('patient');
                $patient = $serviceRequest->patient;
                $hasSub = $patient && $subSvc->hasActiveSubscription($patient);
                $prov = app(IncentiveCalculatorService::class)->estimateProvisionalServicePayout(
                    $staff,
                    $serviceType,
                    (int) $serviceRequest->duration_days,
                    $hasSub
                );
                if ($prov > 0) {
                    $totalStaffPayout = $prov;
                } else {
                    $dailyStaffPayout = $staff->isNurse() ? $serviceType->nurse_payout : $serviceType->caregiver_payout;
                    $totalStaffPayout = $serviceRequest->duration_days * $dailyStaffPayout;
                }
                $serviceRequest->total_staff_payout = $totalStaffPayout;
            }

            // Update service status (admin approval still pending)
            $serviceRequest->update([
                'status' => 'completed',
                'completed_at' => now(),
                'total_staff_payout' => $serviceRequest->total_staff_payout, // Ensure it's saved
                'completion_verified_at' => now(),
                'completion_otp_hash' => null,
                'completion_otp_expires_at' => null,
                'completion_otp_attempts' => 0,
                'completion_otp_channel' => null,
                'completion_otp_sent_to' => null,
                'completion_otp_sent_at' => null,
                // admin_approved_at stays null until admin approves
            ]);

            // Update all daily services to completed
            $serviceRequest->dailyServices()
                ->where('status', 'in_progress')
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

            DB::commit();

            Log::info('Service completed', [
                'service_request_id' => $serviceRequest->id,
                'staff_id' => Auth::id(),
                'total_payout' => $serviceRequest->total_staff_payout,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service completed successfully! Your earnings request of ₹'.number_format($serviceRequest->total_staff_payout).' has been sent to admin for approval.',
                'service' => $serviceRequest->fresh(['serviceType', 'patient']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Service completion failed: '.$e->getMessage(), [
                'service_request_id' => $serviceRequest->id,
                'staff_id' => Auth::id(),
                'error' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete service. Please try again.',
            ], 500);
        }
    }

    /**
     * Staff: Accept booking request (One-Way Booking)
     */
    public function acceptBooking(Request $request, ServiceRequest $serviceRequest)
    {
        $user = Auth::user();

        // Verify this booking is assigned to this staff member
        if ($serviceRequest->assigned_staff_id !== $user->id) {
            abort(403, 'This booking is not assigned to you.');
        }

        // Verify status is pending_approval
        if ($serviceRequest->status !== 'pending_approval') {
            return redirect()->back()
                ->with('error', 'This booking cannot be accepted. Current status: '.$serviceRequest->status);
        }

        if (! $serviceRequest->isVisitPaymentSettled()) {
            return redirect()->back()
                ->with('error', 'This booking is unpaid. The patient must complete visit payment before you can accept.');
        }

        try {
            DB::beginTransaction();

            // Update service request status
            $serviceRequest->update([
                'status' => 'assigned',
                'staff_approved_at' => now(),
            ]);

            // Ensure booking rows are in scheduled state after acceptance.
            DailyService::where('service_request_id', $serviceRequest->id)
                ->whereIn('status', ['pending', 'scheduled'])
                ->update(['status' => 'scheduled']);

            DB::commit();

            try {
                app(\App\Modules\Auth\Services\AppNotificationService::class)
                    ->notifyBookingAccepted($serviceRequest->fresh(['assignedStaff', 'serviceType', 'patient']) ?? $serviceRequest);
            } catch (\Throwable $e) {
                report($e);
            }

            return redirect()->route('staff.dashboard')
                ->with('success', 'Booking accepted successfully! You can now view the service details.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Staff booking acceptance failed: '.$e->getMessage(), [
                'service_request_id' => $serviceRequest->id,
                'staff_id' => $user->id,
                'error' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to accept booking. Please try again.');
        }
    }

    /**
     * Staff: Reject booking request (One-Way Booking)
     */
    public function rejectBooking(Request $request, ServiceRequest $serviceRequest)
    {
        $user = Auth::user();

        // Verify this booking is assigned to this staff member
        if ($serviceRequest->assigned_staff_id !== $user->id) {
            abort(403, 'This booking is not assigned to you.');
        }

        // Verify status is pending_approval
        if ($serviceRequest->status !== 'pending_approval') {
            return redirect()->back()
                ->with('error', 'This booking cannot be rejected. Current status: '.$serviceRequest->status);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update service request status
            $serviceRequest->update([
                'status' => 'pending', // Back to pending for admin to reassign
                'assigned_staff_id' => null, // Remove assignment
                'assigned_at' => null,
                'staff_rejected_at' => now(),
                'staff_rejection_reason' => $request->rejection_reason,
            ]);

            // Remove non-final daily rows created during pending approval to prevent duplicates on reassign.
            DailyService::where('service_request_id', $serviceRequest->id)
                ->whereIn('status', ['pending', 'scheduled', 'in_progress'])
                ->delete();

            DB::commit();

            try {
                app(\App\Modules\Auth\Services\AppNotificationService::class)
                    ->notifyBookingRejected(
                        $serviceRequest->fresh(['serviceType', 'patient']) ?? $serviceRequest,
                        $request->rejection_reason
                    );
            } catch (\Throwable $e) {
                report($e);
            }

            return redirect()->route('staff.dashboard')
                ->with('success', 'Booking rejected. The patient will be notified and admin can assign another staff member.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Staff booking rejection failed: '.$e->getMessage(), [
                'service_request_id' => $serviceRequest->id,
                'staff_id' => $user->id,
                'error' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to reject booking. Please try again.');
        }
    }

    /**
     * Staff: Cancel booking before visit starts (pending_approval / assigned).
     * Ends the booking (unlike reject, which returns it to the pool). Admin cannot cancel.
     */
    public function cancelBooking(Request $request, ServiceRequest $serviceRequest)
    {
        $user = Auth::user();

        if (! $user->isStaff()) {
            abort(403, 'Only nurses and caregivers can cancel assigned bookings.');
        }

        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $cancelled = app(\App\Modules\Services\Services\ServiceCancellationService::class)
                ->cancelByStaff($serviceRequest, $user, $request->input('cancellation_reason'));

            $message = 'Booking cancelled.';
            if ($cancelled->isRefundDue()) {
                $message .= ' A visit refund of ₹'.number_format((float) $cancelled->refund_amount, 2).' was queued for admin to pay manually.';
            }

            return redirect()->route('staff.dashboard')->with('success', $message);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Staff booking cancel failed: '.$e->getMessage(), [
                'service_request_id' => $serviceRequest->id,
                'staff_id' => $user->id,
            ]);

            return redirect()->back()->with('error', 'Failed to cancel booking. Please try again.');
        }
    }

    /**
     * Show patient rewards page (for submitting patient details)
     */
    public function rewards()
    {
        $user = Auth::user();
        $rewardService = app(RewardService::class);
        $rewardService->syncStaffRewardPoints($user);

        $patientAccountService = app(\App\Modules\Rewards\Services\PatientRewardAccountService::class);
        CaregiverReward::query()
            ->where('user_id', $user->id)
            ->verified()
            ->whereNull('patient_user_id')
            ->each(function (CaregiverReward $reward) use ($patientAccountService) {
                try {
                    $patientAccountService->provisionFromVerifiedReward($reward);
                } catch (\Throwable $e) {
                    report($e);
                }
            });
        $payoutService = app(StaffPayoutService::class);

        $userService = app(\App\Modules\Auth\Services\UserService::class);
        CaregiverReward::query()
            ->where('user_id', $user->id)
            ->each(function (CaregiverReward $reward) use ($userService) {
                $formatted = $userService->formatPhoneStorage((string) $reward->patient_phone);
                if ($formatted !== null && $formatted !== $reward->patient_phone) {
                    $reward->forceFill(['patient_phone' => $formatted])->save();
                }
            });

        $rewards = CaregiverReward::where('user_id', $user->id)
            ->with('patientUser')
            ->latest()
            ->paginate(10);

        $verifiedRewardsQuery = CaregiverReward::where('user_id', $user->id)->verified();
        $verifiedPoints = (int) (clone $verifiedRewardsQuery)->sum('reward_points');
        $earnedAmount = $rewardService->calculateRewardAmount($verifiedPoints);
        $staffMobileVerified = $user->hasVerifiedPhone();
        $heldEarningsDueToUnverifiedMobile = $payoutService->calculateHeldDueToUnverifiedMobile($user);
        $heldAmount = $heldEarningsDueToUnverifiedMobile
            ? (float) ($heldEarningsDueToUnverifiedMobile['patient_reward']['amount'] ?? 0)
            : 0.0;

        $stats = [
            'total_submissions' => CaregiverReward::where('user_id', $user->id)->count(),
            'total_points' => $verifiedPoints,
            'earned_amount' => $earnedAmount,
            'payable_amount' => $staffMobileVerified ? $earnedAmount : 0.0,
            'held_amount' => $heldAmount,
            'this_month' => (clone $verifiedRewardsQuery)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('reward_amount'),
        ];

        return view('services::staff.rewards.index', compact(
            'rewards',
            'stats',
            'user',
            'staffMobileVerified',
            'heldEarningsDueToUnverifiedMobile'
        ));
    }

    /**
     * Show staff referral program page
     */
    public function staffReferrals()
    {
        $user = Auth::user();
        $referralService = app(ReferralService::class);
        $payoutService = app(StaffPayoutService::class);

        $referralLink = $referralService->getReferralLink($user);
        $referralStats = $referralService->getReferralStats($user);
        $basePerRef = 100;
        $completedReferrals = (int) ($referralStats['completed_referrals'] ?? 0);
        $staffReferralTotalBase = $completedReferrals * $basePerRef;
        $staffReferralEarnedAmount = (float) ($referralStats['total_reward_amount'] ?? 0);
        $staffMobileVerified = $user->hasVerifiedPhone();
        $heldEarningsDueToUnverifiedMobile = $payoutService->calculateHeldDueToUnverifiedMobile($user);
        $staffReferralHeldAmount = $heldEarningsDueToUnverifiedMobile
            ? (float) ($heldEarningsDueToUnverifiedMobile['staff_referral']['amount'] ?? 0)
            : 0.0;
        $staffReferralPayableAmount = $staffMobileVerified ? $staffReferralEarnedAmount : 0.0;

        $referrals = \App\Modules\Referrals\Models\Referral::where('referrer_id', $user->id)
            ->with('referred')
            ->latest()
            ->paginate(10);

        return view('services::staff.staff-referrals.index', compact(
            'referralLink',
            'referralStats',
            'referrals',
            'user',
            'staffReferralTotalBase',
            'staffReferralEarnedAmount',
            'staffReferralPayableAmount',
            'staffReferralHeldAmount',
            'staffMobileVerified',
            'heldEarningsDueToUnverifiedMobile',
            'basePerRef'
        ));
    }

    /**
     * Show subscription referral program page
     */
    public function subscriptionReferrals()
    {
        $user = Auth::user();
        $payoutService = app(StaffPayoutService::class);
        $staffMobileVerified = $user->hasVerifiedPhone();
        $heldEarningsDueToUnverifiedMobile = $payoutService->calculateHeldDueToUnverifiedMobile($user);

        $subscriptionReferralLink = route('plans.index', ['ref' => $user->id]);

        $subscriptions = \App\Modules\Plans\Models\Subscription::where('referrer_id', $user->id)
            ->with(['user', 'plan'])
            ->latest()
            ->paginate(10);

        $subscriptionBaseQuery = \App\Modules\Plans\Models\Subscription::query()
            ->where('referrer_id', $user->id);
        $totalReferrals = (clone $subscriptionBaseQuery)->count();
        $activeReferrals = (clone $subscriptionBaseQuery)->where('status', 'active')->count();

        $subscriptionLedgerBaseQuery = IncentiveLedger::query()
            ->where('staff_id', $user->id)
            ->where('source_type', IncentiveLedger::SOURCE_SUBSCRIPTION_SALE);
        $subscriptionLedgerSourceIds = (clone $subscriptionLedgerBaseQuery)->pluck('source_id');
        $ledgerTotalCommission = (float) (clone $subscriptionLedgerBaseQuery)->sum('final_amount');
        $ledgerThisMonthCommission = (float) (clone $subscriptionLedgerBaseQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('final_amount');

        $legacyCommissionBaseQuery = \App\Modules\Plans\Models\Subscription::query()
            ->where('referrer_id', $user->id)
            ->where('status', 'active');
        if ($subscriptionLedgerSourceIds->isNotEmpty()) {
            $legacyCommissionBaseQuery->whereNotIn('id', $subscriptionLedgerSourceIds);
        }
        $legacyTotalCommission = (float) (clone $legacyCommissionBaseQuery)->sum('referral_commission_amount');
        $legacyThisMonthCommission = (float) (clone $legacyCommissionBaseQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('referral_commission_amount');

        $earnedCommission = $ledgerTotalCommission + $legacyTotalCommission;
        $subscriptionHeldAmount = $heldEarningsDueToUnverifiedMobile
            ? (float) ($heldEarningsDueToUnverifiedMobile['subscription_referral']['amount'] ?? 0)
            : 0.0;

        $stats = (object) [
            'total_referrals' => $totalReferrals,
            'active_referrals' => $activeReferrals,
            'earned_commission' => $earnedCommission,
            'total_commission' => $staffMobileVerified ? $earnedCommission : 0.0,
            'this_month_commission' => $staffMobileVerified
                ? ($ledgerThisMonthCommission + $legacyThisMonthCommission)
                : 0.0,
            'this_month_earned' => $ledgerThisMonthCommission + $legacyThisMonthCommission,
        ];

        return view('services::staff.subscription-referrals.index', compact(
            'subscriptionReferralLink',
            'subscriptions',
            'stats',
            'user',
            'staffMobileVerified',
            'heldEarningsDueToUnverifiedMobile',
            'subscriptionHeldAmount'
        ));
    }

    /**
     * Unified incentive details for staff self-view and admin drilldown.
     */
    public function incentiveDetails(?User $staff = null)
    {
        $viewer = Auth::user();
        $isAdminViewer = $viewer && $viewer->role === 'admin';

        $targetStaff = $staff;
        if (! $targetStaff) {
            $targetStaff = $viewer;
        }

        if (! $targetStaff || ! in_array($targetStaff->role, ['nurse', 'caregiver'], true)) {
            abort(404, 'Staff member not found.');
        }

        if (! $isAdminViewer && (int) $targetStaff->id !== (int) $viewer->id) {
            abort(403, 'You can only view your own incentive details.');
        }

        $data = app(StaffIncentiveDetailsDataService::class)->buildForStaff($targetStaff);

        return view('services::staff.incentive-details', array_merge($data, [
            'isAdminViewer' => $isAdminViewer,
        ]));
    }

    public function verifyReferralOtp(Request $request)
    {
        $user = Auth::user();
        if ($user->hasPendingMobileContactVerification()) {
            return redirect()->back()
                ->with('error', 'Please complete your pending profile contact verification first.');
        }

        $request->validate([
            'otp_code' => ['required', 'digits:6'],
        ]);
        $result = app(ReferralService::class)->verifyReferralOtpForReferred(Auth::user(), (string) $request->otp_code);

        return redirect()->route('staff.dashboard')
            ->with(($result['success'] ?? false) ? 'success' : 'error', $result['message'] ?? 'OTP verification failed.');
    }

    public function resendReferralOtp(Request $request)
    {
        $user = Auth::user();
        if ($user->hasPendingMobileContactVerification()) {
            return redirect()->back()
                ->with('error', 'Please complete your pending profile contact verification first.');
        }

        $result = app(ReferralService::class)->resendReferralOtpForReferred(Auth::user());

        return redirect()->back()
            ->with(($result['success'] ?? false) ? 'success' : 'error', $result['message'] ?? 'Failed to resend OTP.');
    }

    public function sendCompletionOtpFromBanner(Request $request, ServiceRequest $serviceRequest)
    {
        $response = $this->sendCompletionOtp($request, $serviceRequest);
        $payload = method_exists($response, 'getData') ? (array) $response->getData(true) : [];
        $ok = (bool) ($payload['success'] ?? false);

        return redirect()->back()
            ->with($ok ? 'success' : 'error', (string) ($payload['message'] ?? ($ok ? 'OTP sent successfully.' : 'Failed to send OTP.')));
    }

    public function completeServiceFromBanner(Request $request, ServiceRequest $serviceRequest)
    {
        $response = $this->completeService($request, $serviceRequest);
        $payload = method_exists($response, 'getData') ? (array) $response->getData(true) : [];
        $ok = (bool) ($payload['success'] ?? false);

        return redirect()->back()
            ->with($ok ? 'success' : 'error', (string) ($payload['message'] ?? ($ok ? 'Service completed successfully.' : 'Failed to complete service.')));
    }

    private function normalizeIndianPhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (! $digits) {
            return null;
        }
        if (strlen($digits) === 10) {
            return '91'.$digits;
        }
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return $digits;
        }

        return null;
    }

    private function maskPhone(string $normalizedPhone): string
    {
        return str_repeat('*', max(0, strlen($normalizedPhone) - 4)).substr($normalizedPhone, -4);
    }

    /**
     * Patient mobile matches staff verified account — login OTP already proved possession.
     */
    private function staffMayCompleteWithoutPatientOtp(User $staff, ServiceRequest $serviceRequest): bool
    {
        return $serviceRequest->staffMayCompleteWithoutPatientOtp($staff);
    }
}

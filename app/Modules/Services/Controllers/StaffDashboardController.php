<?php

namespace App\Modules\Services\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Services\Models\DailyService;
use App\Modules\Rewards\Models\CaregiverReward;
use App\Modules\Rewards\Services\RewardService;
use App\Modules\Referrals\Services\ReferralService;
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
        $assignedServices = ServiceRequest::where('assigned_staff_id', $user->id)
            ->whereIn('status', ['pending_approval', 'assigned', 'in_progress', 'completed'])
            ->with(['patient', 'serviceType', 'assignedStaff'])
            ->orderByRaw("CASE WHEN status = 'pending_approval' THEN 0 ELSE 1 END")
            ->orderBy('start_date', 'desc')
            ->paginate(10);
        
        // Calculate missing payouts for services
        foreach ($assignedServices as $service) {
            // Ensure relationships are loaded
            if (!$service->relationLoaded('assignedStaff')) {
                $service->load('assignedStaff');
            }
            if (!$service->relationLoaded('serviceType')) {
                $service->load('serviceType');
            }
            
            if (!$service->total_staff_payout && $service->assignedStaff && $service->serviceType) {
                $dailyStaffPayout = $user->isNurse() ? $service->serviceType->nurse_payout : $service->serviceType->caregiver_payout;
                $totalStaffPayout = $service->duration_days * $dailyStaffPayout;
                $service->update(['total_staff_payout' => $totalStaffPayout]);
            }
        }
        
        // Get statistics
        $allServices = ServiceRequest::where('assigned_staff_id', $user->id);
        
        $stats = [
            'total_assignments' => $allServices->count(),
            'active_assignments' => $allServices->whereIn('status', ['assigned', 'in_progress'])->count(),
            'completed_assignments' => $allServices->where('status', 'completed')->count(),
            'pending_assignments' => $allServices->where('status', 'assigned')->count(),
        ];
        
        // ============================================
        // CALCULATE EARNINGS FROM 4 SOURCES
        // ============================================
        
        // 1. SERVICE REQUEST EARNINGS (from assigned services)
        $allServicesData = ServiceRequest::where('assigned_staff_id', $user->id)
            ->whereNotNull('total_staff_payout')
            ->get();
        
        $serviceRequestEarnings = [
            'total_approved' => $allServicesData->whereNotNull('admin_approved_at')->sum('total_staff_payout'),
            'pending_approval' => $allServicesData->where('status', 'completed')->whereNull('admin_approved_at')->sum('total_staff_payout'),
            'upcoming' => ServiceRequest::where('assigned_staff_id', $user->id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->whereNotNull('total_staff_payout')
                ->sum('total_staff_payout'),
            'this_month' => $allServicesData->whereNotNull('admin_approved_at')
                ->filter(function($service) {
                    if (!$service->admin_approved_at) return false;
                    $approvedDate = $service->admin_approved_at;
                    return $approvedDate->month === now()->month && $approvedDate->year === now()->year;
                })
                ->sum('total_staff_payout'),
            'total_count' => $allServicesData->whereNotNull('admin_approved_at')->count(),
        ];
        
        // 2. PATIENT REWARD EARNINGS (from submitting patient details)
        $rewardService = app(RewardService::class);
        $totalPoints = $user->reward_points ?? 0;
        $patientRewardEarnings = [
            'total_points' => $totalPoints,
            'total_amount' => $rewardService->calculateRewardAmount($totalPoints),
            'total_submissions' => CaregiverReward::where('user_id', $user->id)->count(),
            'this_month' => CaregiverReward::where('user_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('reward_amount'),
        ];
        
        // 3. STAFF REFERRAL EARNINGS (from referring other staff)
        $referralService = app(ReferralService::class);
        $referralStats = $referralService->getReferralStats($user);
        $staffReferralEarnings = [
            'total_referrals' => $referralStats['completed_referrals'],
            'total_points' => $referralStats['total_reward_points'],
            'total_amount' => $referralStats['total_reward_amount'],
            'this_month' => \App\Modules\Referrals\Models\Referral::where('referrer_id', $user->id)
                ->where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->whereYear('completed_at', now()->year)
                ->sum('reward_amount'),
        ];
        
        // 4. SUBSCRIPTION REFERRAL EARNINGS (from referring patients to subscribe)
        $subscriptionReferralStats = \App\Modules\Plans\Models\Subscription::where('referrer_id', $user->id)
            ->selectRaw('COUNT(*) as total_referrals')
            ->selectRaw('SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_referrals')
            ->selectRaw('SUM(referral_commission_amount) as total_commission')
            ->selectRaw('SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN referral_commission_amount ELSE 0 END) as this_month_commission', [now()->month, now()->year])
            ->first();
        
        $subscriptionReferralEarnings = [
            'total_referrals' => $subscriptionReferralStats->total_referrals ?? 0,
            'active_referrals' => $subscriptionReferralStats->active_referrals ?? 0,
            'total_commission' => $subscriptionReferralStats->total_commission ?? 0.00,
            'this_month' => $subscriptionReferralStats->this_month_commission ?? 0.00,
        ];
        
        // TOTAL OVERALL EARNINGS (sum of all 4 sources)
        $totalOverallEarnings = 
            $serviceRequestEarnings['total_approved'] + 
            $patientRewardEarnings['total_amount'] + 
            $staffReferralEarnings['total_amount'] + 
            $subscriptionReferralEarnings['total_commission'];
        
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
        
        return view('services::staff.dashboard', compact(
            'assignedServices', 
            'stats', 
            'earningsStats',
            'serviceRequestEarnings',
            'patientRewardEarnings',
            'staffReferralEarnings',
            'subscriptionReferralEarnings',
            'totalOverallEarnings',
            'recentRewards', 
            'referralLink', 
            'referralStats', 
            'recentReferrals', 
            'subscriptionReferralLink'
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
        
        // If total_staff_payout is not calculated, calculate it now
        if (!$serviceRequest->total_staff_payout && $serviceRequest->assigned_staff_id) {
            $staff = $serviceRequest->assignedStaff;
            $serviceType = $serviceRequest->serviceType;
            
            // Null checks before accessing properties
            if (!$staff) {
                abort(404, 'Assigned staff not found.');
            }
            if (!$serviceType) {
                abort(404, 'Service type not found.');
            }
            
            $dailyStaffPayout = $staff->isNurse() ? $serviceType->nurse_payout : $serviceType->caregiver_payout;
            $totalStaffPayout = $serviceRequest->duration_days * $dailyStaffPayout;
            
            $serviceRequest->update([
                'total_staff_payout' => $totalStaffPayout
            ]);
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
                'message' => 'You are not assigned to this service.'
            ], 403);
        }
        
        // CRITICAL FIX #5: Validate status transition using state machine
        if (!$serviceRequest->canTransitionTo('in_progress')) {
            $validStatuses = implode(', ', $serviceRequest->getValidNextStatuses());
            return response()->json([
                'success' => false,
                'message' => "Service cannot be started from current status '{$serviceRequest->status}'. Valid next statuses: {$validStatuses}"
            ], 400);
        }

        // Additional validation: Check if service start date is valid
        if ($serviceRequest->start_date > now()->startOfDay()) {
            return response()->json([
                'success' => false,
                'message' => 'Service cannot be started before the assigned start date: ' . $serviceRequest->start_date->format('M d, Y')
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
                'staff_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service started successfully!',
                'service' => $serviceRequest->fresh(['serviceType', 'patient'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Service start failed: ' . $e->getMessage(), [
                'service_request_id' => $serviceRequest->id,
                'staff_id' => Auth::id(),
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start service. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Complete a service (change status from in_progress to completed)
     */
    public function completeService(Request $request, ServiceRequest $serviceRequest)
    {
        // Ensure this service is assigned to the current staff member
        if ($serviceRequest->assigned_staff_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this service.'
            ], 403);
        }
        
        // CRITICAL FIX #5: Validate status transition using state machine
        if (!$serviceRequest->canTransitionTo('completed')) {
            $validStatuses = implode(', ', $serviceRequest->getValidNextStatuses());
            return response()->json([
                'success' => false,
                'message' => "Service cannot be completed from current status '{$serviceRequest->status}'. Valid next statuses: {$validStatuses}"
            ], 400);
        }

        // Additional validation: Check if service end date is valid
        if ($serviceRequest->end_date > now()->startOfDay()) {
            return response()->json([
                'success' => false,
                'message' => 'Service cannot be completed before the assigned end date: ' . $serviceRequest->end_date->format('M d, Y')
            ], 400);
        }
        
        // CRITICAL FIX #3: Wrap in transaction
        try {
            DB::beginTransaction();

            // Ensure relationships are loaded
            $serviceRequest->load(['assignedStaff', 'serviceType']);

            // Ensure payout is calculated before completing
            if (!$serviceRequest->total_staff_payout) {
                $staff = $serviceRequest->assignedStaff;
                $serviceType = $serviceRequest->serviceType;
                
                // Null checks before accessing properties
                if (!$staff) {
                    throw new \Exception("Assigned staff not found for service request #{$serviceRequest->id}");
                }
                if (!$serviceType) {
                    throw new \Exception("Service type not found for service request #{$serviceRequest->id}");
                }
                
                $dailyStaffPayout = $staff->isNurse() ? $serviceType->nurse_payout : $serviceType->caregiver_payout;
                $totalStaffPayout = $serviceRequest->duration_days * $dailyStaffPayout;
                
                $serviceRequest->total_staff_payout = $totalStaffPayout;
            }
            
            // Update service status (admin approval still pending)
            $serviceRequest->update([
                'status' => 'completed',
                'completed_at' => now(),
                'total_staff_payout' => $serviceRequest->total_staff_payout, // Ensure it's saved
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
                'total_payout' => $serviceRequest->total_staff_payout
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service completed successfully! Your earnings request of ₹' . number_format($serviceRequest->total_staff_payout) . ' has been sent to admin for approval.',
                'service' => $serviceRequest->fresh(['serviceType', 'patient'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Service completion failed: ' . $e->getMessage(), [
                'service_request_id' => $serviceRequest->id,
                'staff_id' => Auth::id(),
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete service. Please try again.'
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
                ->with('error', 'This booking cannot be accepted. Current status: ' . $serviceRequest->status);
        }

        try {
            DB::beginTransaction();

            // Update service request status
            $serviceRequest->update([
                'status' => 'assigned',
                'staff_approved_at' => now(),
            ]);

            // Update daily services status from 'pending' to 'scheduled'
            DailyService::where('service_request_id', $serviceRequest->id)
                ->where('status', 'pending')
                ->update(['status' => 'scheduled']);

            DB::commit();

            return redirect()->route('staff.dashboard')
                ->with('success', 'Booking accepted successfully! You can now view the service details.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Staff booking acceptance failed: ' . $e->getMessage(), [
                'service_request_id' => $serviceRequest->id,
                'staff_id' => $user->id,
                'error' => $e->getTraceAsString()
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
                ->with('error', 'This booking cannot be rejected. Current status: ' . $serviceRequest->status);
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

            // Delete pending daily services
            DailyService::where('service_request_id', $serviceRequest->id)
                ->where('status', 'pending')
                ->delete();

            DB::commit();

            return redirect()->route('staff.dashboard')
                ->with('success', 'Booking rejected. The patient will be notified and admin can assign another staff member.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Staff booking rejection failed: ' . $e->getMessage(), [
                'service_request_id' => $serviceRequest->id,
                'staff_id' => $user->id,
                'error' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to reject booking. Please try again.');
        }
    }
    
    /**
     * Show patient rewards page (for submitting patient details)
     */
    public function rewards()
    {
        $user = Auth::user();
        $rewardService = app(RewardService::class);
        
        $rewards = CaregiverReward::where('user_id', $user->id)
            ->latest()
            ->paginate(20);
        
        $totalPoints = $user->reward_points ?? 0;
        $totalAmount = $rewardService->calculateRewardAmount($totalPoints);
        
        $stats = [
            'total_submissions' => CaregiverReward::where('user_id', $user->id)->count(),
            'total_points' => $totalPoints,
            'total_amount' => $totalAmount,
            'this_month' => CaregiverReward::where('user_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('reward_amount'),
        ];
        
        return view('services::staff.rewards.index', compact('rewards', 'stats', 'user'));
    }
    
    /**
     * Show staff referral program page
     */
    public function staffReferrals()
    {
        $user = Auth::user();
        $referralService = app(ReferralService::class);
        
        $referralLink = $referralService->getReferralLink($user);
        $referralStats = $referralService->getReferralStats($user);
        $referrals = \App\Modules\Referrals\Models\Referral::where('referrer_id', $user->id)
            ->with('referred')
            ->latest()
            ->paginate(20);
        
        return view('services::staff.staff-referrals.index', compact('referralLink', 'referralStats', 'referrals', 'user'));
    }
    
    /**
     * Show subscription referral program page
     */
    public function subscriptionReferrals()
    {
        $user = Auth::user();
        
        $subscriptionReferralLink = route('plans.index', ['ref' => $user->id]);
        
        $subscriptions = \App\Modules\Plans\Models\Subscription::where('referrer_id', $user->id)
            ->with(['user', 'plan'])
            ->latest()
            ->paginate(20);
        
        $stats = \App\Modules\Plans\Models\Subscription::where('referrer_id', $user->id)
            ->selectRaw('COUNT(*) as total_referrals')
            ->selectRaw('SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_referrals')
            ->selectRaw('SUM(referral_commission_amount) as total_commission')
            ->selectRaw('SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN referral_commission_amount ELSE 0 END) as this_month_commission', [now()->month, now()->year])
            ->first();
        
        return view('services::staff.subscription-referrals.index', compact('subscriptionReferralLink', 'subscriptions', 'stats', 'user'));
    }
}

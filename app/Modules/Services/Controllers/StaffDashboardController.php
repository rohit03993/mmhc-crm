<?php

namespace App\Modules\Services\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Rewards\Models\CaregiverReward;
use App\Modules\Rewards\Services\RewardService;

class StaffDashboardController extends Controller
{
    /**
     * Show staff dashboard with assigned services
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get assigned services for this staff member
        $assignedServices = ServiceRequest::where('assigned_staff_id', $user->id)
            ->with(['patient', 'serviceType', 'assignedStaff'])
            ->orderBy('start_date', 'desc')
            ->paginate(10);
        
        // Calculate missing payouts for services
        foreach ($assignedServices as $service) {
            if (!$service->total_staff_payout && $service->assignedStaff && $service->serviceType) {
                $dailyStaffPayout = $user->isNurse() ? $service->serviceType->nurse_payout : $service->serviceType->caregiver_payout;
                $totalStaffPayout = $service->duration_days * $dailyStaffPayout;
                $service->update(['total_staff_payout' => $totalStaffPayout]);
            }
        }
        
        // Get statistics
        $stats = [
            'total_assignments' => ServiceRequest::where('assigned_staff_id', $user->id)->count(),
            'active_assignments' => ServiceRequest::where('assigned_staff_id', $user->id)
                ->whereIn('status', ['assigned', 'in_progress'])->count(),
            'completed_assignments' => ServiceRequest::where('assigned_staff_id', $user->id)
                ->where('status', 'completed')->count(),
            'pending_assignments' => ServiceRequest::where('assigned_staff_id', $user->id)
                ->where('status', 'assigned')->count(),
        ];

        $rewardService = app(RewardService::class);
        $totalPoints = $user->reward_points ?? 0;
        $recentRewards = CaregiverReward::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();
        $rewardSummary = [
            'points' => $totalPoints,
            'amount' => $rewardService->calculateRewardAmount($totalPoints),
        ];
        
        return view('services::staff.dashboard', compact('assignedServices', 'stats', 'recentRewards', 'rewardSummary'));
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
        
        // If total_staff_payout is not calculated, calculate it now
        if (!$serviceRequest->total_staff_payout && $serviceRequest->assigned_staff_id && $serviceRequest->serviceType) {
            $staff = $serviceRequest->assignedStaff;
            $serviceType = $serviceRequest->serviceType;
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

            // Ensure payout is calculated before completing
            if (!$serviceRequest->total_staff_payout && $serviceRequest->assignedStaff && $serviceRequest->serviceType) {
                $staff = $serviceRequest->assignedStaff;
                $serviceType = $serviceRequest->serviceType;
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
}

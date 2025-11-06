<?php

namespace App\Modules\Services\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Services\Models\ServiceType;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Services\Models\DailyService;
use App\Models\Core\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    /**
     * Display available service types for patients
     */
    public function index()
    {
        $serviceTypes = ServiceType::getActiveServiceTypes();
        
        return view('services::services.index', compact('serviceTypes'));
    }

    /**
     * Show service request form
     */
    public function create(Request $request)
    {
        $serviceTypes = ServiceType::getActiveServiceTypes();
        $user = Auth::user();
        
        // Get selected staff info from URL parameters (when coming from Available Staff page)
        $selectedStaff = null;
        $selectedStaffType = $request->get('staff_type'); // 'nurse' or 'caregiver'
        $selectedStaffId = $request->get('staff_id');
        
        if ($selectedStaffId && $selectedStaffType) {
            $selectedStaff = User::where('id', $selectedStaffId)
                ->where('role', $selectedStaffType)
                ->where('is_active', true)
                ->first();
        }
        
        return view('services::services.create', compact('serviceTypes', 'user', 'selectedStaff', 'selectedStaffType'));
    }

    /**
     * Store service request
     */
    public function store(Request $request)
    {
        // Get service type to check if it's a single visit
        $serviceType = ServiceType::find($request->service_type_id);
        $isSingleVisit = $serviceType && $serviceType->duration_hours == 1;
        
        // Validation rules
        $rules = [
            'service_type_id' => 'required|exists:service_types,id',
            'preferred_staff_type' => 'required|in:nurse,caregiver,any',
            'start_date' => 'required|date|after_or_equal:today',
            'location' => 'required|string|max:500',
            'contact_person' => 'required|string|max:255',
            'contact_phone' => 'required|string|regex:/^[0-9]{10}$/',
            'notes' => 'nullable|string|max:1000',
            'special_requirements' => 'nullable|string|max:1000',
            'preferred_staff_id' => 'nullable|exists:users,id', // Optional: specific staff selected
        ];
        
        // Duration validation: Allow 1 day minimum for all services (removed 7-day lock-in)
        if ($isSingleVisit) {
            $rules['duration_days'] = 'required|integer|min:1|max:1'; // Single visit = 1 day only
        } else {
            $rules['duration_days'] = 'required|integer|min:1'; // Minimum 1 day (removed 7-day requirement)
        }
        
        $messages = [
            'duration_days.min' => 'Duration must be at least 1 day.',
            'duration_days.max' => 'Single visit service is for 1 day only.',
            'contact_phone.regex' => 'Contact phone must be exactly 10 digits.',
        ];
        
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $serviceType = ServiceType::findOrFail($request->service_type_id);
        
        // Calculate end date
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = $startDate->copy()->addDays($request->duration_days - 1);
        
        // Calculate total amount
        $totalAmount = $serviceType->patient_charge * $request->duration_days;

        // Validate preferred_staff_id matches preferred_staff_type if provided
        $preferredStaffId = null;
        if ($request->preferred_staff_id) {
            $preferredStaff = User::find($request->preferred_staff_id);
            if ($preferredStaff && $preferredStaff->isStaff()) {
                // Check if staff type matches preferred_staff_type
                if ($request->preferred_staff_type === 'any' || 
                    ($request->preferred_staff_type === 'nurse' && $preferredStaff->isNurse()) ||
                    ($request->preferred_staff_type === 'caregiver' && $preferredStaff->isCaregiver())) {
                    $preferredStaffId = $request->preferred_staff_id;
                }
            }
        }

        $serviceRequest = ServiceRequest::create([
            'patient_id' => Auth::id(),
            'service_type_id' => $request->service_type_id,
            'preferred_staff_type' => $request->preferred_staff_type,
            'preferred_staff_id' => $preferredStaffId, // Store patient's selected staff
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_days' => $request->duration_days,
            'total_amount' => $totalAmount,
            'total_staff_payout' => null, // Will be calculated when staff is assigned
            'prepaid_amount' => 0.00, // No prepayment required initially
            'payment_status' => 'pending',
            'status' => 'pending',
            'notes' => $request->notes,
            'special_requirements' => $request->special_requirements,
            'location' => $request->location,
            'contact_person' => $request->contact_person,
            'contact_phone' => $request->contact_phone,
        ]);

        return redirect()->route('services.my-requests')
            ->with('success', 'Service request submitted successfully! Our team will contact you soon.');
    }

    /**
     * Display patient's service requests
     */
    public function myRequests()
    {
        $user = Auth::user();
        $serviceRequests = ServiceRequest::where('patient_id', $user->id)
            ->with(['serviceType', 'assignedStaff'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('services::services.my-requests', compact('serviceRequests'));
    }

    /**
     * Show specific service request details
     */
    public function show(ServiceRequest $serviceRequest)
    {
        // Check if user owns this request or is admin
        if ($serviceRequest->patient_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $serviceRequest->load(['serviceType', 'assignedStaff', 'dailyServices.staff']);
        
        return view('services::services.show', compact('serviceRequest'));
    }

    /**
     * Admin: Display all service requests
     */
    public function adminIndex()
    {
        $serviceRequests = ServiceRequest::with(['patient', 'serviceType', 'assignedStaff', 'preferredStaff', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $stats = [
            'total_requests' => ServiceRequest::count(),
            'pending_requests' => ServiceRequest::pending()->count(),
            'assigned_requests' => ServiceRequest::assigned()->count(),
            'in_progress_requests' => ServiceRequest::inProgress()->count(),
            'completed_requests' => ServiceRequest::completed()->count(),
            'pending_approvals' => ServiceRequest::where('status', 'completed')
                ->whereNull('admin_approved_at')
                ->count(),
        ];
        
        return view('services::admin.requests.index', compact('serviceRequests', 'stats'));
    }

    /**
     * Admin: Show assignment form
     */
    public function assignForm(ServiceRequest $serviceRequest)
    {
        $availableStaff = User::whereIn('role', ['nurse', 'caregiver'])
            ->where('is_active', true)
            ->get();
        
        $serviceRequest->load(['patient', 'serviceType', 'preferredStaff']);
        
        return view('services::admin.requests.assign', compact('serviceRequest', 'availableStaff'));
    }

    /**
     * Admin: Assign staff to service request
     */
    public function assign(Request $request, ServiceRequest $serviceRequest)
    {
        $validator = Validator::make($request->all(), [
            'assigned_staff_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $staff = User::findOrFail($request->assigned_staff_id);
        
        // Check if staff is available
        if (!$staff->isStaff()) {
            return redirect()->back()
                ->with('error', 'Selected user is not a staff member.');
        }

        // CRITICAL FIX #1: Check for overlapping services to prevent double-booking
        $overlappingServices = ServiceRequest::where('assigned_staff_id', $staff->id)
            ->where('id', '!=', $serviceRequest->id) // Exclude current service if reassigning
            ->whereIn('status', ['assigned', 'in_progress']) // Only check active services
            ->where(function($query) use ($serviceRequest) {
                // Check if new service dates overlap with existing services
                $query->where(function($q) use ($serviceRequest) {
                    // New service starts during existing service
                    $q->whereBetween('start_date', [$serviceRequest->start_date, $serviceRequest->end_date])
                      ->orWhereBetween('end_date', [$serviceRequest->start_date, $serviceRequest->end_date])
                      ->orWhere(function($subQ) use ($serviceRequest) {
                          // New service completely contains existing service
                          $subQ->where('start_date', '>=', $serviceRequest->start_date)
                               ->where('end_date', '<=', $serviceRequest->end_date);
                      })
                      ->orWhere(function($subQ) use ($serviceRequest) {
                          // Existing service completely contains new service
                          $subQ->where('start_date', '<=', $serviceRequest->start_date)
                               ->where('end_date', '>=', $serviceRequest->end_date);
                      });
                });
            })
            ->exists();

        if ($overlappingServices) {
            return redirect()->back()
                ->with('error', 'Staff member is already assigned to another service during this period. Please select a different staff member or adjust the service dates.');
        }

        // Removed prepayment requirement - admin can assign staff without payment barrier

        // Calculate staff payout based on staff type and service type (serviceType already loaded above)
        $dailyStaffPayout = $staff->isNurse() ? $serviceType->nurse_payout : $serviceType->caregiver_payout;
        $totalStaffPayout = $serviceRequest->duration_days * $dailyStaffPayout;

        // CRITICAL FIX #3: Wrap in transaction for data integrity
        try {
            DB::beginTransaction();

            // CRITICAL FIX #5: Validate status transition
            if (!$serviceRequest->canTransitionTo('assigned')) {
                throw new \Exception("Cannot assign staff. Invalid status transition from '{$serviceRequest->status}' to 'assigned'.");
            }

            $serviceRequest->update([
                'assigned_staff_id' => $request->assigned_staff_id,
                'status' => 'assigned',
                'assigned_at' => now(),
                'total_staff_payout' => $totalStaffPayout,
            ]);

            // Reload to get fresh relationships
            $serviceRequest->refresh();
            $serviceRequest->load(['assignedStaff', 'serviceType']);

            // Create daily service records
            $this->createDailyServiceRecords($serviceRequest);

            DB::commit();

            return redirect()->route('admin.service-requests')
                ->with('success', 'Staff assigned successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Staff assignment failed: ' . $e->getMessage(), [
                'service_request_id' => $serviceRequest->id,
                'staff_id' => $request->assigned_staff_id,
                'error' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to assign staff. Please try again. If the problem persists, contact support.');
        }
    }

    /**
     * Admin: Approve staff payment for completed service
     */
    public function approvePayment(Request $request, ServiceRequest $serviceRequest)
    {
        // Ensure service is completed
        if ($serviceRequest->status !== 'completed') {
            return redirect()->back()
                ->with('error', 'Service must be completed before approving payment.');
        }

        // CRITICAL FIX #4: Use database locking to prevent race condition
        try {
            DB::beginTransaction();
            
            // Lock the row for update to prevent concurrent approvals
            $serviceRequest = ServiceRequest::lockForUpdate()->findOrFail($serviceRequest->id);

            // Check if already approved (double-check after lock)
            if ($serviceRequest->isApprovedByAdmin()) {
                DB::rollBack();
                return redirect()->back()
                    ->with('info', 'Payment has already been approved by another admin.');
            }

            // Approve payment
            $serviceRequest->update([
                'admin_approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            DB::commit();

            Log::info('Payment approved', [
                'service_request_id' => $serviceRequest->id,
                'approved_by' => Auth::id(),
                'amount' => $serviceRequest->total_staff_payout
            ]);

            return redirect()->back()
                ->with('success', 'Payment approved successfully! Staff will see the earnings as approved.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment approval failed: ' . $e->getMessage(), [
                'service_request_id' => $serviceRequest->id,
                'approved_by' => Auth::id(),
                'error' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to approve payment. Please try again.');
        }
    }

    /**
     * Create daily service records for the service request
     */
    protected function createDailyServiceRecords(ServiceRequest $serviceRequest)
    {
        $startDate = $serviceRequest->start_date;
        $endDate = $serviceRequest->end_date;
        $serviceType = $serviceRequest->serviceType;
        $staff = $serviceRequest->assignedStaff;

        // Determine payout based on staff type
        $staffPayout = $staff->isNurse() ? $serviceType->nurse_payout : $serviceType->caregiver_payout;
        
        // Calculate timing based on service type duration
        $durationHours = $serviceType->duration_hours;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Set start time (default 8 AM)
            $startTime = $date->copy()->setTime(8, 0);
            
            // Calculate end time based on duration
            switch ($durationHours) {
                case 24:
                    // 24-hour service: 8 AM to next day 8 AM
                    $endTime = $date->copy()->addDay()->setTime(8, 0);
                    break;
                case 12:
                    // 12-hour service: 8 AM to 8 PM same day
                    $endTime = $date->copy()->setTime(20, 0);
                    break;
                case 8:
                    // 8-hour service: 8 AM to 4 PM same day
                    $endTime = $date->copy()->setTime(16, 0);
                    break;
                case 1:
                    // Single visit: 8 AM to 9 AM same day
                    $endTime = $date->copy()->setTime(9, 0);
                    break;
                default:
                    // Default to 12 hours if unknown
                    $endTime = $date->copy()->setTime(20, 0);
                    break;
            }

            DailyService::create([
                'service_request_id' => $serviceRequest->id,
                'staff_id' => $staff->id,
                'service_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'patient_charge' => $serviceType->patient_charge,
                'staff_payout' => $staffPayout,
                'platform_profit' => $serviceType->patient_charge - $staffPayout,
                'status' => 'scheduled',
            ]);
        }
    }
}

<?php

namespace App\Modules\Services\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Incentives\Services\IncentiveCalculatorService;
use App\Modules\Services\Models\DailyService;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Services\Models\ServiceType;
use App\Modules\Services\Services\StaffAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
     * Direct booking with staff pre-selected (One-Way Booking)
     */
    public function bookStaff(User $staff)
    {
        // Verify staff is active and valid
        if (! $staff->isStaff() || ! $staff->is_active) {
            return redirect()->route('staff.index')
                ->with('error', 'Selected staff member is not available.');
        }

        $serviceTypes = ServiceType::getActiveServiceTypes();
        $user = Auth::user();

        // Load staff profile for availability check
        $staff->load('profile');

        // Check if user has active subscription
        $subscriptionService = app(\App\Modules\Plans\Services\SubscriptionService::class);
        $hasActiveSubscription = $subscriptionService->hasActiveSubscription($user);
        $activeSubscription = $subscriptionService->getActiveSubscription($user);

        return view('services::services.book-staff', compact('serviceTypes', 'user', 'staff', 'hasActiveSubscription', 'activeSubscription'));
    }

    /**
     * Show service request form (Legacy - Redirected to One-Way Booking)
     *
     * NEW FLOW: Patients must select staff first, then book
     * This route now redirects to staff listing or direct booking
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // If staff_id is provided, redirect to new direct booking route
        $selectedStaffId = $request->get('staff_id');
        $selectedStaffType = $request->get('staff_type');

        if ($selectedStaffId && $selectedStaffType) {
            $staff = User::where('id', $selectedStaffId)
                ->where('role', $selectedStaffType)
                ->where('is_active', true)
                ->first();

            if ($staff) {
                // Redirect to new direct booking route
                return redirect()->route('book.staff', $staff)
                    ->with('info', 'Please select a service type and complete your booking.');
            }
        }

        // No staff selected - redirect to staff listing (new one-way booking flow)
        return redirect()->route('staff.index')
            ->with('info', 'Please select a healthcare staff member first to book a service. This is our new streamlined booking process!');
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

        // Additional null check after validation (defensive programming)
        if (! $serviceType) {
            return redirect()->back()
                ->withErrors(['service_type_id' => 'Selected service type not found.'])
                ->withInput();
        }

        // Calculate end date
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = $startDate->copy()->addDays($request->duration_days - 1);

        // Check if patient has active subscription
        $patient = Auth::user();
        $subscriptionService = app(\App\Modules\Plans\Services\SubscriptionService::class);
        $hasActiveSubscription = $subscriptionService->hasActiveSubscription($patient);

        // Calculate total amount (free for subscribers)
        $totalAmount = $hasActiveSubscription ? 0.00 : ($serviceType->patient_charge * $request->duration_days);

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
            'prepaid_amount' => $hasActiveSubscription ? 0.00 : 0.00,
            'payment_status' => $hasActiveSubscription || $totalAmount <= 0 ? 'paid' : 'pending',
            'status' => 'pending',
            'notes' => $request->notes,
            'special_requirements' => $request->special_requirements,
            'location' => $request->location,
            'contact_person' => $request->contact_person,
            'contact_phone' => $request->contact_phone,
        ]);

        if ($hasActiveSubscription || $totalAmount <= 0) {
            return redirect()->route('services.my-requests')
                ->with('success', 'Service request submitted successfully! This service is FREE with your active subscription. Our team will contact you soon.');
        }

        return redirect()->route('services.pay', $serviceRequest)
            ->with('success', 'Booking created. Please pay the visit fee of ₹'.number_format($totalAmount, 0).' to confirm.');
    }

    /**
     * Store direct booking with staff pre-assigned (One-Way Booking)
     */
    public function storeDirectBooking(Request $request, User $staff)
    {
        // Verify staff is active and valid
        if (! $staff->isStaff() || ! $staff->is_active) {
            return redirect()->route('staff.index')
                ->with('error', 'Selected staff member is not available.');
        }

        // Get service type
        $serviceType = ServiceType::find($request->service_type_id);
        if (! $serviceType) {
            return redirect()->back()
                ->with('error', 'Selected service type not found.')
                ->withInput();
        }

        $isSingleVisit = $serviceType->duration_hours == 1;

        // Validation rules
        $rules = [
            'service_type_id' => 'required|exists:service_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'duration_days' => $isSingleVisit ? 'required|integer|min:1|max:1' : 'required|integer|min:1',
            'location' => 'required|string|max:500',
            'contact_person' => 'required|string|max:255',
            'contact_phone' => 'required|string|regex:/^[0-9]{10}$/',
            'notes' => 'nullable|string|max:1000',
            'special_requirements' => 'nullable|string|max:1000',
        ];

        $validator = Validator::make($request->all(), $rules, [
            'duration_days.min' => 'Duration must be at least 1 day.',
            'duration_days.max' => 'Single visit service is for 1 day only.',
            'contact_phone.regex' => 'Contact phone must be exactly 10 digits.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Calculate dates
        $startDate = Carbon::parse($request->start_date);
        $endDate = $startDate->copy()->addDays($request->duration_days - 1);

        // Check staff availability
        $availabilityCheck = StaffAvailabilityService::checkAvailability($staff, $startDate, $endDate);

        if (! $availabilityCheck['available']) {
            // Get alternative staff
            $patient = Auth::user();
            $alternatives = StaffAvailabilityService::getAlternativeStaff(
                $staff->role,
                $startDate,
                $endDate,
                $patient->pincode,
                5
            );

            return redirect()->back()
                ->with('error', $availabilityCheck['reason'])
                ->with('alternatives', $alternatives)
                ->with('alternative_message', 'Here are some alternative staff members available for your dates:')
                ->withInput();
        }

        // Check if patient has active subscription
        $patient = Auth::user();
        $subscriptionService = app(\App\Modules\Plans\Services\SubscriptionService::class);
        $hasActiveSubscription = $subscriptionService->hasActiveSubscription($patient);

        // Calculate amounts
        $totalAmount = $hasActiveSubscription ? 0.00 : ($serviceType->patient_charge * $request->duration_days);
        $totalStaffPayout = app(IncentiveCalculatorService::class)->estimateProvisionalServicePayout(
            $staff,
            $serviceType,
            (int) $request->duration_days,
            $hasActiveSubscription
        );
        if ($totalStaffPayout <= 0) {
            $dailyStaffPayout = $staff->isNurse() ? $serviceType->nurse_payout : $serviceType->caregiver_payout;
            $totalStaffPayout = $request->duration_days * $dailyStaffPayout;
        }

        try {
            DB::beginTransaction();

            // Create service request with staff pre-assigned
            $serviceRequest = ServiceRequest::create([
                'patient_id' => Auth::id(),
                'service_type_id' => $request->service_type_id,
                'preferred_staff_type' => $staff->role,
                'preferred_staff_id' => $staff->id,
                'assigned_staff_id' => $staff->id, // Direct assignment
                'start_date' => $startDate,
                'end_date' => $endDate,
                'duration_days' => $request->duration_days,
                'total_amount' => $totalAmount,
                'total_staff_payout' => $totalStaffPayout,
                'prepaid_amount' => $hasActiveSubscription || $totalAmount <= 0 ? 0.00 : 0.00,
                'payment_status' => $hasActiveSubscription || $totalAmount <= 0 ? 'paid' : 'pending',
                'status' => 'pending_approval', // Staff needs to accept
                'assigned_at' => now(),
                'notes' => ($request->notes ?? '').($hasActiveSubscription ? ' [FREE - Covered by Subscription]' : ''),
                'special_requirements' => $request->special_requirements ?? null,
                'location' => $request->location,
                'contact_person' => $request->contact_person,
                'contact_phone' => $request->contact_phone,
            ]);

            // Refresh to ensure relationships can be loaded
            $serviceRequest->refresh();

            // Create daily service records
            $this->createDailyServiceRecords($serviceRequest);

            DB::commit();

            if ($hasActiveSubscription || $totalAmount <= 0) {
                try {
                    app(\App\Modules\Auth\Services\AppNotificationService::class)
                        ->notifyBookingCreated($serviceRequest->fresh(['patient', 'serviceType']) ?? $serviceRequest);
                } catch (\Throwable $e) {
                    report($e);
                }

                return redirect()->route('services.my-requests')
                    ->with('success', 'Booking created successfully! This service is FREE with your active subscription. The staff member will be notified.');
            }

            return redirect()->route('services.pay', $serviceRequest)
                ->with('success', 'Booking created. Pay ₹'.number_format($totalAmount, 0).' online to confirm — staff are notified after payment.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Log detailed error for debugging
            Log::error('Direct booking failed', [
                'staff_id' => $staff->id,
                'patient_id' => Auth::id(),
                'service_type_id' => $request->service_type_id ?? null,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Something went wrong. Please try again.')
                ->withInput();
        }
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
        if ($serviceRequest->patient_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        $serviceRequest->load(['serviceType', 'assignedStaff', 'dailyServices.staff']);

        return view('services::services.show', compact('serviceRequest'));
    }

    /**
     * Patient: pay visit fee online (Razorpay).
     */
    public function pay(ServiceRequest $serviceRequest)
    {
        if ((int) $serviceRequest->patient_id !== (int) Auth::id()) {
            abort(403);
        }

        $serviceRequest->load(['serviceType', 'preferredStaff', 'assignedStaff']);

        if ($serviceRequest->isCancelled()) {
            return redirect()->route('services.my-requests')
                ->with('error', 'This booking was cancelled.');
        }

        if ($serviceRequest->isVisitPaymentSettled()) {
            return redirect()->route('services.show', $serviceRequest)
                ->with('success', 'This visit is already paid.');
        }

        $razorpayEnabled = app(\App\Modules\Services\Services\ServiceVisitPaymentService::class)->isRazorpayEnabled();

        return view('services::services.pay', compact('serviceRequest', 'razorpayEnabled'));
    }

    /**
     * Create Razorpay order for a visit booking.
     */
    public function createVisitRazorpayOrder(ServiceRequest $serviceRequest)
    {
        try {
            $order = app(\App\Modules\Services\Services\ServiceVisitPaymentService::class)
                ->createOrder($serviceRequest, Auth::user());

            return response()->json([
                'success' => true,
                'order_id' => $order['order_id'],
                'key' => $order['key'],
                'amount' => $order['amount'],
                'currency' => $order['currency'],
                'service_request_id' => $serviceRequest->id,
                'customer' => [
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'contact' => preg_replace('/\D/', '', (string) Auth::user()->phone),
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Visit Razorpay order creation failed', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create payment order. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify Razorpay callback for a visit booking.
     */
    public function verifyVisitRazorpayPayment(Request $request, ServiceRequest $serviceRequest)
    {
        if ((int) $serviceRequest->patient_id !== (int) Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Razorpay callback payload.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            app(\App\Modules\Services\Services\ServiceVisitPaymentService::class)
                ->verifyAndMarkPaid($serviceRequest, $request->only([
                    'razorpay_order_id',
                    'razorpay_payment_id',
                    'razorpay_signature',
                ]), Auth::user());

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully. Staff will be notified.',
                'redirect_url' => route('services.my-requests'),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Visit Razorpay verify failed', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Please contact support if money was deducted.',
            ], 500);
        }
    }

    /**
     * Patient: cancel own pending / pending_approval service request.
     */
    public function cancel(Request $request, ServiceRequest $serviceRequest)
    {
        $user = Auth::user();

        if ($serviceRequest->patient_id !== $user->id) {
            abort(403);
        }

        if (! $user->isPatient()) {
            return redirect()->route('services.my-requests')
                ->with('error', 'Only patients can cancel their own service requests.');
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
                ->cancelByPatient($serviceRequest, $user, $request->input('cancellation_reason'));

            $refundNote = $cancelled->isRefundDue()
                ? ' If you paid a visit fee, MMHC will process your refund manually — our team will contact you.'
                : '';

            return redirect()->route('services.my-requests')
                ->with('success', 'Service request cancelled successfully.'.$refundNote);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Failed to cancel this request. Please try again.');
        }
    }

    /**
     * Admin: Display all service requests
     */
    public function adminIndex(Request $request)
    {
        // Get filter parameters
        $statusFilter = $request->get('status', 'all');
        $paymentFilter = $request->get('payment', 'all');
        $filterId = $request->get('filter'); // Specific ID filter from pending payments page

        $query = ServiceRequest::with(['patient', 'serviceType', 'assignedStaff', 'preferredStaff', 'approvedBy']);

        if ($paymentFilter === 'subscription') {
            $query->where('total_amount', '<=', 0)->where('payment_status', 'paid');
        } elseif ($paymentFilter === 'per_visit') {
            $query->where('total_amount', '>', 0);
        }

        // Filter by status
        if ($statusFilter !== 'all') {
            if ($statusFilter === 'completed' && $request->get('filter') === 'completed') {
                // Show completed requests that need approval
                $query->where('status', 'completed')
                    ->whereNull('admin_approved_at');
            } else {
                $query->where('status', $statusFilter);
            }
        }

        // Filter by specific ID (from pending payments link)
        if ($filterId) {
            $query->where('id', $filterId);
        }

        $serviceRequests = $query->orderBy('created_at', 'desc')->paginate(10);

        $stats = [
            'total_requests' => ServiceRequest::count(),
            'pending_requests' => ServiceRequest::pending()->count(),
            'assigned_requests' => ServiceRequest::assigned()->count(),
            'in_progress_requests' => ServiceRequest::inProgress()->count(),
            'completed_requests' => ServiceRequest::completed()->count(),
            'pending_approvals' => ServiceRequest::where('status', 'completed')
                ->whereNull('admin_approved_at')
                ->count(),
            'subscription_visits' => ServiceRequest::query()
                ->where('total_amount', '<=', 0)
                ->where('payment_status', 'paid')
                ->count(),
            'per_visit_requests' => ServiceRequest::query()->where('total_amount', '>', 0)->count(),
        ];

        return view('services::admin.requests.index', compact('serviceRequests', 'stats', 'statusFilter', 'paymentFilter', 'filterId'));
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
        if (! $staff->isStaff()) {
            return redirect()->back()
                ->with('error', 'Selected user is not a staff member.');
        }

        // CRITICAL FIX #1: Check for overlapping services to prevent double-booking
        $overlappingServices = ServiceRequest::where('assigned_staff_id', $staff->id)
            ->where('id', '!=', $serviceRequest->id) // Exclude current service if reassigning
            ->whereIn('status', ['assigned', 'in_progress']) // Only check active services
            ->where(function ($query) use ($serviceRequest) {
                // Check if new service dates overlap with existing services
                $query->where(function ($q) use ($serviceRequest) {
                    // New service starts during existing service
                    $q->whereBetween('start_date', [$serviceRequest->start_date, $serviceRequest->end_date])
                        ->orWhereBetween('end_date', [$serviceRequest->start_date, $serviceRequest->end_date])
                        ->orWhere(function ($subQ) use ($serviceRequest) {
                            // New service completely contains existing service
                            $subQ->where('start_date', '>=', $serviceRequest->start_date)
                                ->where('end_date', '<=', $serviceRequest->end_date);
                        })
                        ->orWhere(function ($subQ) use ($serviceRequest) {
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

        // Load service type to ensure it exists before calculating payout
        $serviceRequest->load('serviceType');
        $serviceType = $serviceRequest->serviceType;

        if (! $serviceType) {
            return redirect()->back()
                ->with('error', 'Service type not found. Please contact support.');
        }

        // Removed prepayment requirement - admin can assign staff without payment barrier

        $subscriptionService = app(\App\Modules\Plans\Services\SubscriptionService::class);
        $patient = $serviceRequest->patient;
        $hasSub = $patient && $subscriptionService->hasActiveSubscription($patient);
        $calc = app(IncentiveCalculatorService::class);
        $totalStaffPayout = $calc->estimateProvisionalServicePayout(
            $staff,
            $serviceType,
            (int) $serviceRequest->duration_days,
            $hasSub
        );
        if ($totalStaffPayout <= 0) {
            $dailyStaffPayout = $staff->isNurse() ? $serviceType->nurse_payout : $serviceType->caregiver_payout;
            $totalStaffPayout = $serviceRequest->duration_days * $dailyStaffPayout;
        }

        // CRITICAL FIX #3: Wrap in transaction for data integrity
        try {
            DB::beginTransaction();

            // CRITICAL FIX #5: Validate status transition
            if (! $serviceRequest->canTransitionTo('assigned')) {
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

            try {
                app(\App\Modules\Auth\Services\AppNotificationService::class)
                    ->notifyStaffAssigned($serviceRequest->fresh(['patient', 'serviceType']) ?? $serviceRequest);
            } catch (\Throwable $e) {
                report($e);
            }

            return redirect()->route('admin.service-requests')
                ->with('success', 'Staff assigned successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Staff assignment failed: '.$e->getMessage(), [
                'service_request_id' => $serviceRequest->id,
                'staff_id' => $request->assigned_staff_id,
                'error' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to assign staff. Please try again. If the problem persists, contact support.');
        }
    }

    /**
     * Admin: Record money collected from the patient (updates prepaid_amount for dashboard earning).
     */
    public function recordPatientCollection(Request $request, ServiceRequest $serviceRequest)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'collection_note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $total = (float) $serviceRequest->total_amount;
        if ($total <= 0) {
            return redirect()->back()
                ->with('error', 'This request has no patient charge (subscription or free visit).');
        }

        $remaining = $serviceRequest->balanceDue();
        if ($remaining <= 0) {
            return redirect()->back()
                ->with('info', 'Patient charge is already fully collected for this request.');
        }

        $increment = min($remaining, (float) $request->amount);
        $newPrepaid = round((float) $serviceRequest->prepaid_amount + $increment, 2);

        $paymentStatus = $newPrepaid >= $total ? 'paid' : 'partially_paid';

        $adminName = Auth::user()->name;
        $noteLine = sprintf(
            "\n[Patient payment ₹%s recorded by %s on %s%s]",
            number_format($increment, 2),
            $adminName,
            now()->format('Y-m-d H:i'),
            $request->filled('collection_note') ? ': '.$request->collection_note : ''
        );

        $serviceRequest->update([
            'prepaid_amount' => $newPrepaid,
            'payment_status' => $paymentStatus,
            'notes' => trim(($serviceRequest->notes ?? '').$noteLine),
        ]);

        return redirect()->back()
            ->with('success', sprintf(
                'Recorded ₹%s from patient. Collected ₹%s of ₹%s total.',
                number_format($increment, 2),
                number_format($newPrepaid, 2),
                number_format($total, 2)
            ));
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

            $serviceRequest->load(['assignedStaff', 'serviceType', 'patient']);
            $patient = $serviceRequest->patient;
            $subSvc = app(\App\Modules\Plans\Services\SubscriptionService::class);
            $isSub = $patient && $subSvc->hasActiveSubscription($patient);

            app(IncentiveCalculatorService::class)
                ->createOrUpdateServiceLedger(
                    $serviceRequest->assignedStaff,
                    $serviceRequest,
                    $isSub
                );
            $serviceRequest->refresh();

            // Approve payment
            $serviceRequest->update([
                'admin_approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            DB::commit();

            Log::info('Payment approved', [
                'service_request_id' => $serviceRequest->id,
                'approved_by' => Auth::id(),
                'amount' => $serviceRequest->total_staff_payout,
            ]);

            return redirect()->back()
                ->with('success', 'Payment approved successfully! Staff will see the earnings as approved.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment approval failed: '.$e->getMessage(), [
                'service_request_id' => $serviceRequest->id,
                'approved_by' => Auth::id(),
                'error' => $e->getTraceAsString(),
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
        // Only create daily services if status is 'assigned' or 'pending_approval'
        // For 'pending_approval', create them but mark as 'pending' status
        if (! in_array($serviceRequest->status, ['assigned', 'pending_approval', 'in_progress'])) {
            return; // Don't create daily services for pending/cancelled requests
        }

        // Ensure relationships are loaded
        $serviceRequest->load(['serviceType', 'assignedStaff']);

        $startDate = $serviceRequest->start_date;
        $endDate = $serviceRequest->end_date;
        $serviceType = $serviceRequest->serviceType;
        $staff = $serviceRequest->assignedStaff;

        // Null checks before accessing properties
        if (! $serviceType) {
            throw new \Exception("Service type not found for service request #{$serviceRequest->id}");
        }
        if (! $staff) {
            throw new \Exception("Assigned staff not found for service request #{$serviceRequest->id}");
        }

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

            // Keep one daily row per service request date to avoid duplicates on reassign/recreate flows.
            DailyService::updateOrCreate(
                [
                    'service_request_id' => $serviceRequest->id,
                    'service_date' => $date,
                ],
                [
                    'staff_id' => $staff->id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'patient_charge' => $serviceType->patient_charge,
                    'staff_payout' => $staffPayout,
                    'platform_profit' => $serviceType->patient_charge - $staffPayout,
                    'status' => 'scheduled',
                ]
            );
        }
    }
}

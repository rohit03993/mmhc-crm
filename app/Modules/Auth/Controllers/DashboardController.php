<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Auth\Services\LocationService;
use App\Modules\Payments\Models\StaffPayment;
use App\Modules\Payments\Services\StaffPayoutService;
use App\Modules\Plans\Models\Payment;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Plans\Services\StudentSubscriptionService;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Services\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(
        private StaffPayoutService $staffPayoutService
    ) {}

    /**
     * Show user dashboard
     */
    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('auth.login');
        }

        if ($user->role === 'student') {
            $studentSub = app(\App\Modules\Plans\Services\StudentSubscriptionService::class);
            if ($studentSub->requiresStudentMembership($user)) {
                return redirect()->route('student-subscription.offer');
            }

            return redirect()->route('academics.dashboard');
        }

        if ($user->hasAcademicRole()) {
            return redirect()->route('academics.dashboard');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isStaff()) {
            return redirect()->route('staff.dashboard');
        }

        if ($user->isPatient()) {
            return view('auth::dashboard', array_merge(
                [
                    'user' => $user,
                    'stats' => $this->getUserStats($user),
                    'recent_activity' => $this->getRecentActivity($user),
                ],
                $this->getPatientDashboardViewData($user)
            ));
        }

        return redirect()->route('community.index');
    }

    /**
     * Show admin dashboard
     */
    public function adminDashboard()
    {
        $user = Auth::user();

        // Get recent service requests for admin overview
        $recentServiceRequests = \App\Modules\Services\Models\ServiceRequest::with(['patient', 'serviceType', 'assignedStaff'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $data = [
            'user' => $user,
            'stats' => $this->getAdminStats(),
            'recent_activity' => $this->getAdminRecentActivity(),
            'recent_service_requests' => $recentServiceRequests,
        ];

        return view('auth::admin.dashboard', $data);
    }

    /**
     * Show pending payments page (money owed TO company by patients)
     */
    public function pendingPayments(Request $request)
    {
        $filterType = $request->get('type', 'all'); // 'subscriptions', 'services', 'all'

        $studentSubscriptionService = app(\App\Modules\Plans\Services\StudentSubscriptionService::class);

        $pendingSubscriptions = $this->pendingCustomerSubscriptionQuery()
            ->with(['user', 'plan'])
            ->orderByDesc('created_at')
            ->get()
            ->reject(fn ($subscription) => $studentSubscriptionService->isExcludedFromAdminPendingQueue($subscription))
            ->values();

        // Get pending service payments (unpaid balance)
        $pendingServices = \App\Modules\Services\Models\ServiceRequest::with(['patient', 'serviceType', 'assignedStaff'])
            ->where('status', '!=', 'cancelled')
            ->whereIn('status', ['assigned', 'in_progress', 'completed', 'pending'])
            ->whereRaw('COALESCE(total_amount, 0) > COALESCE(prepaid_amount, 0)')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($service) {
                $service->unpaid_balance = $service->total_amount - ($service->prepaid_amount ?? 0);

                return $service;
            });

        // Store original counts before filtering
        $totalSubscriptionsCount = $pendingSubscriptions->count();
        $totalServicesCount = $pendingServices->count();

        // Filter collections for display based on type
        $displaySubscriptions = ($filterType === 'all' || $filterType === 'subscriptions') ? $pendingSubscriptions : collect();
        $displayServices = ($filterType === 'all' || $filterType === 'services') ? $pendingServices : collect();

        // Calculate totals (from filtered collections for display)
        $totalPendingSubscriptions = $displaySubscriptions->sum('total_amount');
        $totalPendingServices = $displayServices->sum('unpaid_balance');
        $totalPending = $totalPendingSubscriptions + $totalPendingServices;

        return view('auth::admin.pending-payments', [
            'pendingSubscriptions' => $displaySubscriptions,
            'pendingServices' => $displayServices,
            'totalPendingSubscriptions' => $totalPendingSubscriptions,
            'totalPendingServices' => $totalPendingServices,
            'totalPending' => $totalPending,
            'filterType' => $filterType,
            'totalSubscriptionsCount' => $totalSubscriptionsCount,
            'totalServicesCount' => $totalServicesCount,
        ]);
    }

    /**
     * Drill-down: who paid how much (subscriptions or services), with profile links.
     */
    public function earningDetail(Request $request, string $type)
    {
        $history = app(\App\Modules\Plans\Services\SubscriptionPaymentHistoryService::class);
        $period = $request->get('period', 'all');
        if (! in_array($period, ['all', 'month'], true)) {
            $period = 'all';
        }

        $title = 'Earning detail';
        $subtitle = '';
        $totalAmount = 0.0;
        $ledgerIntegrity = null;
        $paginator = null;

        if ($type === 'student-subscriptions') {
            $title = 'Student membership payments';
            $subtitle = 'Invoice ledger only — each row is a completed payment record (matches dashboard total exactly).';
            $query = $history->subscriptionPaymentsQuery(true, $period);
            $ledgerIntegrity = $history->subscriptionLedgerIntegrity(true);
            $totalAmount = (float) (clone $query)->sum('amount');
            $paginator = $query->orderByDesc('paid_at')->orderByDesc('id')->paginate(25)->withQueryString();
        } elseif ($type === 'patient-subscriptions') {
            $title = 'Patient healthcare plan payments';
            $subtitle = 'Invoice ledger only — each row is a completed payment record (matches dashboard total exactly).';
            $query = $history->subscriptionPaymentsQuery(false, $period);
            $ledgerIntegrity = $history->subscriptionLedgerIntegrity(false);
            $totalAmount = (float) (clone $query)->sum('amount');
            $paginator = $query->orderByDesc('paid_at')->orderByDesc('id')->paginate(25)->withQueryString();
        } elseif ($type === 'services') {
            $title = 'Service request payments collected';
            $subtitle = 'Prepaid amounts actually received from patients (prepaid_amount > 0). Staff shown for context.';
            $query = $this->collectedServicePaymentsQuery();
            if ($period === 'month') {
                $query->where('created_at', '>=', now()->startOfMonth());
            }
            $totalAmount = (float) (clone $query)->sum('prepaid_amount');
            $paginator = $query->with(['patient', 'assignedStaff', 'serviceType'])
                ->orderByDesc('created_at')
                ->paginate(25)
                ->withQueryString();
        } elseif ($type === 'services-due') {
            $title = 'Service balances still due';
            $subtitle = 'Remaining amount patients still owe on open or completed visits.';
            $query = ServiceRequest::query()
                ->where('status', '!=', 'cancelled')
                ->whereIn('status', ['pending', 'assigned', 'in_progress', 'completed'])
                ->whereRaw('COALESCE(total_amount, 0) > COALESCE(prepaid_amount, 0)')
                ->with(['patient', 'assignedStaff', 'serviceType']);
            $totalAmount = (float) (clone $query)
                ->selectRaw('SUM(GREATEST(0, COALESCE(total_amount, 0) - COALESCE(prepaid_amount, 0))) as due')
                ->value('due');
            $paginator = $query->orderByDesc('created_at')->paginate(25)->withQueryString();
        } else {
            abort(404);
        }

        return view('auth::admin.financial-earning-detail', compact(
            'type',
            'title',
            'subtitle',
            'totalAmount',
            'paginator',
            'period',
            'ledgerIntegrity'
        ));
    }

    /**
     * Drill-down: staff payouts paid vs pending, by category (matches dashboard payout table).
     */
    public function payoutDetail(Request $request, string $type)
    {
        $labels = $this->payoutCategoryLabels();
        if (! isset($labels[$type])) {
            abort(404);
        }

        $status = $request->get('status', 'all');
        if (! in_array($status, ['all', 'paid', 'pending'], true)) {
            $status = 'all';
        }

        $period = $request->get('period', 'all');
        if (! in_array($period, ['all', 'month'], true)) {
            $period = 'all';
        }

        $title = $labels[$type];
        $subtitle = 'Money paid out to nurses and caregivers — totals match the dashboard payout row.';

        $paidQuery = StaffPayment::query()
            ->where('payment_type', $type)
            ->with(['staff:id,name,email,role,unique_id', 'admin:id,name']);

        if ($period === 'month') {
            $paidQuery->where('paid_at', '>=', now()->startOfMonth());
        }

        $paidTotal = (float) (clone $paidQuery)->sum('amount');
        $paidPaginator = ($status === 'all' || $status === 'paid')
            ? (clone $paidQuery)->orderByDesc('paid_at')->orderByDesc('id')->paginate(25, ['*'], 'paid_page')->withQueryString()
            : null;

        $pendingTotal = $this->staffPayoutService->sumGlobalPendingAmount($type);
        $pendingServicePaginator = null;
        $pendingRewardPaginator = null;
        $pendingLedgerPaginator = null;
        $pendingLegacyReferralPaginator = null;
        $pendingLegacySubscriptionPaginator = null;

        if ($status === 'all' || $status === 'pending') {
            if ($type === 'service_request') {
                $pendingServicePaginator = $this->staffPayoutService
                    ->globalPendingServiceRequestQuery()
                    ->with(['assignedStaff:id,name,role,unique_id', 'patient:id,name,role,unique_id', 'serviceType'])
                    ->orderByDesc('admin_approved_at')
                    ->orderByDesc('id')
                    ->paginate(25, ['*'], 'pending_page')
                    ->withQueryString();
            } elseif ($type === 'patient_reward') {
                $pendingRewardPaginator = $this->staffPayoutService
                    ->globalPendingPatientRewardQuery()
                    ->with(['user:id,name,role,unique_id'])
                    ->orderByDesc('verified_at')
                    ->orderByDesc('id')
                    ->paginate(25, ['*'], 'pending_page')
                    ->withQueryString();
            } elseif ($type === 'staff_referral') {
                $pendingLedgerPaginator = $this->staffPayoutService
                    ->globalPendingStaffReferralLedgerQuery()
                    ->with(['staff:id,name,role,unique_id'])
                    ->orderByDesc('created_at')
                    ->paginate(15, ['*'], 'ledger_page')
                    ->withQueryString();
                $pendingLegacyReferralPaginator = $this->staffPayoutService
                    ->globalPendingLegacyStaffReferralQuery()
                    ->with(['referrer:id,name,role,unique_id', 'referred:id,name,role'])
                    ->orderByDesc('completed_at')
                    ->paginate(15, ['*'], 'legacy_page')
                    ->withQueryString();
            } elseif ($type === 'subscription_referral') {
                $pendingLedgerPaginator = $this->staffPayoutService
                    ->globalPendingSubscriptionReferralLedgerQuery()
                    ->with(['staff:id,name,role,unique_id', 'sourceSubscription.plan', 'sourceSubscription.user:id,name'])
                    ->orderByDesc('created_at')
                    ->paginate(15, ['*'], 'ledger_page')
                    ->withQueryString();
                $pendingLegacySubscriptionPaginator = $this->staffPayoutService
                    ->globalPendingLegacySubscriptionReferralQuery()
                    ->with(['plan', 'user:id,name', 'referrer:id,name,role,unique_id'])
                    ->orderByDesc('created_at')
                    ->paginate(15, ['*'], 'legacy_page')
                    ->withQueryString();
            }
        }

        $displayTotal = match ($status) {
            'paid' => $paidTotal,
            'pending' => $pendingTotal,
            default => $paidTotal + $pendingTotal,
        };

        return view('auth::admin.financial-payout-detail', compact(
            'type',
            'title',
            'subtitle',
            'status',
            'period',
            'displayTotal',
            'paidTotal',
            'pendingTotal',
            'paidPaginator',
            'pendingServicePaginator',
            'pendingRewardPaginator',
            'pendingLedgerPaginator',
            'pendingLegacyReferralPaginator',
            'pendingLegacySubscriptionPaginator',
        ));
    }

    /**
     * @return array<string, string>
     */
    protected function payoutCategoryLabels(): array
    {
        return [
            'service_request' => 'Service visits',
            'patient_reward' => 'Patient rewards',
            'staff_referral' => 'Staff referrals',
            'subscription_referral' => 'Subscription referrals',
        ];
    }

    /**
     * Data required by auth::dashboard for patients (staff carousel, requests, pricing).
     *
     * @return array{
     *     available_nurses: Collection,
     *     available_caregivers: Collection,
     *     service_types: Collection,
     *     recent_requests: \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * }
     */
    protected function getPatientDashboardViewData(User $user): array
    {
        if (LocationService::hasUsableCoordinates(
            $user->latitude !== null ? (float) $user->latitude : null,
            $user->longitude !== null ? (float) $user->longitude : null
        )) {
            $availableNurses = LocationService::getNearbyStaffFromCoordinates(
                (float) $user->latitude,
                (float) $user->longitude,
                'nurse',
                50
            )->take(3);
            $availableCaregivers = LocationService::getNearbyStaffFromCoordinates(
                (float) $user->latitude,
                (float) $user->longitude,
                'caregiver',
                50
            )->take(3);
        } else {
        $pincode = trim((string) ($user->pincode ?? ''));

        if ($pincode !== '') {
            $availableNurses = LocationService::getNearbyStaff($pincode, 'nurse')->take(3);
            $availableCaregivers = LocationService::getNearbyStaff($pincode, 'caregiver')->take(3);
        } else {
            $availableNurses = User::query()
                ->where('role', 'nurse')
                ->where('is_active', true)
                ->orderBy('name')
                ->limit(3)
                ->get();
            $availableCaregivers = User::query()
                ->where('role', 'caregiver')
                ->where('is_active', true)
                ->orderBy('name')
                ->limit(3)
                ->get();
        }
        }

        $patientReferralService = app(\App\Modules\Referrals\Services\PatientSubscriptionReferralService::class);

        return [
            'available_nurses' => $availableNurses,
            'available_caregivers' => $availableCaregivers,
            'service_types' => ServiceType::active()->ordered()->get(),
            'recent_requests' => ServiceRequest::query()
                ->where('patient_id', $user->id)
                ->with(['serviceType', 'assignedStaff'])
                ->orderByDesc('created_at')
                ->paginate(5),
            'planReferralLink' => $patientReferralService->getPlanReferralLink($user),
            'referralStats' => $patientReferralService->getStats($user),
        ];
    }

    /**
     * Get user statistics (for patients)
     */
    protected function getUserStats($user)
    {
        if ($user->isPatient()) {
            $serviceRequests = \App\Modules\Services\Models\ServiceRequest::where('patient_id', $user->id);
            $allRequests = $serviceRequests->get();

            // Calculate total spent
            $totalSpent = $allRequests->sum('prepaid_amount');

            // Calculate average service duration
            $avgDuration = $allRequests->where('duration_days', '>', 0)->avg('duration_days');

            // Get favorite staff (most assigned)
            $favoriteStaff = null;
            $staffCounts = $allRequests
                ->whereNotNull('assigned_staff_id')
                ->groupBy('assigned_staff_id')
                ->map(function ($group) {
                    return $group->count();
                });

            if ($staffCounts->isNotEmpty()) {
                $favoriteStaff = $staffCounts->sortDesc()->keys()->first();
            }

            $favoriteStaffName = null;
            if ($favoriteStaff) {
                $staff = \App\Models\Core\User::find($favoriteStaff);
                $favoriteStaffName = $staff ? $staff->name : null;
            }

            // Get upcoming services
            $upcomingServices = $serviceRequests
                ->where('start_date', '>=', now()->startOfDay())
                ->whereIn('status', ['pending', 'assigned', 'pending_approval', 'in_progress'])
                ->count();

            $planVisitsCount = $allRequests->filter(fn ($request) => $request->isCoveredBySubscription())->count();

            $stats = [
                'profile_completion' => $this->calculateProfileCompletion($user),
                'total_requests' => $serviceRequests->count(),
                'active_requests' => $serviceRequests->whereIn('status', ['assigned', 'in_progress', 'pending_approval'])->count(),
                'completed_requests' => $serviceRequests->where('status', 'completed')->count(),
                'pending_requests' => $serviceRequests->where('status', 'pending')->count(),
                'total_spent' => $totalSpent,
                'plan_visits_count' => $planVisitsCount,
                'average_duration' => round($avgDuration ?? 0, 1),
                'favorite_staff' => $favoriteStaffName,
                'upcoming_services' => $upcomingServices,
            ];
        } else {
            $stats = [
                'profile_completion' => $this->calculateProfileCompletion($user),
                'total_requests' => 0,
                'active_requests' => 0,
                'completed_requests' => 0,
                'pending_requests' => 0,
                'total_spent' => 0,
                'average_duration' => 0,
                'favorite_staff' => null,
                'upcoming_services' => 0,
                'plan_visits_count' => 0,
            ];
        }

        return $stats;
    }

    /**
     * Get admin statistics
     */
    protected function getAdminStats()
    {
        return Cache::remember('admin_dashboard_stats', now()->addMinutes(2), function () {
            $usersByRole = User::query()
                ->selectRaw('role, COUNT(*) as aggregate')
                ->groupBy('role')
                ->pluck('aggregate', 'role');

            $totalNurses = (int) ($usersByRole['nurse'] ?? 0);
            $totalCaregivers = (int) ($usersByRole['caregiver'] ?? 0);
            $totalPatients = (int) ($usersByRole['patient'] ?? 0);
            $totalStudents = (int) ($usersByRole['student'] ?? 0);
            $totalFaculty = (int) ($usersByRole['faculty'] ?? 0);
            $totalInstitutionAdmins = (int) ($usersByRole['institution_admin'] ?? 0);
            $totalPlatformAdmins = (int) ($usersByRole['admin'] ?? 0);
            $totalStaff = $totalNurses + $totalCaregivers;
            $healthcareUsers = $totalStaff + $totalPatients;
            $academicUsers = $totalStudents + $totalFaculty + $totalInstitutionAdmins;
            $totalUsers = (int) User::count();

            $stats = [
                'total_users' => $totalUsers,
                'total_nurses' => $totalNurses,
                'total_caregivers' => $totalCaregivers,
                'total_patients' => $totalPatients,
                'total_students' => $totalStudents,
                'total_faculty' => $totalFaculty,
                'total_institution_admins' => $totalInstitutionAdmins,
                'total_platform_admins' => $totalPlatformAdmins,
                'total_staff' => $totalStaff,
                'healthcare_users' => $healthcareUsers,
                'academic_users' => $academicUsers,
                'users_accounted_for' => $healthcareUsers + $academicUsers + $totalPlatformAdmins,
                'pending_approvals' => ServiceRequest::where('status', 'completed')
                    ->whereNull('admin_approved_at')
                    ->count(),
                'total_service_requests' => ServiceRequest::count(),
                'pending_service_requests' => ServiceRequest::where('status', 'pending')->count(),
                'in_progress_services' => ServiceRequest::where('status', 'in_progress')->count(),
                'service_balance_due_count' => 0,
            ];

            $stats['financial'] = $this->getFinancialStats();

            return $stats;
        });
    }

    /**
     * Get financial statistics for admin dashboard
     */
    protected function getFinancialStats()
    {
        $subscriptionMetrics = app(\App\Modules\Plans\Services\SubscriptionPaymentHistoryService::class)
            ->getSubscriptionRevenueMetrics();

        $totalSubscriptionRevenue = $subscriptionMetrics['total_subscription_revenue'];
        $activeSubscriptionsCount = $subscriptionMetrics['active_subscriptions_count'];

        // 2. Service revenue = actual prepaid collected only (never catalogue total_amount fallback)
        $serviceRevenue = (float) $this->collectedServicePaymentsQuery()->sum('prepaid_amount');
        $servicePaymentsCount = $this->collectedServicePaymentsQuery()->count();

        // 3. Total Staff Payouts
        $totalStaffPayouts = \App\Modules\Payments\Models\StaffPayment::sum('amount');

        // 4. Net Profit (Revenue - Payouts)
        $netProfit = ($totalSubscriptionRevenue + $serviceRevenue) - $totalStaffPayouts;

        // 5. Pending Payments (Money OWED TO COMPANY by customers/patients)
        // Excludes abandoned student membership carts (pending, no payment started).
        $pendingSubscriptionPayments = (float) $this->pendingCustomerSubscriptionQuery()->sum('total_amount');

        // Visit fees are subscription-free or full per-visit fee at booking — no balance-due model.
        $pendingServicePayments = 0.0;

        $totalPendingPayments = $pendingSubscriptionPayments;

        $pendingSubscriptionsCount = $this->pendingCustomerSubscriptionQuery()->count();

        $studentPlanId = app(StudentSubscriptionService::class)->getStudentPlan()?->id;
        $pendingSubBase = $this->pendingCustomerSubscriptionQuery();
        $pendingStudentSubscriptions = $studentPlanId
            ? (float) (clone $pendingSubBase)->where('plan_id', $studentPlanId)->sum('total_amount')
            : 0.0;
        $pendingPatientSubscriptions = $studentPlanId
            ? (float) (clone $pendingSubBase)->where('plan_id', '!=', $studentPlanId)->sum('total_amount')
            : (float) $pendingSubscriptionPayments;

        $payoutBreakdown = $this->getStaffPayoutBreakdown();
        $totalEarning = $totalSubscriptionRevenue + $serviceRevenue;

        $pendingServiceRequestsCount = 0;

        // 6. This Month Revenue
        $thisMonthStart = now()->startOfMonth();

        $thisMonthSubscriptionRevenue = $subscriptionMetrics['this_month_subscription_revenue'];

        $thisMonthServiceRevenue = (float) $this->collectedServicePaymentsQuery()
            ->where('created_at', '>=', $thisMonthStart)
            ->sum('prepaid_amount');

        $thisMonthRevenue = $thisMonthSubscriptionRevenue + $thisMonthServiceRevenue;

        // 6. Pending Staff Payments (Money OWED TO STAFF by company)
        // Calculate total pending payments to all staff members
        $pendingStaffPayments = $this->calculatePendingStaffPayments();

        // Count of staff members with pending payments
        $staffWithPendingPayments = \App\Models\Core\User::whereIn('role', ['nurse', 'caregiver'])
            ->where('is_active', true)
            ->get()
            ->filter(function ($staff) {
                $payments = $this->calculatePendingPaymentsForStaff($staff);

                return $payments['total'] > 0;
            })
            ->count();

        // 7. Active Subscriptions Monthly Recurring Revenue (MRR)
        // Calculate monthly equivalent from active subscriptions
        $mrr = \App\Modules\Plans\Models\Subscription::where('status', 'active')
            ->get()
            ->sum(function ($subscription) {
                $frequency = $subscription->payment_frequency ?? 'monthly';
                $amount = $subscription->paid_amount > 0 ? $subscription->paid_amount : $subscription->total_amount;

                return match ($frequency) {
                    'monthly' => $amount,
                    'half_yearly' => $amount / 6,
                    'annually' => $amount / 12,
                    'full_payment' => 0, // One-time payment, no recurring
                    default => $amount / 12, // Default to monthly
                };
            });

        return [
            'total_subscription_revenue' => round($totalSubscriptionRevenue, 2),
            'student_subscription_revenue' => $subscriptionMetrics['student_subscription_revenue'],
            'patient_subscription_revenue' => $subscriptionMetrics['patient_subscription_revenue'],
            'this_month_student_subscription_revenue' => $subscriptionMetrics['this_month_student_subscription_revenue'],
            'this_month_patient_subscription_revenue' => $subscriptionMetrics['this_month_patient_subscription_revenue'],
            'recent_subscription_payments' => $subscriptionMetrics['recent_subscription_payments'],
            'active_subscriptions_count' => $activeSubscriptionsCount,
            'total_service_revenue' => round($serviceRevenue, 2),
            'service_payments_count' => $servicePaymentsCount,
            'total_earning' => round($totalEarning, 2),
            'total_revenue' => round($totalEarning, 2),
            'total_staff_payouts' => round($totalStaffPayouts, 2),
            'net_profit' => round($netProfit, 2),
            'pending_subscription_payments' => round($pendingSubscriptionPayments, 2),
            'pending_student_subscriptions' => round($pendingStudentSubscriptions, 2),
            'pending_patient_subscriptions' => round($pendingPatientSubscriptions, 2),
            'pending_service_payments' => round($pendingServicePayments, 2),
            'total_pending_payments' => round($totalPendingPayments, 2),
            'total_pending_to_collect' => round($totalPendingPayments, 2),
            'pending_subscriptions_count' => $pendingSubscriptionsCount,
            'pending_service_requests_count' => $pendingServiceRequestsCount,
            'pending_staff_payments' => round($pendingStaffPayments, 2),
            'staff_with_pending_payments' => $staffWithPendingPayments,
            'payout_breakdown' => $payoutBreakdown,
            'this_month_revenue' => round($thisMonthRevenue, 2),
            'this_month_subscription_revenue' => round($thisMonthSubscriptionRevenue, 2),
            'this_month_service_revenue' => round($thisMonthServiceRevenue, 2),
            'monthly_recurring_revenue' => round($mrr, 2),
            'student_ledger_integrity' => $subscriptionMetrics['student_ledger_integrity'],
            'patient_ledger_integrity' => $subscriptionMetrics['patient_ledger_integrity'],
        ];
    }

    /**
     * Staff payouts: paid (recorded) vs pending (owed), by category.
     *
     * @return array<string, mixed>
     */
    protected function getStaffPayoutBreakdown(): array
    {
        $types = [
            'service_request' => 'Service visits',
            'patient_reward' => 'Patient rewards',
            'staff_referral' => 'Staff referrals',
            'subscription_referral' => 'Subscription referrals',
        ];

        $paidLines = [];
        $paidTotal = 0.0;
        foreach (array_keys($types) as $type) {
            $amount = (float) StaffPayment::where('payment_type', $type)->sum('amount');
            $paidLines[$type] = $amount;
            $paidTotal += $amount;
        }

        $pendingLines = [];
        foreach (array_keys($types) as $type) {
            $pendingLines[$type] = ['amount' => 0.0, 'count' => 0];
        }

        foreach (User::query()->whereIn('role', ['nurse', 'caregiver'])->where('is_active', true)->cursor() as $staff) {
            $pending = $this->staffPayoutService->calculatePendingPayments($staff);
            foreach (array_keys($types) as $type) {
                $pendingLines[$type]['amount'] += (float) ($pending[$type]['amount'] ?? 0);
                $pendingLines[$type]['count'] += (int) ($pending[$type]['count'] ?? 0);
            }
        }

        $pendingTotal = 0.0;
        foreach ($pendingLines as $row) {
            $pendingTotal += $row['amount'];
        }

        return [
            'labels' => $types,
            'paid_lines' => $paidLines,
            'paid_total' => round($paidTotal, 2),
            'pending_lines' => $pendingLines,
            'pending_total' => round($pendingTotal, 2),
            'combined_total' => round($paidTotal + $pendingTotal, 2),
        ];
    }

    /**
     * Service visits where the patient actually prepaid money to MMHC.
     */
    protected function collectedServicePaymentsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return ServiceRequest::query()
            ->where('status', '!=', 'cancelled')
            ->where('prepaid_amount', '>', 0);
    }

    /**
     * Unpaid subscription checkouts that represent real money owed (not abandoned student carts).
     */
    protected function pendingCustomerSubscriptionQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $studentPlanId = app(StudentSubscriptionService::class)->getStudentPlan()?->id;

        return Subscription::query()
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->where('payment_status', '!=', 'paid')
                    ->orWhereNull('payment_status')
                    ->orWhere('payment_status', 'partially_paid');
            })
            ->where(function ($q) use ($studentPlanId) {
                if (! $studentPlanId) {
                    return;
                }

                $q->where('plan_id', '!=', $studentPlanId)
                    ->orWhereNotNull('payment_screenshot')
                    ->orWhereNotNull('transaction_id')
                    ->orWhereNotNull('razorpay_order_id');
            });
    }

    /**
     * Calculate total pending payments to all staff members
     */
    protected function calculatePendingStaffPayments()
    {
        $totalPending = 0;

        $staffMembers = \App\Models\Core\User::whereIn('role', ['nurse', 'caregiver'])
            ->where('is_active', true)
            ->get();

        foreach ($staffMembers as $staff) {
            $payments = $this->calculatePendingPaymentsForStaff($staff);
            $totalPending += $payments['total'];
        }

        return $totalPending;
    }

    /**
     * Calculate pending payments for a specific staff member
     * (Similar logic to AdminPaymentController::calculatePendingPayments)
     */
    protected function calculatePendingPaymentsForStaff($staff)
    {
        return $this->staffPayoutService->calculatePendingPayments($staff);
    }

    /**
     * Get recent activity for user
     */
    protected function getRecentActivity($user)
    {
        $activities = collect();

        if ($user->isPatient()) {
            // Get recent service requests
            $serviceRequests = \App\Modules\Services\Models\ServiceRequest::where('patient_id', $user->id)
                ->with(['serviceType', 'assignedStaff'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            foreach ($serviceRequests as $request) {
                // Service created
                $activities->push([
                    'type' => 'service_created',
                    'icon' => 'fa-calendar-plus',
                    'color' => 'primary',
                    'message' => 'Service request created: '.($request->serviceType->name ?? 'Service'),
                    'timestamp' => $request->created_at,
                    'link' => route('services.show', $request->id),
                ]);

                // Staff assigned
                if ($request->assignedStaff && $request->assigned_at) {
                    $activities->push([
                        'type' => 'staff_assigned',
                        'icon' => 'fa-user-check',
                        'color' => 'success',
                        'message' => $request->assignedStaff->name.' assigned to your service',
                        'timestamp' => $request->assigned_at,
                        'link' => route('services.show', $request->id),
                    ]);
                }

                // Service started
                if ($request->started_at) {
                    $activities->push([
                        'type' => 'service_started',
                        'icon' => 'fa-play-circle',
                        'color' => 'info',
                        'message' => 'Service started',
                        'timestamp' => $request->started_at,
                        'link' => route('services.show', $request->id),
                    ]);
                }

                // Service completed
                if ($request->completed_at) {
                    $activities->push([
                        'type' => 'service_completed',
                        'icon' => 'fa-check-circle',
                        'color' => 'success',
                        'message' => 'Service completed',
                        'timestamp' => $request->completed_at,
                        'link' => route('services.show', $request->id),
                    ]);
                }
            }

            // Account creation
            $activities->push([
                'type' => 'registration',
                'icon' => 'fa-user-plus',
                'color' => 'primary',
                'message' => 'Account created successfully',
                'timestamp' => $user->created_at ?? now(),
                'link' => null,
            ]);
        } else {
            // Default activity
            $activities->push([
                'type' => 'registration',
                'icon' => 'fa-user-plus',
                'color' => 'primary',
                'message' => 'Account created successfully',
                'timestamp' => $user->created_at ?? now(),
                'link' => null,
            ]);
        }

        // Sort by timestamp (most recent first) and limit to 8
        return $activities
            ->sortByDesc(fn (array $activity) => optional($activity['timestamp'])->getTimestamp() ?? 0)
            ->take(8)
            ->map(function (array $activity) {
                $activity['time'] = optional($activity['timestamp'])->diffForHumans() ?? 'Recently';

                return $activity;
            })
            ->values();
    }

    /**
     * Get recent activity for admin
     */
    protected function getAdminRecentActivity()
    {
        // This will be populated by other modules
        return [
            'type' => 'system',
            'message' => 'System initialized',
            'time' => now()->diffForHumans(),
        ];
    }

    /**
     * Calculate profile completion percentage
     */
    protected function calculateProfileCompletion($user)
    {
        $fields = ['name', 'email', 'phone', 'address', 'date_of_birth'];
        $completed = 0;

        foreach ($fields as $field) {
            if (! empty($user->$field)) {
                $completed++;
            }
        }

        return round(($completed / count($fields)) * 100);
    }
}

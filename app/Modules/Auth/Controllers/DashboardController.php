<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show user dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Redirect academic roles to academics module
        if ($user->hasAcademicRole()) {
            return redirect()->route('academics.dashboard');
        }
        
        // Redirect staff (nurses and caregivers) to their dedicated dashboard
        if ($user->isStaff()) {
            return redirect()->route('staff.dashboard');
        }
        
        // Redirect admin to admin dashboard
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        // For patients, show regular dashboard with service requests (paginated - max 10 per page)
        $serviceRequests = \App\Modules\Services\Models\ServiceRequest::where('patient_id', $user->id)
            ->with(['serviceType', 'assignedStaff'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Get available staff for dashboard display (limit to 3 for mobile-friendly display)
        // Sort by distance if patient has pincode
        $availableNurses = \App\Models\Core\User::where('role', 'nurse')
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(10)
            ->get();
        
        $availableCaregivers = \App\Models\Core\User::where('role', 'caregiver')
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(10)
            ->get();
        
        // If patient has pincode, sort by distance
        if ($user->isPatient() && $user->pincode) {
            $availableNurses = \App\Modules\Auth\Services\LocationService::getNearbyStaff($user->pincode, 'nurse')->take(3);
            $availableCaregivers = \App\Modules\Auth\Services\LocationService::getNearbyStaff($user->pincode, 'caregiver')->take(3);
        } else {
            $availableNurses = $availableNurses->take(3);
            $availableCaregivers = $availableCaregivers->take(3);
            
            // Add null distance for consistency
            $availableNurses = $availableNurses->map(function ($nurse) {
                $nurse->distance_km = null;
                return $nurse;
            });
            $availableCaregivers = $availableCaregivers->map(function ($caregiver) {
                $caregiver->distance_km = null;
                return $caregiver;
            });
        }
        
        // Get service types for pricing display
        $serviceTypes = \App\Modules\Services\Models\ServiceType::getActiveServiceTypes();
        
        // Get subscription information
        $subscriptionService = app(\App\Modules\Plans\Services\SubscriptionService::class);
        $activeSubscription = $subscriptionService->getActiveSubscription($user);
        $hasActiveSubscription = $subscriptionService->hasActiveSubscription($user);
        
        $data = [
            'user' => $user,
            'stats' => $this->getUserStats($user),
            'recent_activity' => $this->getRecentActivity($user),
            'recent_requests' => $serviceRequests, // Paginated collection (max 10 per page)
            'available_nurses' => $availableNurses,
            'available_caregivers' => $availableCaregivers,
            'service_types' => $serviceTypes,
            'active_subscription' => $activeSubscription,
            'has_active_subscription' => $hasActiveSubscription,
        ];

        return view('auth::dashboard', $data);
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

        // Get pending subscription payments
        $pendingSubscriptions = \App\Modules\Plans\Models\Subscription::with(['user', 'plan'])
            ->where(function($query) {
                $query->where('status', 'pending')
                      ->where(function($q) {
                          // Has payment proof but not verified
                          $q->where(function($q2) {
                              $q2->whereNotNull('payment_screenshot')
                                 ->orWhereNotNull('transaction_id');
                          })
                          ->where(function($q3) {
                              $q3->where('payment_status', '!=', 'paid')
                                 ->orWhereNull('payment_status')
                                 ->orWhere('payment_status', 'partially_paid');
                          });
                      })
                      // Or no payment proof yet
                      ->orWhere(function($q) {
                          $q->where('status', 'pending')
                            ->whereNull('payment_screenshot')
                            ->whereNull('transaction_id')
                            ->where(function($q2) {
                                $q2->where('payment_status', '!=', 'paid')
                                   ->orWhereNull('payment_status');
                            });
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Get pending service payments (unpaid balance)
        $pendingServices = \App\Modules\Services\Models\ServiceRequest::with(['patient', 'serviceType', 'assignedStaff'])
            ->where('status', '!=', 'cancelled')
            ->whereIn('status', ['assigned', 'in_progress', 'completed', 'pending'])
            ->whereRaw('COALESCE(total_amount, 0) > COALESCE(prepaid_amount, 0)')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($service) {
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
                ->whereIn('status', ['pending', 'assigned'])
                ->count();
            
            $stats = [
                'profile_completion' => $this->calculateProfileCompletion($user),
                'total_requests' => $serviceRequests->count(),
                'active_requests' => $serviceRequests->whereIn('status', ['assigned', 'in_progress'])->count(),
                'completed_requests' => $serviceRequests->where('status', 'completed')->count(),
                'pending_requests' => $serviceRequests->where('status', 'pending')->count(),
                'total_spent' => $totalSpent,
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
            ];
        }

        return $stats;
    }

    /**
     * Get admin statistics
     */
    protected function getAdminStats()
    {
        $stats = [
            'total_users' => \App\Models\Core\User::count(),
            'total_nurses' => \App\Models\Core\User::where('role', 'nurse')->count(),
            'total_caregivers' => \App\Models\Core\User::where('role', 'caregiver')->count(),
            'total_patients' => \App\Models\Core\User::where('role', 'patient')->count(),
            'total_staff' => \App\Models\Core\User::whereIn('role', ['nurse', 'caregiver'])->count(),
            'pending_approvals' => \App\Modules\Services\Models\ServiceRequest::where('status', 'completed')
                ->whereNull('admin_approved_at')
                ->count(),
            'total_service_requests' => \App\Modules\Services\Models\ServiceRequest::count(),
            'pending_service_requests' => \App\Modules\Services\Models\ServiceRequest::where('status', 'pending')->count(),
            'in_progress_services' => \App\Modules\Services\Models\ServiceRequest::where('status', 'in_progress')->count(),
        ];

        // Add financial statistics
        $stats['financial'] = $this->getFinancialStats();

        return $stats;
    }

    /**
     * Get financial statistics for admin dashboard
     */
    protected function getFinancialStats()
    {
        // 1. Subscription Revenue
        // Total revenue from subscriptions with paid status or active status with paid amount
        $totalSubscriptionRevenue = \App\Modules\Plans\Models\Subscription::where(function($query) {
                $query->where('payment_status', 'paid')
                      ->orWhere(function($q) {
                          $q->where('status', 'active')
                            ->whereNotNull('paid_amount')
                            ->where('paid_amount', '>', 0);
                      });
            })
            ->sum('paid_amount');
        
        // If paid_amount is not reliable, use total_amount as fallback
        if ($totalSubscriptionRevenue == 0) {
            $totalSubscriptionRevenue = \App\Modules\Plans\Models\Subscription::where(function($query) {
                    $query->where('payment_status', 'paid')
                          ->orWhere('status', 'active');
                })
                ->sum('total_amount');
        }

        // Active subscription revenue (only active subscriptions)
        $subscriptionRevenue = \App\Modules\Plans\Models\Subscription::where('status', 'active')
            ->where(function($query) {
                $query->whereNotNull('paid_amount')
                      ->where('paid_amount', '>', 0)
                      ->orWhereNotNull('total_amount')
                      ->where('total_amount', '>', 0);
            })
            ->sum('paid_amount');
        
        if ($subscriptionRevenue == 0) {
            $subscriptionRevenue = \App\Modules\Plans\Models\Subscription::where('status', 'active')
                ->sum('total_amount');
        }

        // Active subscriptions count
        $activeSubscriptionsCount = \App\Modules\Plans\Models\Subscription::where('status', 'active')->count();

        // 2. Service Revenue
        // Total revenue from service requests (prepaid_amount from completed/in_progress services)
        $serviceRevenue = \App\Modules\Services\Models\ServiceRequest::whereIn('status', ['assigned', 'in_progress', 'completed'])
            ->where('status', '!=', 'cancelled')
            ->sum('prepaid_amount');

        // Alternative: Use total_amount if prepaid_amount is not reliable
        if ($serviceRevenue == 0) {
            $serviceRevenue = \App\Modules\Services\Models\ServiceRequest::whereIn('status', ['assigned', 'in_progress', 'completed'])
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
        }

        // 3. Total Staff Payouts
        $totalStaffPayouts = \App\Modules\Payments\Models\StaffPayment::sum('amount');

        // 4. Net Profit (Revenue - Payouts)
        $netProfit = ($totalSubscriptionRevenue + $serviceRevenue) - $totalStaffPayouts;

        // 5. Pending Payments (Money OWED TO COMPANY by customers/patients)
        // IMPORTANT: This is money patients owe, NOT money owed to staff
        
        // Pending subscription payments: Subscriptions where payment proof submitted but not verified
        $pendingSubscriptionPayments = \App\Modules\Plans\Models\Subscription::where(function($query) {
                // Status is pending AND has payment proof (screenshot or transaction ID) but not verified
                $query->where('status', 'pending')
                      ->where(function($q) {
                          $q->whereNotNull('payment_screenshot')
                            ->orWhereNotNull('transaction_id');
                      })
                      ->where(function($q) {
                          $q->where('payment_status', '!=', 'paid')
                            ->orWhereNull('payment_status')
                            ->orWhere('payment_status', 'partially_paid');
                      });
            })
            ->sum('total_amount');

        // Also include subscriptions with status pending and no payment proof yet
        $pendingSubscriptionsNoProof = \App\Modules\Plans\Models\Subscription::where('status', 'pending')
            ->whereNull('payment_screenshot')
            ->whereNull('transaction_id')
            ->where(function($q) {
                $q->where('payment_status', '!=', 'paid')
                  ->orWhereNull('payment_status');
            })
            ->sum('total_amount');

        $pendingSubscriptionPayments += $pendingSubscriptionsNoProof;

        // Pending service payments: Services where patient hasn't paid fully (unpaid balance)
        $pendingServicePayments = optional(\App\Modules\Services\Models\ServiceRequest::where('status', '!=', 'cancelled')
            ->where(function($query) {
                // Service is assigned, in progress, or completed but payment not fully received
                $query->whereIn('status', ['assigned', 'in_progress', 'completed', 'pending'])
                      ->whereRaw('COALESCE(total_amount, 0) > COALESCE(prepaid_amount, 0)');
            })
            ->selectRaw('SUM(GREATEST(0, COALESCE(total_amount, 0) - COALESCE(prepaid_amount, 0))) as pending')
            ->first())->pending ?? 0;

        $totalPendingPayments = $pendingSubscriptionPayments + $pendingServicePayments;
        
        // Count of pending payment items for display
        $pendingSubscriptionsCount = \App\Modules\Plans\Models\Subscription::where(function($query) {
                $query->where('status', 'pending')
                      ->where(function($q) {
                          $q->where(function($q2) {
                              $q2->whereNotNull('payment_screenshot')
                                 ->orWhereNotNull('transaction_id');
                          })
                          ->where(function($q3) {
                              $q3->where('payment_status', '!=', 'paid')
                                 ->orWhereNull('payment_status')
                                 ->orWhere('payment_status', 'partially_paid');
                          });
                      })
                      ->orWhere(function($q) {
                          $q->where('status', 'pending')
                            ->whereNull('payment_screenshot')
                            ->whereNull('transaction_id')
                            ->where(function($q2) {
                                $q2->where('payment_status', '!=', 'paid')
                                   ->orWhereNull('payment_status');
                            });
                      });
            })
            ->count();
            
        $pendingServiceRequestsCount = \App\Modules\Services\Models\ServiceRequest::where('status', '!=', 'cancelled')
            ->whereIn('status', ['assigned', 'in_progress', 'completed', 'pending'])
            ->whereRaw('COALESCE(total_amount, 0) > COALESCE(prepaid_amount, 0)')
            ->count();

        // 6. This Month Revenue
        $thisMonthStart = now()->startOfMonth();
        
        // Subscriptions paid this month (use payment_verified_at or created_at as fallback)
        $thisMonthSubscriptionRevenue = \App\Modules\Plans\Models\Subscription::where(function($query) use ($thisMonthStart) {
                $query->where('payment_status', 'paid')
                      ->where(function($q) use ($thisMonthStart) {
                          $q->where('payment_verified_at', '>=', $thisMonthStart)
                            ->orWhere(function($q2) use ($thisMonthStart) {
                                $q2->whereNull('payment_verified_at')
                                   ->where('created_at', '>=', $thisMonthStart);
                            });
                      });
            })
            ->sum('paid_amount');

        // Services paid this month (use created_at as proxy for payment date, or admin_approved_at for completed services)
        $thisMonthServiceRevenue = \App\Modules\Services\Models\ServiceRequest::where(function($query) use ($thisMonthStart) {
                $query->where(function($q) use ($thisMonthStart) {
                        $q->where('created_at', '>=', $thisMonthStart)
                          ->whereIn('status', ['assigned', 'in_progress', 'completed'])
                          ->where('status', '!=', 'cancelled');
                    })
                    ->orWhere(function($q) use ($thisMonthStart) {
                        $q->where('admin_approved_at', '>=', $thisMonthStart)
                          ->where('status', 'completed');
                    });
            })
            ->sum('prepaid_amount');

        $thisMonthRevenue = $thisMonthSubscriptionRevenue + $thisMonthServiceRevenue;

        // 6. Pending Staff Payments (Money OWED TO STAFF by company)
        // Calculate total pending payments to all staff members
        $pendingStaffPayments = $this->calculatePendingStaffPayments();
        
        // Count of staff members with pending payments
        $staffWithPendingPayments = \App\Models\Core\User::whereIn('role', ['nurse', 'caregiver'])
            ->where('is_active', true)
            ->get()
            ->filter(function($staff) {
                $payments = $this->calculatePendingPaymentsForStaff($staff);
                return $payments['total'] > 0;
            })
            ->count();

        // 7. Active Subscriptions Monthly Recurring Revenue (MRR)
        // Calculate monthly equivalent from active subscriptions
        $mrr = \App\Modules\Plans\Models\Subscription::where('status', 'active')
            ->get()
            ->sum(function($subscription) {
                $frequency = $subscription->payment_frequency ?? 'monthly';
                $amount = $subscription->paid_amount > 0 ? $subscription->paid_amount : $subscription->total_amount;
                
                return match($frequency) {
                    'monthly' => $amount,
                    'half_yearly' => $amount / 6,
                    'annually' => $amount / 12,
                    'full_payment' => 0, // One-time payment, no recurring
                    default => $amount / 12, // Default to monthly
                };
            });

        return [
            'total_subscription_revenue' => round($totalSubscriptionRevenue, 2),
            'active_subscription_revenue' => round($subscriptionRevenue, 2),
            'active_subscriptions_count' => $activeSubscriptionsCount,
            'total_service_revenue' => round($serviceRevenue, 2),
            'total_revenue' => round($totalSubscriptionRevenue + $serviceRevenue, 2),
            'total_staff_payouts' => round($totalStaffPayouts, 2),
            'net_profit' => round($netProfit, 2),
            'pending_subscription_payments' => round($pendingSubscriptionPayments, 2),
            'pending_service_payments' => round($pendingServicePayments, 2),
            'total_pending_payments' => round($totalPendingPayments, 2),
            'pending_subscriptions_count' => $pendingSubscriptionsCount,
            'pending_service_requests_count' => $pendingServiceRequestsCount,
            'pending_staff_payments' => round($pendingStaffPayments, 2),
            'staff_with_pending_payments' => $staffWithPendingPayments,
            'this_month_revenue' => round($thisMonthRevenue, 2),
            'this_month_subscription_revenue' => round($thisMonthSubscriptionRevenue, 2),
            'this_month_service_revenue' => round($thisMonthServiceRevenue, 2),
            'monthly_recurring_revenue' => round($mrr, 2),
        ];
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
        $rewardService = app(\App\Modules\Rewards\Services\RewardService::class);

        // 1. Service Request Earnings (completed and approved, but not paid)
        $serviceEarnings = \App\Modules\Services\Models\ServiceRequest::where('assigned_staff_id', $staff->id)
            ->where('status', 'completed')
            ->whereNotNull('admin_approved_at')
            ->where('staff_payment_processed', false)
            ->sum('total_staff_payout') ?? 0;

        // 2. Patient Reward Earnings
        $patientRewardEarnings = \App\Modules\Rewards\Models\CaregiverReward::where('user_id', $staff->id)
            ->where('payment_processed', false)
            ->sum('reward_amount') ?? 0;

        // 3. Staff Referral – points only, not paid out (excluded from pending)
        $staffReferralEarnings = 0;

        // 4. Subscription Referral Earnings
        $subscriptionReferralEarnings = \App\Modules\Plans\Models\Subscription::where('referrer_id', $staff->id)
            ->where('status', 'active')
            ->where('referral_payment_processed', false)
            ->sum('referral_commission_amount') ?? 0;

        $total = $serviceEarnings + $patientRewardEarnings + $subscriptionReferralEarnings;

        return [
            'service_request' => ['amount' => $serviceEarnings],
            'patient_reward' => ['amount' => $patientRewardEarnings],
            'staff_referral' => ['amount' => 0],
            'subscription_referral' => ['amount' => $subscriptionReferralEarnings],
            'total' => $total,
        ];
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
                    'message' => 'Service request created: ' . ($request->serviceType->name ?? 'Service'),
                    'timestamp' => $request->created_at,
                    'link' => route('services.show', $request->id),
                ]);
                
                // Staff assigned
                if ($request->assignedStaff && $request->assigned_at) {
                    $activities->push([
                        'type' => 'staff_assigned',
                        'icon' => 'fa-user-check',
                        'color' => 'success',
                        'message' => $request->assignedStaff->name . ' assigned to your service',
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
                'timestamp' => $user->created_at,
                'link' => null,
            ]);
        } else {
            // Default activity
            $activities->push([
                'type' => 'registration',
                'icon' => 'fa-user-plus',
                'color' => 'primary',
                'message' => 'Account created successfully',
                'timestamp' => $user->created_at,
                'link' => null,
            ]);
        }
        
        // Sort by timestamp (most recent first) and limit to 8
        return $activities->sortByDesc('timestamp')->take(8)->map(function($activity) {
            $activity['time'] = $activity['timestamp']->diffForHumans();
            return $activity;
        })->values();
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
            if (!empty($user->$field)) {
                $completed++;
            }
        }

        return round(($completed / count($fields)) * 100);
    }
}

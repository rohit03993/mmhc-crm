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
        
        // Redirect staff (nurses and caregivers) to their dedicated dashboard
        if ($user->isStaff()) {
            return redirect()->route('staff.dashboard');
        }
        
        // Redirect admin to admin dashboard
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        // For patients, show regular dashboard with service requests
        $serviceRequests = \App\Modules\Services\Models\ServiceRequest::where('patient_id', $user->id)
            ->with(['serviceType', 'assignedStaff'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get available staff for dashboard display (limit to 6 for better UI)
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
        
        $data = [
            'user' => $user,
            'stats' => $this->getUserStats($user),
            'recent_activity' => $this->getRecentActivity($user),
            'recent_requests' => $serviceRequests,
            'available_nurses' => $availableNurses,
            'available_caregivers' => $availableCaregivers,
            'service_types' => $serviceTypes,
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
            $favoriteStaff = $allRequests
                ->whereNotNull('assigned_staff_id')
                ->groupBy('assigned_staff_id')
                ->map->count()
                ->sortDesc()
                ->keys()
                ->first();
            
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

        return $stats;
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

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
        
        $data = [
            'user' => $user,
            'stats' => $this->getUserStats($user),
            'recent_activity' => $this->getRecentActivity($user),
            'recent_requests' => $serviceRequests,
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
            
            $stats = [
                'profile_completion' => $this->calculateProfileCompletion($user),
                'total_requests' => $serviceRequests->count(),
                'active_requests' => $serviceRequests->whereIn('status', ['assigned', 'in_progress'])->count(),
                'completed_requests' => $serviceRequests->where('status', 'completed')->count(),
                'pending_requests' => $serviceRequests->where('status', 'pending')->count(),
            ];
        } else {
            $stats = [
                'profile_completion' => $this->calculateProfileCompletion($user),
                'total_requests' => 0,
                'active_requests' => 0,
                'completed_requests' => 0,
                'pending_requests' => 0,
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
        // This will be populated by other modules
        return [
            'type' => 'registration',
            'message' => 'Account created successfully',
            'time' => $user->created_at->diffForHumans(),
        ];
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

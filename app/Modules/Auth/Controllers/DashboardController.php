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
        
        $data = [
            'user' => $user,
            'stats' => $this->getUserStats($user),
            'recent_activity' => $this->getRecentActivity($user),
        ];

        return view('auth::dashboard', $data);
    }

    /**
     * Show admin dashboard
     */
    public function adminDashboard()
    {
        $user = Auth::user();
        
        $data = [
            'user' => $user,
            'stats' => $this->getAdminStats(),
            'recent_activity' => $this->getAdminRecentActivity(),
        ];

        return view('auth::admin.dashboard', $data);
    }

    /**
     * Get user statistics
     */
    protected function getUserStats($user)
    {
        $stats = [
            'profile_completion' => $this->calculateProfileCompletion($user),
            'total_requests' => 0, // Will be implemented in other modules
            'active_requests' => 0, // Will be implemented in other modules
            'completed_requests' => 0, // Will be implemented in other modules
        ];

        return $stats;
    }

    /**
     * Get admin statistics
     */
    protected function getAdminStats()
    {
        $stats = [
            'total_users' => \App\Models\Core\User::count(),
            'total_caregivers' => \App\Models\Core\User::where('role', 'caregiver')->count(),
            'total_patients' => \App\Models\Core\User::where('role', 'patient')->count(),
            'pending_approvals' => 0, // Will be implemented in other modules
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

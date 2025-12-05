<?php

namespace App\Modules\Services\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Services\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StaffController extends Controller
{
    /**
     * Display available staff (nurses and caregivers)
     * With pagination, search, filters, and sorting
     */
    public function index(Request $request)
    {
        // Get request parameters
        $patientPincode = $request->get('pincode');
        $search = $request->get('search');
        $experience = $request->get('experience');
        $qualification = $request->get('qualification');
        $distance = $request->get('distance');
        $sort = $request->get('sort', 'distance'); // distance, name, experience
        $perPage = 10; // Max 10 per page for mobile-friendly display
        
        $patient = auth()->user();
        
        // Get patient pincode from request, user profile, or service location
        if (!$patientPincode && $patient && $patient->pincode) {
            $patientPincode = $patient->pincode;
        }
        
        // Process nurses
        $nurses = $this->getStaffList('nurse', $patientPincode, $search, $experience, $qualification, $distance, $sort, $perPage, $request);
        
        // Process caregivers
        $caregivers = $this->getStaffList('caregiver', $patientPincode, $search, $experience, $qualification, $distance, $sort, $perPage, $request);
        
        // Get service types for pricing display
        $serviceTypes = ServiceType::active()->ordered()->get();
        
        return view('services::staff.index', compact(
            'nurses', 
            'caregivers', 
            'serviceTypes', 
            'patientPincode',
            'search',
            'experience',
            'qualification',
            'distance',
            'sort'
        ));
    }
    
    /**
     * Get staff list with filters, search, and pagination
     */
    private function getStaffList($role, $patientPincode, $search, $experience, $qualification, $distance, $sort, $perPage, $request)
    {
        // If patient pincode available, use LocationService
        if ($patientPincode) {
            $staff = \App\Modules\Auth\Services\LocationService::getNearbyStaff(
                $patientPincode, 
                $role,
                $distance ? (int)$distance : null
            );
            
            // Apply search filter
            if ($search) {
                $staff = $staff->filter(function($member) use ($search) {
                    return stripos($member->name, $search) !== false ||
                           stripos($member->unique_id, $search) !== false ||
                           stripos($member->qualification ?? '', $search) !== false;
                });
            }
            
            // Apply experience filter
            if ($experience) {
                $staff = $staff->filter(function($member) use ($experience) {
                    return $member->experience === $experience;
                });
            }
            
            // Apply qualification filter
            if ($qualification) {
                $staff = $staff->filter(function($member) use ($qualification) {
                    return stripos($member->qualification ?? '', $qualification) !== false;
                });
            }
            
            // Apply sorting
            if ($sort === 'name') {
                $staff = $staff->sortBy('name');
            } elseif ($sort === 'experience') {
                $staff = $staff->sortByDesc(function($member) {
                    // Extract numeric value from experience string (e.g., "5-10" -> 5)
                    if (preg_match('/(\d+)/', $member->experience ?? '', $matches)) {
                        return (int)$matches[1];
                    }
                    return 0;
                });
            } else {
                // Default: sort by distance (already sorted by LocationService)
                $staff = $staff->sortBy('distance_km');
            }
            
            // Reset collection keys
            $staff = $staff->values();
            
            // Manual pagination for collection
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $items = $staff->slice(($currentPage - 1) * $perPage, $perPage)->all();
            
            $paginatedStaff = new LengthAwarePaginator(
                $items,
                $staff->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
            
            return $paginatedStaff;
        } else {
            // No pincode - use query builder with pagination
            $query = User::where('role', $role)
                ->where('is_active', true)
                ->with('profile');
            
            // Apply search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('unique_id', 'like', "%{$search}%")
                      ->orWhere('qualification', 'like', "%{$search}%");
                });
            }
            
            // Apply experience filter
            if ($experience) {
                $query->where('experience', $experience);
            }
            
            // Apply qualification filter
            if ($qualification) {
                $query->where('qualification', 'like', "%{$qualification}%");
            }
            
            // Apply sorting
            if ($sort === 'name') {
                $query->orderBy('name');
            } elseif ($sort === 'experience') {
                // Sort by experience - need custom logic
                $query->orderByRaw("CAST(SUBSTRING_INDEX(experience, '-', 1) AS UNSIGNED) DESC");
            } else {
                $query->orderBy('name');
            }
            
            $staff = $query->paginate($perPage);
            
            // Add null distance for consistency
            $staff->getCollection()->transform(function($member) {
                $member->distance_km = null;
                return $member;
            });
            
            return $staff;
        }
    }
}

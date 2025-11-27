<?php

namespace App\Modules\Services\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Services\Models\ServiceType;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    /**
     * Display available staff (nurses and caregivers)
     * Sorted by distance from patient's location
     */
    public function index(Request $request)
    {
        $patientPincode = $request->get('pincode');
        $patient = auth()->user();
        
        // Get patient pincode from request, user profile, or service location
        if (!$patientPincode && $patient && $patient->pincode) {
            $patientPincode = $patient->pincode;
        }
        
        // If patient pincode available, sort by distance
        if ($patientPincode) {
            $nurses = \App\Modules\Auth\Services\LocationService::getNearbyStaff($patientPincode, 'nurse');
            $caregivers = \App\Modules\Auth\Services\LocationService::getNearbyStaff($patientPincode, 'caregiver');
        } else {
            // No pincode provided, show alphabetical order
            $nurses = User::where('role', 'nurse')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
            
            $caregivers = User::where('role', 'caregiver')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
            
            // Add distance as null for consistency
            $nurses = $nurses->map(function ($nurse) {
                $nurse->distance_km = null;
                return $nurse;
            });
            
            $caregivers = $caregivers->map(function ($caregiver) {
                $caregiver->distance_km = null;
                return $caregiver;
            });
        }
        
        // Get service types for pricing display
        $serviceTypes = ServiceType::active()->ordered()->get();
        
        return view('services::staff.index', compact('nurses', 'caregivers', 'serviceTypes', 'patientPincode'));
    }
}

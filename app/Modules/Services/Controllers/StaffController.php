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
     */
    public function index()
    {
        $nurses = User::where('role', 'nurse')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $caregivers = User::where('role', 'caregiver')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Get service types for pricing display
        $serviceTypes = ServiceType::active()->ordered()->get();
        
        return view('services::staff.index', compact('nurses', 'caregivers', 'serviceTypes'));
    }
}

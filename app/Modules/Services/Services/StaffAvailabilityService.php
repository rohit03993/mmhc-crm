<?php

namespace App\Modules\Services\Services;

use App\Models\Core\User;
use App\Modules\Services\Models\ServiceRequest;
use Carbon\Carbon;

class StaffAvailabilityService
{
    /**
     * Check if staff is available for given dates
     * 
     * @param User $staff
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $excludeRequestId Exclude this request ID (for updates)
     * @return array ['available' => bool, 'reason' => string, 'conflicting_requests' => array]
     */
    public static function checkAvailability(User $staff, Carbon $startDate, Carbon $endDate, ?int $excludeRequestId = null): array
    {
        // Check 1: Staff must be active
        if (!$staff->is_active) {
            return [
                'available' => false,
                'reason' => 'Staff member is not active.',
                'conflicting_requests' => []
            ];
        }

        // Check 2: Staff profile availability status
        // Load profile if not already loaded
        if (!$staff->relationLoaded('profile')) {
            $staff->load('profile');
        }
        $profile = $staff->profile;
        
        // If profile doesn't exist, create it with available status
        if (!$profile) {
            $profile = \App\Modules\Profiles\Models\Profile::create([
                'user_id' => $staff->id,
                'availability_status' => 'available',
            ]);
            $staff->setRelation('profile', $profile);
        }
        
        if ($profile->availability_status === 'unavailable') {
            return [
                'available' => false,
                'reason' => 'Staff member is currently unavailable.',
                'conflicting_requests' => []
            ];
        }

        // Check 3: Check for overlapping service requests
        $query = ServiceRequest::where('assigned_staff_id', $staff->id)
            ->whereIn('status', ['pending_approval', 'assigned', 'in_progress'])
            ->where(function($q) use ($startDate, $endDate) {
                // Check for any date overlap
                $q->where(function($subQ) use ($startDate, $endDate) {
                    // New service starts during existing service
                    $subQ->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->orWhere(function($innerQ) use ($startDate, $endDate) {
                            // New service completely contains existing service
                            $innerQ->where('start_date', '>=', $startDate->format('Y-m-d'))
                                   ->where('end_date', '<=', $endDate->format('Y-m-d'));
                        })
                        ->orWhere(function($innerQ) use ($startDate, $endDate) {
                            // Existing service completely contains new service
                            $innerQ->where('start_date', '<=', $startDate->format('Y-m-d'))
                                   ->where('end_date', '>=', $endDate->format('Y-m-d'));
                        });
                });
            });

        if ($excludeRequestId) {
            $query->where('id', '!=', $excludeRequestId);
        }

        $conflictingRequests = $query->with(['patient', 'serviceType'])->get();

        if ($conflictingRequests->count() > 0) {
            return [
                'available' => false,
                'reason' => 'Staff member is already booked during this period.',
                'conflicting_requests' => $conflictingRequests->toArray()
            ];
        }

        return [
            'available' => true,
            'reason' => 'Staff member is available.',
            'conflicting_requests' => []
        ];
    }

    /**
     * Get alternative available staff for same dates
     * 
     * @param string $staffRole 'nurse' or 'caregiver'
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $patientPincode
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAlternativeStaff(string $staffRole, Carbon $startDate, Carbon $endDate, ?string $patientPincode = null, int $limit = 5)
    {
        $query = User::where('role', $staffRole)
            ->where('is_active', true)
            ->with('profile');

        // Filter by availability status
        $query->whereHas('profile', function($q) {
            $q->where('availability_status', '!=', 'unavailable')
              ->orWhereNull('availability_status');
        });

        // Get all staff of this role
        $allStaff = $query->get();

        // Filter by availability
        $availableStaff = $allStaff->filter(function($staff) use ($startDate, $endDate) {
            $check = self::checkAvailability($staff, $startDate, $endDate);
            return $check['available'];
        });

        $patient = auth()->user();
        if ($patient && \App\Modules\Auth\Services\LocationService::hasUsableCoordinates(
            $patient->latitude !== null ? (float) $patient->latitude : null,
            $patient->longitude !== null ? (float) $patient->longitude : null
        )) {
            $availableStaff = \App\Modules\Auth\Services\LocationService::getNearbyStaffFromCoordinates(
                (float) $patient->latitude,
                (float) $patient->longitude,
                $staffRole
            )->filter(function ($staff) use ($availableStaff) {
                return $availableStaff->contains('id', $staff->id);
            });
        } elseif ($patientPincode) {
            $availableStaff = \App\Modules\Auth\Services\LocationService::getNearbyStaff(
                $patientPincode,
                $staffRole
            )->filter(function ($staff) use ($availableStaff) {
                return $availableStaff->contains('id', $staff->id);
            });
        }

        return $availableStaff->take($limit);
    }
}


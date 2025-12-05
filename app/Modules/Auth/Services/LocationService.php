<?php

namespace App\Modules\Auth\Services;

use App\Models\Pincode;

class LocationService
{
    /**
     * Extract pincode from address string
     * 
     * @param string $address
     * @return string|null
     */
    public static function extractPincode(string $address): ?string
    {
        // Pattern to match 6-digit pincode at the end of address or standalone
        // Indian pincodes: 6 digits, first digit 1-9
        $pattern = '/\b([1-9][0-9]{5})\b/';
        
        if (preg_match($pattern, $address, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Extract pincode and set coordinates for user
     * 
     * @param string $address
     * @return array|null Returns ['pincode', 'latitude', 'longitude'] or null
     */
    public static function extractLocationData(string $address): ?array
    {
        $pincode = self::extractPincode($address);
        
        if (!$pincode) {
            return null;
        }
        
        // Get coordinates from pincode database
        $pincodeData = Pincode::findByPincode($pincode);
        
        if ($pincodeData && $pincodeData->latitude && $pincodeData->longitude) {
            return [
                'pincode' => $pincode,
                'latitude' => (float) $pincodeData->latitude,
                'longitude' => (float) $pincodeData->longitude,
            ];
        }
        
        // Return pincode even if coordinates not found (coordinates can be added later)
        return [
            'pincode' => $pincode,
            'latitude' => null,
            'longitude' => null,
        ];
    }

    /**
     * Set location data for user based on address
     * 
     * @param \App\Models\Core\User $user
     * @param string|null $address
     * @return void
     */
    public static function setUserLocation(\App\Models\Core\User $user, ?string $address): void
    {
        if (!$address) {
            return;
        }
        
        $locationData = self::extractLocationData($address);
        
        if ($locationData) {
            $user->update([
                'pincode' => $locationData['pincode'],
                'latitude' => $locationData['latitude'],
                'longitude' => $locationData['longitude'],
            ]);
        }
    }

    /**
     * Calculate distance between user and target pincode
     * 
     * @param \App\Models\Core\User $user
     * @param string $targetPincode
     * @return float|null Distance in kilometers
     */
    public static function calculateDistanceFromUser(\App\Models\Core\User $user, string $targetPincode): ?float
    {
        if (!$user->pincode || !$user->latitude || !$user->longitude) {
            return null;
        }
        
        return Pincode::calculateDistance($user->pincode, $targetPincode);
    }

    /**
     * Calculate distance between two users
     * 
     * @param \App\Models\Core\User $user1
     * @param \App\Models\Core\User $user2
     * @return float|null Distance in kilometers
     */
    public static function calculateDistanceBetweenUsers(\App\Models\Core\User $user1, \App\Models\Core\User $user2): ?float
    {
        if (!$user1->pincode || !$user2->pincode) {
            return null;
        }
        
        return Pincode::calculateDistance($user1->pincode, $user2->pincode);
    }

    /**
     * Get staff members sorted by distance from patient pincode
     * 
     * @param string $patientPincode
     * @param string|null $staffRole 'nurse', 'caregiver', or null for both
     * @param int|null $maxDistanceKm Maximum distance in km (optional filter)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getNearbyStaff(string $patientPincode, ?string $staffRole = null, ?int $maxDistanceKm = null)
    {
        // Get patient coordinates
        $patientCoords = Pincode::getCoordinates($patientPincode);
        
        if (!$patientCoords) {
            // If patient pincode not found, return unsorted staff
            $query = \App\Models\Core\User::whereIn('role', ['nurse', 'caregiver'])
                ->where('is_active', true);
            
            if ($staffRole) {
                $query->where('role', $staffRole);
            }
            
            return $query->orderBy('name')->get();
        }
        
        // Get all active staff with profile
        $query = \App\Models\Core\User::whereIn('role', ['nurse', 'caregiver'])
            ->where('is_active', true)
            ->whereNotNull('pincode')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('profile');
        
        if ($staffRole) {
            $query->where('role', $staffRole);
        }
        
        $staff = $query->get();
        
        // Calculate distance for each staff member
        $staffWithDistance = $staff->map(function ($member) use ($patientCoords, $maxDistanceKm) {
            $distance = Pincode::haversineDistance(
                $patientCoords['latitude'],
                $patientCoords['longitude'],
                (float) $member->latitude,
                (float) $member->longitude
            );
            
            $member->distance_km = $distance;
            
            return $member;
        })->filter(function ($member) use ($maxDistanceKm) {
            // Filter by max distance if specified
            if ($maxDistanceKm !== null) {
                return $member->distance_km <= $maxDistanceKm;
            }
            return true;
        })->sortBy('distance_km'); // Sort by distance (nearest first)
        
        return $staffWithDistance->values(); // Re-index array
    }
}


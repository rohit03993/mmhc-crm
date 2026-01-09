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
     * Also updates spatial POINT column for optimized queries
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
            $updateData = [
                'pincode' => $locationData['pincode'],
                'latitude' => $locationData['latitude'],
                'longitude' => $locationData['longitude'],
            ];

            // Update spatial POINT column
            // Use sentinel POINT(0 0) if coordinates missing (required for NOT NULL constraint)
            if ($locationData['latitude'] && $locationData['longitude']) {
                $updateData['location'] = \DB::raw("ST_GeomFromText('POINT({$locationData['longitude']} {$locationData['latitude']})', 4326)");
            } else {
                // Use sentinel value POINT(0 0) for missing coordinates (required for NOT NULL constraint)
                $updateData['location'] = \DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");
            }

            $user->update($updateData);
        }
    }

    /**
     * Update spatial POINT column from latitude/longitude
     * 
     * @param float|null $latitude
     * @param float|null $longitude
     * @return \Illuminate\Database\Query\Expression|null
     */
    public static function createSpatialPoint(?float $latitude, ?float $longitude)
    {
        if ($latitude === null || $longitude === null) {
            return null;
        }

        // MySQL POINT format: POINT(longitude latitude) with SRID 4326 (WGS84)
        return \DB::raw("ST_GeomFromText('POINT({$longitude} {$latitude})', 4326)");
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
     * Uses spatial indexes for optimized database-level distance calculations
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

        // Create POINT for patient location using parameterized query (longitude, latitude order for MySQL)
        $longitude = (float) $patientCoords['longitude'];
        $latitude = (float) $patientCoords['latitude'];

        // Build query with spatial distance calculation
        // Filter out sentinel POINT(0 0) values (users without coordinates)
        $query = \App\Models\Core\User::whereIn('role', ['nurse', 'caregiver'])
            ->where('is_active', true)
            ->whereNotNull('latitude') // Ensure coordinates exist
            ->whereNotNull('longitude')
            ->whereRaw("location != ST_GeomFromText('POINT(0 0)', 4326)") // Exclude sentinel value
            ->with('profile');

        if ($staffRole) {
            $query->where('role', $staffRole);
        }

        // Calculate distance using ST_Distance_Sphere (returns meters, convert to km)
        // ST_Distance_Sphere is more accurate than ST_Distance for geographic coordinates
        // Using parameterized query to prevent SQL injection
        $query->selectRaw("
            users.*,
            ST_Distance_Sphere(
                location,
                ST_GeomFromText(?, 4326)
            ) / 1000 as distance_km
        ", ["POINT({$longitude} {$latitude})"]);

        // Filter by max distance if specified (in meters for ST_Distance_Sphere)
        if ($maxDistanceKm !== null) {
            $maxDistanceMeters = $maxDistanceKm * 1000;
            $query->whereRaw("
                ST_Distance_Sphere(
                    location,
                    ST_GeomFromText(?, 4326)
                ) <= ?
            ", ["POINT({$longitude} {$latitude})", $maxDistanceMeters]);
        }

        // Order by distance (nearest first)
        $query->orderByRaw('distance_km ASC');

        return $query->get();
    }
}


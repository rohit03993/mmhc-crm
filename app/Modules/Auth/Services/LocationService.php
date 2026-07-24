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
        $patientCoords = Pincode::getCoordinates($patientPincode);

        if (! $patientCoords) {
            $query = \App\Models\Core\User::whereIn('role', ['nurse', 'caregiver'])
                ->where('is_active', true);

            if ($staffRole) {
                $query->where('role', $staffRole);
            }

            return $query->orderBy('name')->get();
        }

        return self::getNearbyStaffFromCoordinates(
            (float) $patientCoords['latitude'],
            (float) $patientCoords['longitude'],
            $staffRole,
            $maxDistanceKm
        );
    }

    /**
     * Staff sorted by distance from the patient's GPS coordinates (no pincode required).
     */
    public static function getNearbyStaffFromCoordinates(
        float $latitude,
        float $longitude,
        ?string $staffRole = null,
        ?int $maxDistanceKm = null
    ) {
        $longitude = (float) $longitude;
        $latitude = (float) $latitude;
        $pointWkt = "POINT({$longitude} {$latitude})";

        $query = \App\Models\Core\User::whereIn('role', ['nurse', 'caregiver'])
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("location != ST_GeomFromText('POINT(0 0)', 4326)")
            ->with('profile');

        if ($staffRole) {
            $query->where('role', $staffRole);
        }

        $query->selectRaw('
            users.*,
            ST_Distance_Sphere(
                location,
                ST_GeomFromText(?, 4326)
            ) / 1000 as distance_km
        ', [$pointWkt]);

        if ($maxDistanceKm !== null) {
            $maxDistanceMeters = $maxDistanceKm * 1000;
            $query->whereRaw('
                ST_Distance_Sphere(
                    location,
                    ST_GeomFromText(?, 4326)
                ) <= ?
            ', [$pointWkt, $maxDistanceMeters]);
        }

        $query->orderByRaw('distance_km ASC');

        return $query->get();
    }

    /**
     * Store live GPS position on a user profile (patient or staff).
     * Updates existing latitude / longitude / location columns only.
     */
    public static function applyGpsCoordinatesToUser(\App\Models\Core\User $user, float $latitude, float $longitude): void
    {
        $user->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'location' => self::createSpatialPoint($latitude, $longitude),
        ]);
    }

    public static function hasUsableCoordinates(?float $latitude, ?float $longitude): bool
    {
        if ($latitude === null || $longitude === null) {
            return false;
        }

        if (abs($latitude) < 0.0001 && abs($longitude) < 0.0001) {
            return false;
        }

        return $latitude >= -90 && $latitude <= 90 && $longitude >= -180 && $longitude <= 180;
    }

    /**
     * Resolve GPS coordinates to the nearest pincode in our database.
     *
     * @return array{pincode: string, city: string|null, state: string|null, distance_km: float}|null
     */
    public static function resolveNearestPincode(float $latitude, float $longitude): ?array
    {
        return Pincode::nearestToCoordinates($latitude, $longitude);
    }

    /**
     * Save pincode + spatial point on a user (patient) from the pincode master table.
     */
    public static function applyPincodeToUser(\App\Models\Core\User $user, string $pincode): bool
    {
        $pincode = trim($pincode);
        if (! preg_match('/^[1-9][0-9]{5}$/', $pincode)) {
            return false;
        }

        $pincodeData = Pincode::findByPincode($pincode);
        if (! $pincodeData) {
            $user->update(['pincode' => $pincode]);

            return true;
        }

        $latitude = $pincodeData->latitude ? (float) $pincodeData->latitude : null;
        $longitude = $pincodeData->longitude ? (float) $pincodeData->longitude : null;

        $updateData = [
            'pincode' => $pincode,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        if ($latitude && $longitude) {
            $updateData['location'] = self::createSpatialPoint($latitude, $longitude);
        } else {
            $updateData['location'] = \DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");
        }

        $user->update($updateData);

        return true;
    }
}


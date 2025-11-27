<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pincode extends Model
{
    protected $fillable = [
        'pincode',
        'latitude',
        'longitude',
        'office_name',
        'city',
        'district',
        'state',
        'division',
        'region',
    ];

    /**
     * Get pincode by pincode string
     */
    public static function findByPincode(string $pincode): ?self
    {
        return static::where('pincode', $pincode)->first();
    }

    /**
     * Get coordinates for a pincode
     */
    public static function getCoordinates(string $pincode): ?array
    {
        $pincodeData = static::findByPincode($pincode);
        
        if ($pincodeData && $pincodeData->latitude && $pincodeData->longitude) {
            return [
                'latitude' => (float) $pincodeData->latitude,
                'longitude' => (float) $pincodeData->longitude,
                'city' => $pincodeData->city,
                'state' => $pincodeData->state,
            ];
        }
        
        return null;
    }

    /**
     * Calculate distance between two pincodes in kilometers
     */
    public static function calculateDistance(string $pincode1, string $pincode2): ?float
    {
        $coord1 = static::getCoordinates($pincode1);
        $coord2 = static::getCoordinates($pincode2);
        
        if (!$coord1 || !$coord2) {
            return null;
        }
        
        return static::haversineDistance(
            $coord1['latitude'],
            $coord1['longitude'],
            $coord2['latitude'],
            $coord2['longitude']
        );
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in kilometers
     */
    public static function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Radius of the Earth in kilometers
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return round($earthRadius * $c, 2);
    }

    /**
     * Find nearby pincodes within a radius
     */
    public static function findNearby(string $pincode, float $radiusKm = 50): array
    {
        $center = static::getCoordinates($pincode);
        
        if (!$center) {
            return [];
        }
        
        // Rough approximation: 1 degree latitude ≈ 111 km
        $latDelta = $radiusKm / 111;
        $lonDelta = $radiusKm / (111 * cos(deg2rad($center['latitude'])));
        
        $nearby = static::whereBetween('latitude', [
                $center['latitude'] - $latDelta,
                $center['latitude'] + $latDelta
            ])
            ->whereBetween('longitude', [
                $center['longitude'] - $lonDelta,
                $center['longitude'] + $lonDelta
            ])
            ->get();
        
        // Calculate exact distance and filter
        $results = [];
        foreach ($nearby as $pin) {
            $distance = static::haversineDistance(
                $center['latitude'],
                $center['longitude'],
                (float) $pin->latitude,
                (float) $pin->longitude
            );
            
            if ($distance <= $radiusKm) {
                $results[] = [
                    'pincode' => $pin->pincode,
                    'distance' => $distance,
                    'city' => $pin->city,
                    'state' => $pin->state,
                ];
            }
        }
        
        // Sort by distance
        usort($results, fn($a, $b) => $a['distance'] <=> $b['distance']);
        
        return $results;
    }
}


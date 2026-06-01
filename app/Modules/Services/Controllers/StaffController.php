<?php

namespace App\Modules\Services\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Auth\Services\LocationService;
use App\Modules\Services\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class StaffController extends Controller
{
    /** Default radius (km) when patient uses GPS location. */
    public const GPS_DEFAULT_DISTANCE_KM = 25;

    public function index(Request $request)
    {
        $search = $request->get('search');
        $experience = $request->get('experience');
        $qualification = $request->get('qualification');
        $distance = $request->get('distance');
        $locationFromGps = $request->get('location') === 'gps';
        $perPage = 10;

        $patient = auth()->user();
        $coords = $this->resolvePatientCoordinates($request, $patient);

        $patientLat = $coords['latitude'] ?? null;
        $patientLng = $coords['longitude'] ?? null;
        $hasPatientLocation = $coords !== null;

        if ($hasPatientLocation && $locationFromGps && ($distance === null || $distance === '')) {
            $distance = (string) self::GPS_DEFAULT_DISTANCE_KM;
        }

        $sort = $request->get('sort');
        if (! $sort) {
            $sort = $hasPatientLocation ? 'distance' : 'name';
        } elseif ($sort === 'distance' && ! $hasPatientLocation) {
            $sort = 'name';
        }

        $needsLocationSetup = $patient && $patient->isPatient() && ! $hasPatientLocation;

        $nurses = $this->getStaffList('nurse', $patientLat, $patientLng, $search, $experience, $qualification, $distance, $sort, $perPage, $request);
        $caregivers = $this->getStaffList('caregiver', $patientLat, $patientLng, $search, $experience, $qualification, $distance, $sort, $perPage, $request);

        $serviceTypes = ServiceType::active()->ordered()->get();

        return view('services::staff.index', compact(
            'nurses',
            'caregivers',
            'serviceTypes',
            'patientLat',
            'patientLng',
            'hasPatientLocation',
            'needsLocationSetup',
            'locationFromGps',
            'search',
            'experience',
            'qualification',
            'distance',
            'sort'
        ));
    }

    /**
     * Save browser GPS on the patient profile and return staff list URL.
     */
    public function resolveLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'save_to_profile' => 'sometimes|boolean',
        ]);

        $latitude = (float) $validated['latitude'];
        $longitude = (float) $validated['longitude'];
        $saveToProfile = $request->boolean('save_to_profile', true);

        if (! LocationService::hasUsableCoordinates($latitude, $longitude)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid location received. Please try again.',
            ], 422);
        }

        $patient = auth()->user();
        if ($saveToProfile && $patient && $patient->isPatient()) {
            try {
                LocationService::applyGpsCoordinatesToUser($patient, $latitude, $longitude);
            } catch (\Throwable $e) {
                Log::warning('Could not save patient GPS coordinates', [
                    'user_id' => $patient->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'message' => 'Showing nurses and caregivers nearest to your current location.',
            'redirect_url' => route('staff.index', [
                'lat' => round($latitude, 6),
                'lng' => round($longitude, 6),
                'distance' => self::GPS_DEFAULT_DISTANCE_KM,
                'sort' => 'distance',
                'location' => 'gps',
            ]),
        ]);
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    private function resolvePatientCoordinates(Request $request, ?User $patient): ?array
    {
        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = (float) $request->get('lat');
            $lng = (float) $request->get('lng');
            if (LocationService::hasUsableCoordinates($lat, $lng)) {
                return ['latitude' => $lat, 'longitude' => $lng];
            }
        }

        if ($patient && LocationService::hasUsableCoordinates(
            $patient->latitude !== null ? (float) $patient->latitude : null,
            $patient->longitude !== null ? (float) $patient->longitude : null
        )) {
            return [
                'latitude' => (float) $patient->latitude,
                'longitude' => (float) $patient->longitude,
            ];
        }

        return null;
    }

    private function getStaffList(
        $role,
        ?float $patientLat,
        ?float $patientLng,
        $search,
        $experience,
        $qualification,
        $distance,
        $sort,
        $perPage,
        $request
    ) {
        if ($patientLat !== null && $patientLng !== null) {
            $maxDistance = ($distance !== null && $distance !== '') ? (int) $distance : null;

            $staff = LocationService::getNearbyStaffFromCoordinates(
                $patientLat,
                $patientLng,
                $role,
                $maxDistance
            );

            if ($search) {
                $staff = $staff->filter(function ($member) use ($search) {
                    return stripos($member->name, $search) !== false
                        || stripos($member->unique_id, $search) !== false
                        || stripos($member->qualification ?? '', $search) !== false;
                });
            }

            if ($experience) {
                $staff = $staff->filter(fn ($member) => $member->experience === $experience);
            }

            if ($qualification) {
                $staff = $staff->filter(fn ($member) => stripos($member->qualification ?? '', $qualification) !== false);
            }

            if ($sort === 'name') {
                $staff = $staff->sortBy('name');
            } elseif ($sort === 'experience') {
                $staff = $staff->sortByDesc(function ($member) {
                    if (preg_match('/(\d+)/', $member->experience ?? '', $matches)) {
                        return (int) $matches[1];
                    }

                    return 0;
                });
            } else {
                $staff = $staff->sortBy('distance_km');
            }

            $staff = $staff->values();

            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $items = $staff->slice(($currentPage - 1) * $perPage, $perPage)->all();

            return new LengthAwarePaginator(
                $items,
                $staff->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
        }

        $query = User::where('role', $role)
            ->where('is_active', true)
            ->with('profile');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('unique_id', 'like', "%{$search}%")
                    ->orWhere('qualification', 'like', "%{$search}%");
            });
        }

        if ($experience) {
            $query->where('experience', $experience);
        }

        if ($qualification) {
            $query->where('qualification', 'like', "%{$qualification}%");
        }

        if ($sort === 'name') {
            $query->orderBy('name');
        } elseif ($sort === 'experience') {
            $query->orderByRaw("CAST(SUBSTRING_INDEX(experience, '-', 1) AS UNSIGNED) DESC");
        } else {
            $query->orderBy('name');
        }

        $staff = $query->paginate($perPage);

        $staff->getCollection()->transform(function ($member) {
            $member->distance_km = null;

            return $member;
        });

        return $staff;
    }
}

<?php

namespace App\Modules\Services\Support;

use App\Models\Core\User;

/**
 * Native-style mobile shell for patient & healthcare staff flows.
 */
class HealthcareMobileUi
{
    /** @var list<string> */
    private const PATIENT_ROUTE_PREFIXES = [
        'dashboard',
        'staff.',
        'services.',
        'book.',
        'subscriptions.',
        'plans.',
        'community.',
        'profile.',
        'documents.',
        'patient.referrals.',
    ];

    /** @var list<string> */
    private const STAFF_ROUTE_PREFIXES = [
        'staff.',
        'rewards.',
        'community.',
        'profile.',
        'documents.',
        'staff.payments.',
        'academics.open-classrooms.',
        'academics.mentorship.',
    ];

    public static function enabledFor(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $route = request()->route()?->getName() ?? '';

        if ($user->isPatient()) {
            return self::routeMatches($route, self::PATIENT_ROUTE_PREFIXES);
        }

        if ($user->isStaff()) {
            return self::routeMatches($route, self::STAFF_ROUTE_PREFIXES);
        }

        return false;
    }

    public static function htmlClass(?User $user): string
    {
        if (! self::enabledFor($user)) {
            return '';
        }

        if ($user->isPatient()) {
            return 'mmhc-healthcare-mobile mmhc-healthcare-patient';
        }

        return 'mmhc-healthcare-mobile mmhc-healthcare-staff';
    }

    public static function bodyClass(?User $user): string
    {
        if (! self::enabledFor($user)) {
            return '';
        }

        return $user->isPatient()
            ? 'mmhc-healthcare-app-shell mmhc-healthcare-role-patient'
            : 'mmhc-healthcare-app-shell mmhc-healthcare-role-staff';
    }

    public static function isDashboardRoute(): bool
    {
        return request()->routeIs('dashboard', 'staff.dashboard');
    }

    /**
     * @param  list<string>  $prefixes
     */
    private static function routeMatches(string $route, array $prefixes): bool
    {
        if ($route === '') {
            return false;
        }

        foreach ($prefixes as $prefix) {
            if ($route === $prefix || str_starts_with($route, $prefix)) {
                return true;
            }
        }

        return false;
    }
}

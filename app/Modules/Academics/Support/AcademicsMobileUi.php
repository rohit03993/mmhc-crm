<?php

namespace App\Modules\Academics\Support;

use Illuminate\Support\Facades\Route;

class AcademicsMobileUi
{
    public static function enabledFor(?\App\Models\Core\User $user): bool
    {
        return $user && in_array($user->role, ['institution_admin', 'faculty'], true);
    }

    public static function htmlClass(?\App\Models\Core\User $user): string
    {
        if (! self::enabledFor($user) || ! request()->routeIs('academics.*')) {
            return '';
        }

        return 'mmhc-academics-mobile';
    }

    public static function showMobileHeader(?\App\Models\Core\User $user): bool
    {
        if (! self::enabledFor($user) || ! request()->routeIs('academics.*')) {
            return false;
        }

        return ! request()->routeIs('academics.dashboard');
    }

    public static function backUrl(): string
    {
        $route = Route::currentRouteName();
        if ($route && str_contains($route, '.')) {
            $parts = explode('.', $route);
            if (count($parts) > 2) {
                $parent = $parts[0].'.'.$parts[1].'.index';
                if (Route::has($parent)) {
                    return route($parent);
                }
            }
        }

        return route('academics.dashboard');
    }
}

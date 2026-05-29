<?php

namespace App\Modules\Auth\Support;

use App\Models\Core\User;
use App\Modules\Plans\Services\StudentSubscriptionService;

/**
 * Single place for "where should this user land after login?" logic.
 */
class PostLoginRedirect
{
    public static function urlFor(?User $user): string
    {
        if (! $user) {
            return route('auth.login');
        }

        if (! $user->hasVerifiedPhone() && ! $user->isExemptFromPhoneVerification()) {
            return route('profile.verify-phone');
        }

        if ($user->role === 'admin') {
            return route('admin.dashboard');
        }

        if ($user->isStaff()) {
            return route('staff.dashboard');
        }

        if ($user->role === 'student') {
            $studentSub = app(StudentSubscriptionService::class);
            if ($studentSub->requiresStudentMembership($user)) {
                return route('student-subscription.offer');
            }

            return route('academics.dashboard');
        }

        if ($user->hasAcademicRole()) {
            return route('academics.dashboard');
        }

        if ($user->isPatient()) {
            return route('dashboard');
        }

        return route('community.index');
    }
}

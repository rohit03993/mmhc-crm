<?php

namespace App\Modules\Academics\Support;

use App\Models\Core\User;

/**
 * Central academics RBAC — platform admin provisions colleges; institute admin operates them.
 */
class AcademicsAccess
{
    /** @var list<string> */
    public const COLLEGE_OPERATOR_ROLES = ['institution_admin'];

    public static function isPlatformProvisioner(?User $user): bool
    {
        return $user !== null && $user->role === 'admin';
    }

    public static function isCollegeOperator(?User $user): bool
    {
        return $user !== null && $user->role === 'institution_admin';
    }

    public static function canViewInstitutionOverview(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        if (self::isPlatformProvisioner($user)) {
            return true;
        }

        if ($user->role === 'faculty' && $user->academic_institution_id) {
            return true;
        }

        return self::isCollegeOperator($user);
    }

    public static function platformMayMutateCollegeData(?User $user): bool
    {
        return self::isCollegeOperator($user);
    }

}

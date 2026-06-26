<?php

namespace App\Modules\Academics\Support;

use Illuminate\Support\Facades\Route;

/**
 * Native-style mobile shell for all academics roles on academics.* routes.
 */
class AcademicsMobileUi
{
    /** @var list<string> */
    public const ACADEMIC_ROLES = ['admin', 'institution_admin', 'faculty', 'student'];

    public static function enabledFor(?\App\Models\Core\User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (in_array($user->role, ['nurse', 'caregiver'], true)) {
            return request()->routeIs('academics.open-classrooms.*', 'academics.mentorship.*');
        }

        if (! in_array($user->role, self::ACADEMIC_ROLES, true)) {
            return false;
        }

        if (request()->routeIs(
            'academics.*',
            'community.*',
            'profile.*',
            'documents.*',
            'student-subscription.*',
        )) {
            return true;
        }

        // Student ₹12k membership checkout lives outside academics.*
        if ($user->role === 'student' && request()->routeIs('subscriptions.*')) {
            return true;
        }

        return false;
    }

    public static function htmlClass(?\App\Models\Core\User $user): string
    {
        if (! self::enabledFor($user)) {
            return '';
        }

        return 'mmhc-academics-mobile mmhc-academics-app';
    }

    public static function bodyClass(?\App\Models\Core\User $user): string
    {
        if (! self::enabledFor($user)) {
            return '';
        }

        $role = $user->role ?? '';

        return trim('mmhc-academics-app-shell mmhc-academics-role-'.$role);
    }

    public static function showMobileHeader(?\App\Models\Core\User $user): bool
    {
        if (! self::enabledFor($user)) {
            return false;
        }

        if (request()->routeIs('academics.dashboard', 'academics.exams.take', 'academics.exams.result')) {
            return false;
        }

        return true;
    }

    public static function isDashboard(): bool
    {
        return request()->routeIs('academics.dashboard');
    }

    public static function backUrl(): string
    {
        $route = Route::currentRouteName();
        if (! $route || ! str_starts_with($route, 'academics.')) {
            return route('academics.dashboard');
        }

        $map = [
            'academics.my-assignments.show' => 'academics.my-assignments',
            'academics.open-classrooms.show' => 'academics.open-classrooms.index',
            'academics.open-classrooms.create' => 'academics.open-classrooms.index',
            'academics.submit.store' => 'academics.my-assignments',
            'academics.topics.student-library' => 'academics.learning-resources',
            'academics.exams.take' => 'academics.exams.index',
            'academics.exams.result' => 'academics.exams.index',
            'academics.exams.show' => 'academics.exams.index',
            'academics.exams.edit' => 'academics.exams.index',
            'academics.exams.attempts' => 'academics.exams.index',
            'academics.assignments.submissions' => 'academics.assignments.index',
            'academics.enrollments.show' => 'academics.enrollments.index',
            'academics.institutions.edit' => 'academics.institutions.index',
            'academics.institutions.show' => 'academics.dashboard',
            'academics.batches.edit' => 'academics.batches.index',
            'academics.subjects.edit' => 'academics.subjects.index',
            'academics.topics.edit' => 'academics.topics.index',
            'academics.topics.resources.index' => 'academics.topics.index',
            'academics.topics.resources.create' => 'academics.topics.index',
            'academics.topics.resources.edit' => 'academics.topics.index',
            'academics.assignments.edit' => 'academics.assignments.index',
            'academics.assignments.show' => 'academics.assignments.index',
            'academics.mentorship.reviews.show' => 'academics.mentorship.index',
            'academics.mentorship.browse' => 'academics.mentorship.index',
            'academics.mentorship.profile' => 'academics.mentorship.index',
            'academics.reports.show' => 'academics.reports.index',
            'academics.reports.student' => 'academics.reports.index',
            'academics.attendance.mark' => 'academics.attendance.index',
            'academics.people.show' => 'academics.dashboard',
        ];

        if ($route === 'academics.submit.form') {
            $assignment = request()->route('assignment');
            if ($assignment) {
                return route('academics.my-assignments.show', $assignment);
            }
        }

        if ($route === 'academics.open-classrooms.assignments.submit') {
            $openClassroom = request()->route('openClassroom');
            $assignment = request()->route('assignment');
            if ($openClassroom && $assignment) {
                return route('academics.open-classrooms.assignments.show', [$openClassroom, $assignment]);
            }
        }

        $openClassroomBack = self::openClassroomBackUrl($route);
        if ($openClassroomBack !== null) {
            return $openClassroomBack;
        }

        if (isset($map[$route]) && Route::has($map[$route])) {
            return route($map[$route]);
        }

        if (str_contains($route, '.')) {
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

    public static function dashboardGreeting(?\App\Models\Core\User $user): string
    {
        if (! $user) {
            return 'Welcome';
        }

        $hour = (int) now()->format('G');
        $prefix = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        $first = explode(' ', trim($user->name))[0] ?? 'there';

        return $prefix.', '.$first;
    }

    /**
     * Back links for open-classroom routes that need {openClassroom} / {assignment} params.
     */
    protected static function openClassroomBackUrl(string $route): ?string
    {
        $openClassroom = request()->route('openClassroom');
        if (! $openClassroom) {
            return null;
        }

        $assignment = request()->route('assignment');

        return match ($route) {
            'academics.open-classrooms.edit' => route('academics.open-classrooms.show', $openClassroom),
            'academics.open-classrooms.assignments.show' => route('academics.open-classrooms.show', $openClassroom),
            'academics.open-classrooms.assignments.submissions' => $assignment
                ? route('academics.open-classrooms.assignments.show', [$openClassroom, $assignment])
                : route('academics.open-classrooms.show', $openClassroom),
            default => null,
        };
    }
}

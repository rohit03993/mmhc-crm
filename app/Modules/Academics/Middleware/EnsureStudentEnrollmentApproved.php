<?php

namespace App\Modules\Academics\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks students whose institute enrollment is still pending or rejected.
 */
class EnsureStudentEnrollmentApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if ($user && $user->role === 'student' && ! $user->hasApprovedAcademicEnrollment()) {
            return redirect()
                ->route('academics.dashboard')
                ->with('error', 'Your institute enrollment must be approved before you can access assignments and learning resources.');
        }

        return $next($request);
    }
}

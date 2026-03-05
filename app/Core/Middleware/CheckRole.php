<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return abort(403, 'Access denied. You must be authenticated.');
        }

        $user = auth()->user();
        $allowed = array_reduce($roles, function (array $carry, string $role) {
            return array_merge($carry, array_map('trim', explode(',', $role)));
        }, []);

        if (!in_array($user->role, $allowed)) {
            abort(403, 'Access denied. You do not have the required role.');
        }

        return $next($request);
    }
}

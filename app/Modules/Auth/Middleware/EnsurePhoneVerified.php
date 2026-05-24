<?php

namespace App\Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks authenticated users until their account mobile is SMS-verified.
 */
class EnsurePhoneVerified
{
    /** @var list<string> */
    protected array $allowedRouteNames = [
        'auth.logout',
        'auth.welcome.nursing-warrior',
        'profile.verify-phone',
        'profile.verify-phone.send',
        'profile.edit',
        'profile.update',
        'profile.verify-contact-otp',
        'profile.resend-contact-otp',
        'staff.referrals.verify-otp',
        'staff.referrals.resend-otp',
        'rewards.verify-otp-banner',
        'rewards.send-otp-banner',
        'student-subscription.offer',
        'student-subscription.subscribe',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || $user->hasVerifiedPhone() || $user->isExemptFromPhoneVerification()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if ($routeName && $this->isAllowedRoute($routeName)) {
            return $next($request);
        }

        if (! trim((string) ($user->phone ?? ''))) {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Add your mobile number in Profile, then verify it with SMS OTP to use the app.');
        }

        return redirect()
            ->route('profile.verify-phone')
            ->with('error', 'Verify your mobile number with SMS OTP to continue using MMHC.');
    }

    protected function isAllowedRoute(string $routeName): bool
    {
        return in_array($routeName, $this->allowedRouteNames, true);
    }

}

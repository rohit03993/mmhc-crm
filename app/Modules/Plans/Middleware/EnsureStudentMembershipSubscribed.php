<?php

namespace App\Modules\Plans\Middleware;

use App\Modules\Plans\Services\StudentSubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * After phone verification, students must complete the student journey membership payment.
 */
class EnsureStudentMembershipSubscribed
{
    /** @var list<string> */
    protected array $allowedRouteNames = [
        'auth.logout',
        'profile.index',
        'profile.edit',
        'profile.update',
        'profile.verify-phone',
        'profile.verify-phone.send',
        'profile.verify-contact-otp',
        'profile.resend-contact-otp',
        'profile.upload-avatar',
        'student-subscription.offer',
        'student-subscription.subscribe',
        'student-subscription.validate-coupon',
        'subscriptions.index',
        'subscriptions.show',
        'subscriptions.subscribe',
        'subscriptions.payment-confirmation',
        'subscriptions.invoice',
        'subscriptions.submit-payment',
        'subscriptions.razorpay.order',
        'subscriptions.razorpay.verify',
        'subscriptions.apply-coupon',
        'subscriptions.remove-coupon',
        'subscriptions.destroy',
        'subscriptions.payment-screenshot',
        'subscriptions.cancel',
        'subscriptions.renew',
        'storage.serve',
    ];

    /** @var list<string> */
    protected array $allowedRoutePrefixes = [
        'community.',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Uploaded files (community photos, avatars) must load even before membership payment
        if ($request->is('media-file')) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $service = app(StudentSubscriptionService::class);
        if (! $service->requiresStudentMembership($user)) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if ($routeName && $this->isAllowedRoute($routeName)) {
            return $next($request);
        }

        return redirect()
            ->route('student-subscription.offer')
            ->with('info', 'Please complete your student membership subscription to continue.');
    }

    protected function isAllowedRoute(string $routeName): bool
    {
        if (in_array($routeName, $this->allowedRouteNames, true)) {
            return true;
        }

        foreach ($this->allowedRoutePrefixes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        return false;
    }
}

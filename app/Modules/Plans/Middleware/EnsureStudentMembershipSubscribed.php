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
        'subscriptions.index',
        'subscriptions.show',
        'subscriptions.subscribe',
        'subscriptions.payment-confirmation',
        'subscriptions.submit-payment',
        'subscriptions.razorpay.order',
        'subscriptions.razorpay.verify',
        'subscriptions.destroy',
        'subscriptions.payment-screenshot',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $service = app(StudentSubscriptionService::class);
        if (! $service->requiresStudentMembership($user)) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if ($routeName && in_array($routeName, $this->allowedRouteNames, true)) {
            return $next($request);
        }

        return redirect()
            ->route('student-subscription.offer')
            ->with('info', 'Please complete your student membership subscription to continue.');
    }
}

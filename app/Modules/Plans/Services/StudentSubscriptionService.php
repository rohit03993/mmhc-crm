<?php

namespace App\Modules\Plans\Services;

use App\Models\Core\User;
use App\Modules\Plans\Models\Plan;
use App\Modules\Plans\Models\Subscription;

class StudentSubscriptionService
{
    public function isEnforcementEnabled(): bool
    {
        return (bool) config('student_subscription.enabled', true);
    }

    public function paymentFrequency(): string
    {
        return (string) config('student_subscription.payment_frequency', 'student_launch');
    }

    public function getStudentPlan(): ?Plan
    {
        $slug = (string) config('student_subscription.plan_slug', 'student-journey-launch');

        return Plan::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function requiresStudentMembership(User $user): bool
    {
        if (! $this->isEnforcementEnabled()) {
            return false;
        }

        if ($user->role !== 'student') {
            return false;
        }

        if (! $user->hasVerifiedPhone()) {
            return false;
        }

        return ! $this->hasActiveStudentMembership($user);
    }

    public function hasActiveStudentMembership(User $user): bool
    {
        if ($user->role !== 'student') {
            return false;
        }

        $plan = $this->getStudentPlan();
        if (! $plan) {
            return false;
        }

        return Subscription::query()
            ->where('user_id', $user->id)
            ->where('plan_id', $plan->id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->where('end_date', '>', now())
            ->exists();
    }

    public function getPendingStudentSubscription(User $user): ?Subscription
    {
        $plan = $this->getStudentPlan();
        if (! $plan) {
            return null;
        }

        return Subscription::query()
            ->where('user_id', $user->id)
            ->where('plan_id', $plan->id)
            ->where('status', 'pending')
            ->where('payment_status', '!=', 'paid')
            ->latest('id')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function offerDisplay(): array
    {
        return (array) config('student_subscription.display', []);
    }

    public function isStudentPlanSubscription(Subscription $subscription): bool
    {
        $plan = $this->getStudentPlan();

        return $plan && (int) $subscription->plan_id === (int) $plan->id;
    }

    public function postPaymentRedirectUrl(User $user, Subscription $subscription): string
    {
        if ($user->role === 'student' && $this->isStudentPlanSubscription($subscription) && $subscription->payment_status === 'paid') {
            return route('academics.dashboard');
        }

        return route('subscriptions.show', $subscription);
    }
}

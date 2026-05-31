<?php

namespace App\Modules\Plans\Services;

use App\Models\Core\User;
use App\Modules\Plans\Models\Plan;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Plans\Models\SubscriptionCoupon;
use Illuminate\Support\Facades\Schema;

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
        $query = Plan::query()->where('is_active', true);

        if (Schema::hasColumn('plans', 'slug')) {
            return (clone $query)->where('slug', $slug)->first();
        }

        // Fallback when slug migration has not been run yet
        return (clone $query)->where('name', 'Student Journey Membership')->first();
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

        $pending = Subscription::query()
            ->where('user_id', $user->id)
            ->where('plan_id', $plan->id)
            ->where('status', 'pending')
            ->where('payment_status', '!=', 'paid')
            ->latest('id')
            ->first();

        if (! $pending) {
            return null;
        }

        return $this->syncPendingCheckoutPricing($pending);
    }

    /**
     * Align unpaid student checkout with the current plan catalogue (e.g. after admin price change).
     */
    public function syncPendingCheckoutPricing(Subscription $pending): Subscription
    {
        if ($pending->payment_status === 'paid' || $pending->status !== 'pending') {
            return $pending;
        }

        if (! $this->isStudentPlanSubscription($pending)) {
            return $pending;
        }

        $previousTotal = (float) $pending->total_amount;
        $coupon = $pending->subscription_coupon_id
            ? SubscriptionCoupon::find($pending->subscription_coupon_id)
            : null;

        try {
            app(SubscriptionService::class)->reconcileSubscriptionFromPlanCatalogue($pending, false);
        } catch (\Throwable) {
            return $pending->fresh() ?? $pending;
        }

        $pending->refresh();

        if ($coupon && $coupon->is_active) {
            try {
                app(SubscriptionCouponService::class)->applyToSubscription($pending, $coupon);
            } catch (\Throwable) {
                $pending->update([
                    'subscription_coupon_id' => null,
                    'coupon_code' => null,
                    'amount_before_discount' => null,
                    'discount_amount' => 0,
                ]);
            }
        } else {
            $pending->update([
                'subscription_coupon_id' => null,
                'coupon_code' => null,
                'amount_before_discount' => null,
                'discount_amount' => 0,
                'total_amount' => $pending->total_amount,
                'base_amount' => $pending->base_amount,
            ]);
        }

        $pending->refresh();

        if (abs($previousTotal - (float) $pending->total_amount) >= 0.01) {
            $pending->update([
                'razorpay_order_id' => null,
                'gateway_status' => null,
            ]);
        }

        return $pending->fresh();
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

    public function allowsManualPayment(?Subscription $subscription = null): bool
    {
        if (! $subscription) {
            return app(SubscriptionPaymentService::class)->isRazorpayEnabled()
                ? (bool) config('student_subscription.manual_with_razorpay', false)
                : (bool) config('student_subscription.manual_payment_enabled', true);
        }

        return app(SubscriptionPaymentService::class)->allowsManualPayment($subscription);
    }

    public function shouldAutoActivateOnManualProof(): bool
    {
        return (bool) config('student_subscription.auto_activate_on_manual_proof', true);
    }

    /**
     * Student memberships with auto-activate skip the admin manual-verify queue.
     */
    public function isExcludedFromAdminPendingQueue(Subscription $subscription): bool
    {
        return $this->isStudentPlanSubscription($subscription)
            && $this->shouldAutoActivateOnManualProof();
    }

    public function postPaymentRedirectUrl(User $user, Subscription $subscription): string
    {
        if ($subscription->payment_status === 'paid') {
            $invoiceUrl = route('subscriptions.invoice', $subscription);
            if ($user->role === 'student' && $this->isStudentPlanSubscription($subscription)) {
                return $invoiceUrl.'?continue='.urlencode(route('academics.dashboard'));
            }

            return $invoiceUrl;
        }

        return route('subscriptions.show', $subscription);
    }
}

<?php

namespace App\Modules\Plans\Services;

use App\Modules\Plans\Models\Subscription;

/**
 * Patient & student subscription checkout: Razorpay by default; manual UPI only when configured.
 * (Staff payouts use manual-only — see config payments.staff_payout.)
 */
class SubscriptionPaymentService
{
    public function __construct(
        private RazorpayService $razorpayService,
        private StudentSubscriptionService $studentSubscriptionService
    ) {}

    public function isRazorpayEnabled(): bool
    {
        return $this->razorpayService->isEnabled();
    }

    public function isStudentMembershipSubscription(Subscription $subscription): bool
    {
        return $this->studentSubscriptionService->isStudentPlanSubscription($subscription);
    }

    /**
     * Whether UPI + screenshot path is shown on payment-confirmation.
     */
    public function allowsManualPayment(Subscription $subscription): bool
    {
        $isStudent = $this->isStudentMembershipSubscription($subscription);

        if ($this->isRazorpayEnabled()) {
            if ($isStudent) {
                return (bool) config('student_subscription.manual_payment_enabled', true)
                    && (bool) config('student_subscription.manual_with_razorpay', false);
            }

            return (bool) config('payments.subscription.manual_enabled', false)
                && (bool) config('payments.subscription.manual_with_razorpay', false);
        }

        if ($isStudent) {
            return (bool) config('student_subscription.manual_payment_enabled', true);
        }

        return (bool) config('payments.subscription.manual_enabled', false);
    }
}

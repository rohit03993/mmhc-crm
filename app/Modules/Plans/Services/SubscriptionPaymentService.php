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
     * When Razorpay is enabled, checkout is online-only (no manual form).
     */
    public function allowsManualPayment(Subscription $subscription): bool
    {
        if ($this->isRazorpayEnabled()) {
            return false;
        }

        $isStudent = $this->isStudentMembershipSubscription($subscription);

        if ($isStudent) {
            return (bool) config('student_subscription.manual_payment_enabled', false);
        }

        return (bool) config('payments.subscription.manual_enabled', false);
    }
}

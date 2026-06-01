<?php

namespace App\Modules\Plans\Services;

use App\Models\Core\User;
use App\Modules\Plans\Models\Payment;
use App\Modules\Plans\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SubscriptionPaymentHistoryService
{
    public function __construct(
        protected StudentSubscriptionService $studentSubscriptionService,
        protected SubscriptionInvoiceService $invoiceService,
    ) {}

    /**
     * Subscriptions where money was collected (recognized revenue).
     */
    public function paidSubscriptionsQuery(): Builder
    {
        return Subscription::query()
            ->where('payment_status', 'paid');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getPaidSubscriptionsForUser(User $user): Collection
    {
        return $this->paidSubscriptionsQuery()
            ->where('user_id', $user->id)
            ->with([
                'plan',
                'paymentVerifiedBy:id,name,role',
                'payments' => fn ($q) => $q->where('status', 'completed')->latest('id'),
            ])
            ->orderByDesc('payment_verified_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Subscription $subscription) => $this->formatHistoryRow($subscription));
    }

    /**
     * @return array<string, mixed>
     */
    public function formatHistoryRow(Subscription $subscription): array
    {
        $payment = $subscription->payments->first()
            ?? $this->invoiceService->ensurePaymentRecord($subscription->fresh() ?? $subscription);

        $isStudent = $this->studentSubscriptionService->isStudentPlanSubscription($subscription);

        return [
            'subscription' => $subscription,
            'payment' => $payment,
            'type_label' => $isStudent ? 'Student membership' : 'Healthcare subscription',
            'plan_name' => $subscription->plan->name ?? '—',
            'list_amount' => (float) ($subscription->amount_before_discount ?? $subscription->total_amount),
            'discount_amount' => (float) ($subscription->discount_amount ?? 0),
            'coupon_code' => $subscription->coupon_code,
            'paid_amount' => (float) ($subscription->paid_amount ?? $subscription->total_amount),
            'paid_at' => $subscription->payment_verified_at,
            'method_label' => $this->getPaymentMethodLabel($subscription),
            'verified_by_label' => $this->getVerificationLabel($subscription),
            'transaction_id' => $subscription->razorpay_payment_id ?: $subscription->transaction_id,
            'invoice_url' => $subscription->payment_status === 'paid'
                ? route('subscriptions.invoice', $subscription)
                : null,
            'admin_detail_url' => route('admin.subscriptions.view', $subscription),
        ];
    }

    public function getPaymentMethodLabel(Subscription $subscription): string
    {
        $provider = strtolower((string) ($subscription->payment_provider ?? ''));

        if ($provider === 'razorpay' || $subscription->razorpay_payment_id) {
            return 'Razorpay';
        }

        if ($subscription->payment_screenshot || $subscription->transaction_id) {
            return 'Manual (UPI / proof)';
        }

        return ucfirst($provider ?: '—');
    }

    public function getVerificationLabel(Subscription $subscription): string
    {
        if ($subscription->payment_provider === 'razorpay' || $subscription->razorpay_payment_id) {
            return 'Razorpay (automatic)';
        }

        $verifier = $subscription->relationLoaded('paymentVerifiedBy')
            ? $subscription->paymentVerifiedBy
            : $subscription->paymentVerifiedBy()->first();

        if ($verifier && in_array($verifier->role, ['admin', 'institution_admin'], true)) {
            return 'Admin: '.$verifier->name;
        }

        if ($verifier) {
            return $verifier->name;
        }

        return '—';
    }

    /**
     * Student membership summary for profile (paid + active context).
     *
     * @return array<string, mixed>|null
     */
    public function getStudentMembershipSummary(User $user): ?array
    {
        if ($user->role !== 'student') {
            return null;
        }

        $active = null;
        if ($this->studentSubscriptionService->hasActiveStudentMembership($user)) {
            $plan = $this->studentSubscriptionService->getStudentPlan();
            if ($plan) {
                $active = Subscription::query()
                    ->where('user_id', $user->id)
                    ->where('plan_id', $plan->id)
                    ->where('status', 'active')
                    ->where('payment_status', 'paid')
                    ->with('plan')
                    ->orderByDesc('end_date')
                    ->first();
            }
        }

        $studentPlan = $this->studentSubscriptionService->getStudentPlan();

        $latestPaid = $this->paidSubscriptionsQuery()
            ->where('user_id', $user->id)
            ->when($studentPlan, fn ($q) => $q->where('plan_id', $studentPlan->id))
            ->with('plan')
            ->orderByDesc('payment_verified_at')
            ->first();

        return [
            'active' => $active,
            'latest_paid' => $latestPaid,
            'has_paid_membership' => $latestPaid !== null,
            'invoice_url' => $latestPaid && $latestPaid->payment_status === 'paid'
                ? route('subscriptions.invoice', $latestPaid)
                : null,
        ];
    }

    /**
     * Subscription revenue metrics for admin dashboard (recognized = paid_amount).
     *
     * @return array<string, mixed>
     */
    public function getSubscriptionRevenueMetrics(): array
    {
        $base = $this->paidSubscriptionsQuery();
        $studentPlanId = $this->studentSubscriptionService->getStudentPlan()?->id;

        $totalSubscriptionRevenue = (float) (clone $base)->sum('paid_amount');

        $studentRevenue = $studentPlanId
            ? (float) (clone $base)->where('plan_id', $studentPlanId)->sum('paid_amount')
            : 0.0;

        $patientRevenue = $studentPlanId
            ? (float) (clone $base)->where('plan_id', '!=', $studentPlanId)->sum('paid_amount')
            : $totalSubscriptionRevenue;

        $thisMonthStart = now()->startOfMonth();
        $thisMonthSubscriptionRevenue = (float) (clone $base)
            ->where('payment_verified_at', '>=', $thisMonthStart)
            ->sum('paid_amount');

        $thisMonthStudentRevenue = $studentPlanId
            ? (float) (clone $base)
                ->where('plan_id', $studentPlanId)
                ->where('payment_verified_at', '>=', $thisMonthStart)
                ->sum('paid_amount')
            : 0.0;

        $thisMonthPatientRevenue = $studentPlanId
            ? (float) (clone $base)
                ->where('plan_id', '!=', $studentPlanId)
                ->where('payment_verified_at', '>=', $thisMonthStart)
                ->sum('paid_amount')
            : $thisMonthSubscriptionRevenue;

        $activeSubscriptionsCount = Subscription::where('status', 'active')->count();

        $recentPayments = Payment::query()
            ->where('status', 'completed')
            ->with(['user:id,name,email', 'subscription.plan'])
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return [
            'total_subscription_revenue' => round($totalSubscriptionRevenue, 2),
            'student_subscription_revenue' => round($studentRevenue, 2),
            'patient_subscription_revenue' => round($patientRevenue, 2),
            'active_subscriptions_count' => $activeSubscriptionsCount,
            'this_month_subscription_revenue' => round($thisMonthSubscriptionRevenue, 2),
            'this_month_student_subscription_revenue' => round($thisMonthStudentRevenue, 2),
            'this_month_patient_subscription_revenue' => round($thisMonthPatientRevenue, 2),
            'recent_subscription_payments' => $recentPayments,
        ];
    }

    public static function bustAdminDashboardCache(): void
    {
        Cache::forget('admin_dashboard_stats');
    }
}

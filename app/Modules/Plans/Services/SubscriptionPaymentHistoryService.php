<?php

namespace App\Modules\Plans\Services;

use App\Models\Core\User;
use App\Modules\Plans\Models\Payment;
use App\Modules\Plans\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Financial truth for subscription income: completed rows in the payments table (invoice ledger).
 * subscriptions.paid_amount alone is not used for dashboard totals — it can include demo/legacy rows without invoices.
 */
class SubscriptionPaymentHistoryService
{
    public function __construct(
        protected StudentSubscriptionService $studentSubscriptionService,
    ) {}

    /**
     * Invoice ledger: money actually recorded with invoice/receipt numbers.
     */
    public function completedPaymentsQuery(): Builder
    {
        return Payment::query()->where('status', 'completed');
    }

    public function studentPlanId(): ?int
    {
        $plan = $this->studentSubscriptionService->getStudentPlan();

        return $plan ? (int) $plan->id : null;
    }

    /**
     * Completed payments for student or patient healthcare plans.
     */
    public function subscriptionPaymentsQuery(bool $studentMembership, string $period = 'all'): Builder
    {
        $studentPlanId = $this->studentPlanId();

        $query = $this->completedPaymentsQuery()
            ->with(['user:id,name,email,role', 'subscription.plan']);

        if ($studentPlanId) {
            $query->whereHas('subscription', function ($q) use ($studentMembership, $studentPlanId) {
                if ($studentMembership) {
                    $q->where('plan_id', $studentPlanId);
                } else {
                    $q->where('plan_id', '!=', $studentPlanId);
                }
            });
        } elseif ($studentMembership) {
            $query->whereRaw('1 = 0');
        }

        if ($period === 'month') {
            $query->where('paid_at', '>=', now()->startOfMonth());
        }

        return $query;
    }

    /**
     * Subscriptions marked paid (used only for integrity warnings, not revenue totals).
     */
    public function paidSubscriptionsQuery(): Builder
    {
        return Subscription::query()->where('payment_status', 'paid');
    }

    /**
     * @return array{ledger: float, subscription_flagged: float, gap: float, missing_ledger_count: int}
     */
    public function subscriptionLedgerIntegrity(bool $studentMembership): array
    {
        $studentPlanId = $this->studentPlanId();
        $ledger = (float) $this->subscriptionPaymentsQuery($studentMembership)->sum('amount');

        $subQuery = $this->paidSubscriptionsQuery();
        if ($studentPlanId) {
            if ($studentMembership) {
                $subQuery->where('plan_id', $studentPlanId);
            } else {
                $subQuery->where('plan_id', '!=', $studentPlanId);
            }
        } elseif ($studentMembership) {
            return [
                'ledger' => $ledger,
                'subscription_flagged' => 0.0,
                'gap' => 0.0,
                'missing_ledger_count' => 0,
            ];
        }

        $subscriptionFlagged = (float) $subQuery->sum('paid_amount');

        $missingLedgerCount = (int) (clone $subQuery)
            ->whereDoesntHave('payments', fn ($q) => $q->where('status', 'completed'))
            ->count();

        return [
            'ledger' => round($ledger, 2),
            'subscription_flagged' => round($subscriptionFlagged, 2),
            'gap' => round(max(0, $subscriptionFlagged - $ledger), 2),
            'missing_ledger_count' => $missingLedgerCount,
        ];
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
        $payment = $subscription->payments->first(fn ($p) => $p->status === 'completed')
            ?? $subscription->payments->first();

        $collected = $payment
            ? (float) $payment->amount
            : (float) ($subscription->paid_amount > 0 ? $subscription->paid_amount : $subscription->total_amount);

        $isStudent = $this->studentSubscriptionService->isStudentPlanSubscription($subscription);

        return [
            'subscription' => $subscription,
            'payment' => $payment,
            'type_label' => $isStudent ? 'Student membership' : 'Healthcare subscription',
            'plan_name' => $subscription->plan->name ?? '—',
            'list_amount' => (float) ($subscription->amount_before_discount ?? $subscription->total_amount),
            'discount_amount' => (float) ($subscription->discount_amount ?? 0),
            'coupon_code' => $subscription->coupon_code,
            'paid_amount' => $collected,
            'paid_at' => $payment?->paid_at ?? $subscription->payment_verified_at,
            'method_label' => $this->getPaymentMethodLabel($subscription),
            'verified_by_label' => $this->getVerificationLabel($subscription),
            'transaction_id' => $subscription->razorpay_payment_id ?: $subscription->transaction_id,
            'invoice_url' => $payment && $subscription->payment_status === 'paid'
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
     * Subscription revenue from invoice ledger only (matches drill-down lists).
     *
     * @return array<string, mixed>
     */
    public function getSubscriptionRevenueMetrics(): array
    {
        $studentIntegrity = $this->subscriptionLedgerIntegrity(true);
        $patientIntegrity = $this->subscriptionLedgerIntegrity(false);

        $studentRevenue = $studentIntegrity['ledger'];
        $patientRevenue = $patientIntegrity['ledger'];

        $totalSubscriptionRevenue = $studentRevenue + $patientRevenue;

        $thisMonthStudentRevenue = (float) $this->subscriptionPaymentsQuery(true, 'month')->sum('amount');
        $thisMonthPatientRevenue = (float) $this->subscriptionPaymentsQuery(false, 'month')->sum('amount');
        $thisMonthSubscriptionRevenue = $thisMonthStudentRevenue + $thisMonthPatientRevenue;

        $activeSubscriptionsCount = Subscription::where('status', 'active')->count();

        $studentPaymentsCount = (int) $this->subscriptionPaymentsQuery(true)->count();
        $patientPaymentsCount = (int) $this->subscriptionPaymentsQuery(false)->count();

        $recentPayments = $this->completedPaymentsQuery()
            ->with(['user:id,name,email', 'subscription.plan'])
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return [
            'total_subscription_revenue' => round($totalSubscriptionRevenue, 2),
            'student_subscription_revenue' => round($studentRevenue, 2),
            'patient_subscription_revenue' => round($patientRevenue, 2),
            'student_payments_count' => $studentPaymentsCount,
            'patient_payments_count' => $patientPaymentsCount,
            'student_ledger_integrity' => $studentIntegrity,
            'patient_ledger_integrity' => $patientIntegrity,
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

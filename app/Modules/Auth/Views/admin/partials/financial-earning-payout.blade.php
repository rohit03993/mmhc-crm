@php
    $f = $stats['financial'] ?? [];
    $pb = $f['payout_breakdown'] ?? [];
    $payoutLabels = $pb['labels'] ?? [];
@endphp

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="stat-mini-card stat-success-mini">
            <div class="stat-mini-value">₹{{ number_format($f['total_earning'] ?? 0, 0) }}</div>
            <div class="stat-mini-label">Total earned (in)</div>
            <div class="stat-mini-sublabel">Subscriptions + services</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-mini-card stat-danger-mini">
            <div class="stat-mini-value">₹{{ number_format($f['total_staff_payouts'] ?? 0, 0) }}</div>
            <div class="stat-mini-label">Paid out (staff)</div>
            <div class="stat-mini-sublabel">Recorded payouts</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-mini-card {{ ($f['net_profit'] ?? 0) >= 0 ? 'stat-success-mini' : 'stat-danger-mini' }}">
            <div class="stat-mini-value">₹{{ number_format($f['net_profit'] ?? 0, 0) }}</div>
            <div class="stat-mini-label">Net (earned − paid)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.pending-payments') }}" class="text-decoration-none">
            <div class="stat-mini-card stat-warning-mini clickable-card-hover">
                <div class="stat-mini-value">₹{{ number_format($f['total_pending_to_collect'] ?? 0, 0) }}</div>
                <div class="stat-mini-label">Still to collect</div>
                <div class="stat-mini-sublabel">From customers <i class="fas fa-arrow-right ms-1"></i></div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- EARNING --}}
    <div class="col-12 col-xl-6">
        <div class="border rounded-3 h-100 overflow-hidden">
            <div class="px-3 py-2 bg-success text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-arrow-down me-2"></i>Earning (money in)</h6>
                <a href="{{ route('admin.plan-payments') }}" class="btn btn-sm btn-light">Customer payments</a>
            </div>
            <div class="p-3">
                <p class="small text-muted mb-3">Actual money received by MMHC — subscription checkout and service prepayments.</p>

                <div class="table-responsive">
                    <table class="table table-sm mb-0 fin-split-table">
                        <thead class="table-light">
                            <tr>
                                <th>Source</th>
                                <th class="text-end">All time</th>
                                <th class="text-end">This month</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="fas fa-graduation-cap text-primary me-1"></i> Student membership</td>
                                <td class="text-end fw-semibold">₹{{ number_format($f['student_subscription_revenue'] ?? 0, 2) }}</td>
                                <td class="text-end text-muted">₹{{ number_format($f['this_month_student_subscription_revenue'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-heartbeat text-success me-1"></i> Patient healthcare plans</td>
                                <td class="text-end fw-semibold">₹{{ number_format($f['patient_subscription_revenue'] ?? 0, 2) }}</td>
                                <td class="text-end text-muted">₹{{ number_format($f['this_month_patient_subscription_revenue'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-clipboard-list text-info me-1"></i> Service requests (prepaid)</td>
                                <td class="text-end fw-semibold">₹{{ number_format($f['total_service_revenue'] ?? 0, 2) }}</td>
                                <td class="text-end text-muted">₹{{ number_format($f['this_month_service_revenue'] ?? 0, 2) }}</td>
                            </tr>
                            <tr class="table-success">
                                <td class="fw-bold">Total earned</td>
                                <td class="text-end fw-bold">₹{{ number_format($f['total_earning'] ?? 0, 2) }}</td>
                                <td class="text-end fw-bold">₹{{ number_format($f['this_month_revenue'] ?? 0, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="small text-uppercase text-muted fw-semibold mt-4 mb-2">Expected (not yet collected)</h6>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 fin-split-table">
                        <tbody>
                            <tr>
                                <td>Student membership checkouts</td>
                                <td class="text-end">₹{{ number_format($f['pending_student_subscriptions'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Patient plan checkouts</td>
                                <td class="text-end">₹{{ number_format($f['pending_patient_subscriptions'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Service balances due</td>
                                <td class="text-end">₹{{ number_format($f['pending_service_payments'] ?? 0, 2) }}</td>
                            </tr>
                            <tr class="table-warning">
                                <td class="fw-semibold">Total to collect</td>
                                <td class="text-end fw-semibold">₹{{ number_format($f['total_pending_to_collect'] ?? 0, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="small text-muted mb-0 mt-2">Abandoned student carts (never started payment) are excluded from “to collect”.</p>
            </div>
        </div>
    </div>

    {{-- PAYOUT --}}
    <div class="col-12 col-xl-6">
        <div class="border rounded-3 h-100 overflow-hidden">
            <div class="px-3 py-2 bg-danger text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-arrow-up me-2"></i>Payout (money out)</h6>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-light">Staff payments</a>
            </div>
            <div class="p-3">
                <p class="small text-muted mb-3">Paid to nurses &amp; caregivers — services, rewards, and referral commissions.</p>

                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="text-center p-2 rounded bg-light">
                            <div class="small text-muted">Already paid</div>
                            <div class="fw-bold text-danger">₹{{ number_format($pb['paid_total'] ?? 0, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center p-2 rounded bg-light">
                            <div class="small text-muted">Pending</div>
                            <div class="fw-bold text-warning">₹{{ number_format($pb['pending_total'] ?? 0, 2) }}</div>
                            @if(($f['staff_with_pending_payments'] ?? 0) > 0)
                                <div class="small text-muted">{{ $f['staff_with_pending_payments'] }} staff</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center p-2 rounded border border-danger border-opacity-25">
                            <div class="small text-muted">Total liability</div>
                            <div class="fw-bold">₹{{ number_format($pb['combined_total'] ?? 0, 2) }}</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm mb-0 fin-split-table">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Pending</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payoutLabels as $typeKey => $typeLabel)
                                @php
                                    $paidAmt = $pb['paid_lines'][$typeKey] ?? 0;
                                    $pendAmt = $pb['pending_lines'][$typeKey]['amount'] ?? 0;
                                    $pendCnt = $pb['pending_lines'][$typeKey]['count'] ?? 0;
                                @endphp
                                <tr>
                                    <td>
                                        {{ $typeLabel }}
                                        @if($pendCnt > 0)
                                            <span class="badge bg-warning text-dark ms-1">{{ $pendCnt }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">₹{{ number_format($paidAmt, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($pendAmt, 2) }}</td>
                                    <td class="text-end fw-semibold">₹{{ number_format($paidAmt + $pendAmt, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="table-danger">
                                <td class="fw-bold">All payouts</td>
                                <td class="text-end fw-bold">₹{{ number_format($pb['paid_total'] ?? 0, 2) }}</td>
                                <td class="text-end fw-bold">₹{{ number_format($pb['pending_total'] ?? 0, 2) }}</td>
                                <td class="text-end fw-bold">₹{{ number_format($pb['combined_total'] ?? 0, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!empty($f['recent_subscription_payments']) && $f['recent_subscription_payments']->isNotEmpty())
<hr class="my-3">
<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Latest subscription payments (earning)</h6>
    <a href="{{ route('admin.plan-payments') }}" class="small text-decoration-none">View all →</a>
</div>
<div class="table-responsive">
    <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Date</th>
                <th>User</th>
                <th>Plan</th>
                <th class="text-end">Amount</th>
                <th>Invoice</th>
            </tr>
        </thead>
        <tbody>
            @foreach($f['recent_subscription_payments'] as $recentPayment)
                <tr>
                    <td>{{ optional($recentPayment->paid_at)->format('d M Y') ?? '—' }}</td>
                    <td>
                        @if($recentPayment->user)
                            <a href="{{ route('admin.profiles.view', $recentPayment->user) }}" class="text-decoration-none">{{ $recentPayment->user->name }}</a>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $recentPayment->subscription->plan->name ?? '—' }}</td>
                    <td class="text-end fw-semibold">₹{{ number_format((float) $recentPayment->amount, 2) }}</td>
                    <td>
                        @if($recentPayment->subscription)
                            <a href="{{ route('subscriptions.invoice', $recentPayment->subscription) }}" class="btn btn-sm btn-outline-primary rounded-pill" target="_blank" rel="noopener">View</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(($f['monthly_recurring_revenue'] ?? 0) > 0)
<hr class="my-3">
<div class="financial-mrr-card">
    <div class="mrr-icon"><i class="fas fa-sync-alt"></i></div>
    <div class="mrr-content">
        <div class="mrr-label">Monthly recurring revenue (MRR)</div>
        <div class="mrr-value">₹{{ number_format($f['monthly_recurring_revenue'], 2) }}/month</div>
    </div>
</div>
@endif

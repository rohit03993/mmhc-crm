@extends('auth::layout')

@section('title', 'Customer payments (income)')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1"><i class="fas fa-file-invoice-dollar me-2 text-success"></i>Customer payments</h2>
            <p class="text-muted small mb-0">
                Money <strong>received by MMHC</strong> from subscription and student membership payments.
                This is not <a href="{{ route('admin.payments.index') }}" class="text-decoration-none">staff payouts</a>.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.subscriptions') }}" class="btn btn-outline-secondary btn-sm">Manage subscriptions</a>
            <a href="{{ route('admin.pending-payments') }}" class="btn btn-outline-warning btn-sm">Pending from customers</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total subscription income</div>
                    <div class="fs-4 fw-bold text-success">₹{{ number_format($revenueMetrics['total_subscription_revenue'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Student membership</div>
                    <div class="fs-5 fw-semibold">₹{{ number_format($revenueMetrics['student_subscription_revenue'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Patient plans</div>
                    <div class="fs-5 fw-semibold">₹{{ number_format($revenueMetrics['patient_subscription_revenue'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">This month (subscriptions)</div>
                    <div class="fs-5 fw-semibold">₹{{ number_format($revenueMetrics['this_month_subscription_revenue'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.plan-payments') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Search</label>
                    <input type="search" name="q" class="form-control form-control-sm" placeholder="Invoice, receipt, txn, name, email" value="{{ $search ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">From</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.plan-payments') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                </div>
            </form>
            <div class="d-flex flex-wrap gap-2 mt-3">
                @php
                    $tabQuery = array_filter(['q' => $search ?? null, 'from' => request('from'), 'to' => request('to')]);
                @endphp
                <a href="{{ route('admin.plan-payments', array_merge($tabQuery, ['audience' => 'all'])) }}"
                   class="btn btn-sm {{ ($audience ?? 'all') === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
                <a href="{{ route('admin.plan-payments', array_merge($tabQuery, ['audience' => 'student'])) }}"
                   class="btn btn-sm {{ ($audience ?? '') === 'student' ? 'btn-primary' : 'btn-outline-primary' }}">Student membership</a>
                <a href="{{ route('admin.plan-payments', array_merge($tabQuery, ['audience' => 'patient'])) }}"
                   class="btn btn-sm {{ ($audience ?? '') === 'patient' ? 'btn-primary' : 'btn-outline-primary' }}">Patient plans</a>
            </div>
            @if(isset($totalFilteredAmount))
                <p class="small text-muted mb-0 mt-2">
                    Showing <strong>₹{{ number_format($totalFilteredAmount, 2) }}</strong> for current filters ({{ $payments->total() }} record{{ $payments->total() === 1 ? '' : 's' }}).
                </p>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Paid on</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Plan</th>
                            <th>Invoice</th>
                            <th class="text-end">Amount</th>
                            <th>Method</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            @php
                                $sub = $payment->subscription;
                                $isStudent = $sub && $studentPlanId && (int) $sub->plan_id === (int) $studentPlanId;
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ optional($payment->paid_at)->format('d M Y') ?? '—' }}</div>
                                    <small class="text-muted">{{ optional($payment->paid_at)->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @if($payment->user)
                                        <a href="{{ route('admin.profiles.view', $payment->user) }}" class="text-decoration-none fw-semibold">{{ $payment->user->name }}</a>
                                        <small class="text-muted d-block">{{ $payment->user->email }}</small>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ $isStudent ? 'bg-primary' : 'bg-info text-dark' }}">
                                        {{ $isStudent ? 'Student' : 'Patient plan' }}
                                    </span>
                                </td>
                                <td>{{ $sub->plan->name ?? '—' }}</td>
                                <td>
                                    <code class="small">{{ $payment->invoice_number }}</code>
                                    @if($payment->transaction_id)
                                        <small class="text-muted d-block text-truncate" style="max-width: 8rem;" title="{{ $payment->transaction_id }}">{{ $payment->transaction_id }}</small>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold text-success">₹{{ number_format((float) $payment->amount, 2) }}</td>
                                <td>{{ $payment->payment_method_display }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.plan-payments.view', $payment) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Details</a>
                                    @if($sub)
                                        <a href="{{ route('subscriptions.invoice', $sub) }}" class="btn btn-sm btn-outline-primary rounded-pill" target="_blank" rel="noopener">Invoice</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    No completed payment records yet. They are created when a subscription is marked paid (Razorpay or admin verify).
                                    <br><small class="mt-2 d-block">Open a paid subscription invoice once to backfill older rows if needed.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
            <div class="card-footer bg-white border-0">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection

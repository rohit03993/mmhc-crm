@extends('auth::layout')

@section('title', 'Payment '.$payment->invoice_number)

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('admin.plan-payments', request()->only(['audience', 'q', 'from', 'to'])) }}" class="btn btn-link text-decoration-none ps-0">
            <i class="fas fa-arrow-left me-1"></i>Back to customer payments
        </a>
        <h2 class="h4 mb-1 mt-2"><i class="fas fa-receipt me-2 text-success"></i>{{ $payment->invoice_number }}</h2>
        <p class="text-muted small mb-0">Receipt {{ $payment->receipt_number }} · {{ $payment->status_display }}</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Payment record</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Amount collected</span>
                            <span class="fs-4 fw-bold text-success">₹{{ number_format((float) $payment->amount, 2) }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Paid at</span>
                            <strong>{{ optional($payment->paid_at)->format('d M Y, h:i A') ?? '—' }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Method</span>
                            <strong>{{ $payment->payment_method_display }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Transaction ID</span>
                            <code>{{ $payment->transaction_id ?: '—' }}</code>
                        </div>
                        @if($paymentRow)
                        <div class="col-12">
                            <span class="text-muted small d-block">Processed by</span>
                            <strong>{{ $paymentRow['verified_by_label'] }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($payment->subscription)
                @php $sub = $payment->subscription; @endphp
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Linked subscription</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <span class="text-muted small d-block">Plan</span>
                                <strong>{{ $sub->plan->name ?? '—' }}</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted small d-block">Type</span>
                                <strong>{{ $paymentRow['type_label'] ?? 'Subscription' }}</strong>
                            </div>
                            @if($sub->coupon_code)
                            <div class="col-md-6">
                                <span class="text-muted small d-block">Coupon</span>
                                <strong>{{ $sub->coupon_code }}</strong>
                                <span class="text-muted"> (−₹{{ number_format((float) $sub->discount_amount, 2) }})</span>
                            </div>
                            @endif
                            @if($sub->amount_before_discount && (float) $sub->discount_amount > 0)
                            <div class="col-md-6">
                                <span class="text-muted small d-block">List price (before coupon)</span>
                                <strong>₹{{ number_format((float) $sub->amount_before_discount, 2) }}</strong>
                            </div>
                            @endif
                            <div class="col-md-6">
                                <span class="text-muted small d-block">Subscription status</span>
                                <span class="badge bg-{{ $sub->status_color ?? 'secondary' }}">{{ $sub->status_display ?? $sub->status }}</span>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <a href="{{ route('admin.subscriptions.view', $sub) }}" class="btn btn-outline-secondary btn-sm">Subscription details</a>
                            <a href="{{ route('subscriptions.invoice', $sub) }}" class="btn btn-primary btn-sm" target="_blank" rel="noopener">
                                <i class="fas fa-file-invoice me-1"></i>Tax invoice
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Customer</h5>
                </div>
                <div class="card-body">
                    @if($payment->user)
                        <p class="mb-1 fw-semibold">{{ $payment->user->name }}</p>
                        <p class="text-muted small mb-2">{{ $payment->user->email }}</p>
                        <span class="badge bg-light text-dark border">{{ ucfirst($payment->user->role) }}</span>
                        <div class="mt-3">
                            <a href="{{ route('admin.profiles.view', $payment->user) }}" class="btn btn-sm btn-outline-primary w-100">Open profile</a>
                        </div>
                    @else
                        <p class="text-muted mb-0">User record unavailable.</p>
                    @endif
                </div>
            </div>

            @if($payment->canBeRefunded())
            <div class="card border-0 shadow-sm mt-3 border-warning">
                <div class="card-body">
                    <h6 class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Refund</h6>
                    <p class="small text-muted">Marks this payment record as refunded in CRM only. Process the actual refund in Razorpay or your bank separately.</p>
                    <form method="POST" action="{{ route('admin.plan-payments.refund', $payment) }}" onsubmit="return confirm('Mark this payment as refunded in the system?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">Mark refunded</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

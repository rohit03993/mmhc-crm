@extends('auth::layout')

@section('title', 'Invoice '.$payment->invoice_number.' - MMHC')
@section('page-title', 'Tax Invoice')

@section('head')
<style>
@media print {
    .no-print { display: none !important; }
    .main-content { padding: 0 !important; }
}
.invoice-sheet {
    max-width: 820px;
    margin: 0 auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    padding: 2rem;
}
.invoice-meta { font-size: 0.9rem; color: #64748b; }
.invoice-table th, .invoice-table td { padding: 0.65rem 0.75rem; vertical-align: top; }
.invoice-total-row { font-size: 1.15rem; font-weight: 700; }
</style>
@endsection

@section('content')
@php
    $merchantName = \App\Modules\Plans\Support\SubscriptionSettings::upiMerchantName();
    $priceIncludesGst = (bool) data_get(
        $subscription->plan->payment_options ?? [],
        $subscription->payment_frequency.'.price_includes_gst',
        false
    );
@endphp

<div class="container-fluid py-4">
    <div class="no-print d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            @if(session('success'))
                <div class="alert alert-success mb-2">{{ session('success') }}</div>
            @endif
            <h4 class="mb-0"><i class="fas fa-file-invoice text-primary me-2"></i>Payment invoice</h4>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print / Save PDF
            </button>
            @if(!empty($continueUrl))
                <a href="{{ $continueUrl }}" class="btn btn-primary">
                    <i class="fas fa-graduation-cap me-1"></i>Continue to Academics
                </a>
            @else
                <a href="{{ route('subscriptions.show', $subscription) }}" class="btn btn-outline-primary">
                    <i class="fas fa-receipt me-1"></i>Subscription details
                </a>
            @endif
        </div>
    </div>

    <div class="invoice-sheet">
        <div class="d-flex flex-wrap justify-content-between gap-3 mb-4 pb-3 border-bottom">
            <div>
                <h5 class="fw-bold mb-1">{{ $merchantName }}</h5>
                <p class="invoice-meta mb-0">Tax invoice / payment receipt</p>
            </div>
            <div class="text-md-end">
                <div class="fw-bold fs-5 text-primary">{{ $payment->invoice_number }}</div>
                <div class="invoice-meta">Receipt: {{ $payment->receipt_number }}</div>
                <div class="invoice-meta">Date: {{ ($payment->paid_at ?? now())->format('d M Y') }}</div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="small text-muted text-uppercase fw-semibold mb-1">Billed to</div>
                <strong>{{ $subscription->user->name }}</strong><br>
                <span class="invoice-meta">{{ $subscription->user->email }}</span><br>
                @if($subscription->user->phone)
                    <span class="invoice-meta">{{ $subscription->user->phone }}</span>
                @endif
            </div>
            <div class="col-md-6">
                <div class="small text-muted text-uppercase fw-semibold mb-1">Payment</div>
                <strong>{{ ucfirst($payment->payment_method) }}</strong><br>
                @if($payment->transaction_id)
                    <span class="invoice-meta">Txn: {{ $payment->transaction_id }}</span>
                @endif
            </div>
        </div>

        <div class="table-responsive mb-3">
            <table class="table invoice-table border">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th class="text-end" style="width:28%">Amount (INR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $subscription->plan->name }}</strong><br>
                            <span class="invoice-meta">
                                {{ ucfirst(str_replace('_', ' ', $subscription->payment_frequency)) }}
                                · Valid {{ $subscription->start_date->format('d M Y') }} – {{ $subscription->end_date->format('d M Y') }}
                            </span>
                        </td>
                        <td class="text-end">₹{{ number_format((float) $subscription->total_amount, 2) }}</td>
                    </tr>
                    @if(!$priceIncludesGst && ($subscription->gst_amount ?? 0) > 0)
                    <tr>
                        <td class="invoice-meta">Includes GST ({{ number_format($subscription->gst_rate ?? 18, 2) }}%)</td>
                        <td class="text-end">₹{{ number_format((float) $subscription->gst_amount, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="invoice-total-row">
                        <td class="text-end">Total paid</td>
                        <td class="text-end text-success">₹{{ number_format((float) $payment->amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($priceIncludesGst)
            <p class="small text-muted mb-0">Amount is GST-inclusive as per plan pricing.</p>
        @endif

        <p class="small text-muted mt-4 mb-0">
            This is a computer-generated invoice for your MMHC subscription payment. For support, contact MMHC with invoice number <strong>{{ $payment->invoice_number }}</strong>.
        </p>
    </div>
</div>
@endsection

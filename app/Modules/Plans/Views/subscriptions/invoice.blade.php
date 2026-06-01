@extends('auth::layout')

@section('title', 'Invoice '.$payment->invoice_number.' - MMHC')
@section('page-title', 'Tax Invoice')

@section('head')
<style>
@media print {
    .no-print,
    .sidebar,
    .top-navbar,
    .mobile-nav-toggle {
        display: none !important;
    }
    body {
        background: #fff !important;
    }
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    .invoice-outer {
        padding: 0 !important;
    }
    .invoice-sheet {
        box-shadow: none !important;
        border: none !important;
        max-width: 100% !important;
    }
    .invoice-brand-bar,
    .invoice-brand-bar h1,
    .invoice-brand-bar p,
    .invoice-brand-bar strong,
    .invoice-brand-bar .invoice-gstin {
        color: #ffffff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .invoice-brand-bar {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
.invoice-outer {
    max-width: 880px;
    margin: 0 auto;
}
.invoice-sheet {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
    overflow: hidden;
}
.invoice-brand-bar {
    background: linear-gradient(135deg, #1e3a5f 0%, #0f766e 100%);
    color: #ffffff !important;
    padding: 1.5rem 2rem;
}
.main-content .invoice-brand-bar,
.main-content .invoice-brand-bar h1,
.main-content .invoice-brand-bar p,
.main-content .invoice-brand-bar strong,
.main-content .invoice-brand-bar .invoice-company-name,
.main-content .invoice-brand-bar .invoice-company-tagline,
.main-content .invoice-brand-bar .invoice-gstin,
.main-content .invoice-brand-bar .meta-line {
    color: #ffffff !important;
}
.invoice-brand-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1.25rem;
}
.invoice-logo-wrap {
    background: #fff;
    border-radius: 12px;
    padding: 0.5rem 0.85rem;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}
.invoice-logo {
    max-height: 56px;
    max-width: 220px;
    width: auto;
    height: auto;
    display: block;
}
.invoice-company-name {
    font-size: 1.15rem;
    font-weight: 700;
    margin: 0.75rem 0 0.2rem;
    color: #ffffff !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
}
.invoice-company-tagline {
    font-size: 0.88rem;
    margin: 0;
    color: rgba(255, 255, 255, 0.92) !important;
}
.invoice-gstin {
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 0.55rem;
    padding: 0.35rem 0.75rem;
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.45);
    border-radius: 8px;
    display: inline-block;
    letter-spacing: 0.06em;
    color: #ffffff !important;
}
.invoice-type-badge {
    text-align: right;
    color: #ffffff !important;
}
.invoice-type-badge h1 {
    font-size: 1.35rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    margin: 0 0 0.35rem;
    text-transform: uppercase;
    color: #ffffff !important;
}
.invoice-type-badge .meta-line {
    font-size: 0.88rem;
    margin: 0.2rem 0;
    color: rgba(255, 255, 255, 0.95) !important;
}
.invoice-type-badge .meta-line strong {
    color: #ffffff !important;
    font-weight: 700;
}
.invoice-body {
    padding: 1.75rem 2rem 2rem;
}
.invoice-parties {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 576px) {
    .invoice-parties {
        grid-template-columns: 1fr;
    }
}
.invoice-party-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem 1.15rem;
}
.invoice-party-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 0.5rem;
}
.invoice-party-box strong {
    color: #0f172a;
    font-size: 1rem;
}
.invoice-party-box .sub {
    font-size: 0.88rem;
    color: #475569;
    line-height: 1.45;
}
.invoice-table {
    margin-bottom: 0;
    border-color: #e2e8f0 !important;
}
.invoice-table thead th {
    background: #f1f5f9 !important;
    color: #334155 !important;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-color: #e2e8f0 !important;
}
.invoice-table td {
    color: #1e293b;
    border-color: #e2e8f0 !important;
    vertical-align: middle;
}
.invoice-line-desc strong {
    color: #0f172a;
}
.invoice-line-desc .sub {
    font-size: 0.82rem;
    color: #64748b;
}
.invoice-total-row td {
    background: #f0fdf4 !important;
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a !important;
    border-top: 2px solid #86efac !important;
}
.invoice-total-row .amount {
    color: #047857 !important;
    font-size: 1.25rem;
}
.invoice-footnote {
    font-size: 0.82rem;
    color: #64748b;
    margin-top: 1.25rem;
    padding-top: 1rem;
    border-top: 1px dashed #cbd5e1;
    line-height: 1.5;
}
</style>
@endsection

@section('content')
@php
    $companyName = $siteCompanyName ?? \App\Modules\Plans\Support\SubscriptionSettings::upiMerchantName();
    $logoUrl = $siteLogoUrl ?? mmhc_app_logo_url();
    $tagline = $siteTagline ?? 'Miracle Health Care';
    $gstin = \App\Modules\Plans\Support\SubscriptionSettings::gstNumber();
    $priceIncludesGst = (bool) data_get(
        $subscription->plan->payment_options ?? [],
        $subscription->payment_frequency.'.price_includes_gst',
        false
    );
    $listAmount = (float) ($subscription->amount_before_discount ?? $subscription->total_amount);
    $discountAmount = (float) ($subscription->discount_amount ?? 0);
    $gstAmount = (float) ($subscription->gst_amount ?? 0);
@endphp

<div class="container-fluid py-4 invoice-outer">
    <div class="no-print d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            @if(session('success'))
                <div class="alert alert-success mb-2">{{ session('success') }}</div>
            @endif
            <h4 class="mb-0 text-dark"><i class="fas fa-file-invoice text-primary me-2"></i>Payment invoice</h4>
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
        <header class="invoice-brand-bar text-white">
            <div class="invoice-brand-row">
                <div>
                    <div class="invoice-logo-wrap">
                        <img src="{{ $logoUrl }}" alt="{{ $companyName }}" class="invoice-logo">
                    </div>
                    <p class="invoice-company-name mb-0 text-white">{{ $companyName }}</p>
                    @if($tagline)
                        <p class="invoice-company-tagline text-white">{{ $tagline }}</p>
                    @endif
                    @if($gstin)
                        <div class="invoice-gstin text-white">GSTIN: {{ $gstin }}</div>
                    @endif
                </div>
                <div class="invoice-type-badge text-white">
                    <h1 class="text-white mb-0">Tax Invoice</h1>
                    <p class="meta-line text-white mb-0"><strong>{{ $payment->invoice_number }}</strong></p>
                    <p class="meta-line text-white mb-0">Receipt: {{ $payment->receipt_number }}</p>
                    <p class="meta-line text-white mb-0">Date: {{ ($payment->paid_at ?? now())->format('d M Y') }}</p>
                </div>
            </div>
        </header>

        <div class="invoice-body">
            <div class="invoice-parties">
                <div class="invoice-party-box">
                    <div class="invoice-party-label">Billed to</div>
                    <strong>{{ $subscription->user->name }}</strong>
                    <div class="sub mt-1">
                        {{ $subscription->user->email }}<br>
                        @if($subscription->user->phone)
                            {{ $subscription->user->phone }}<br>
                        @endif
                        @if($subscription->user->unique_id)
                            <span class="text-muted">ID: {{ $subscription->user->unique_id }}</span>
                        @endif
                    </div>
                </div>
                <div class="invoice-party-box">
                    <div class="invoice-party-label">Payment details</div>
                    <strong>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'payment')) }}</strong>
                    <div class="sub mt-1">
                        Status: <span class="text-success fw-semibold">Paid</span><br>
                        @if($payment->transaction_id)
                            Transaction: <code class="small">{{ $payment->transaction_id }}</code>
                        @endif
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table invoice-table border">
                    <thead>
                        <tr>
                            <th style="width:62%">Description</th>
                            <th class="text-end">Amount (INR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="invoice-line-desc">
                                <strong>{{ $subscription->plan->name }}</strong>
                                <div class="sub">
                                    {{ ucfirst(str_replace('_', ' ', $subscription->payment_frequency)) }}
                                    · {{ $subscription->start_date->format('d M Y') }} – {{ $subscription->end_date->format('d M Y') }}
                                </div>
                            </td>
                            <td class="text-end">₹{{ number_format($listAmount, 2) }}</td>
                        </tr>
                        @if(!$priceIncludesGst && $gstAmount > 0)
                            <tr>
                                <td class="invoice-line-desc">
                                    <span class="sub">GST @ {{ rtrim(rtrim(number_format((float) ($subscription->gst_rate ?? 18), 2), '0'), '.') }}%</span>
                                </td>
                                <td class="text-end">₹{{ number_format($gstAmount, 2) }}</td>
                            </tr>
                        @endif
                        @if($discountAmount > 0)
                            <tr>
                                <td class="invoice-line-desc">
                                    <span class="sub">Discount — coupon <strong>{{ $subscription->coupon_code }}</strong></span>
                                </td>
                                <td class="text-end text-success">− ₹{{ number_format($discountAmount, 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="invoice-total-row">
                            <td class="text-end">Total paid</td>
                            <td class="text-end amount">₹{{ number_format((float) $payment->amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($priceIncludesGst)
                <p class="invoice-footnote mb-2">Amount is GST-inclusive as per plan pricing.</p>
            @elseif($gstin && $gstAmount > 0)
                <p class="invoice-footnote mb-2">GST computed at {{ rtrim(rtrim(number_format((float) ($subscription->gst_rate ?? 18), 2), '0'), '.') }}% on the taxable value before discount.</p>
            @endif

            <p class="invoice-footnote mb-0">
                This is a computer-generated tax invoice for your subscription payment to <strong>{{ $companyName }}</strong>.
                For billing support, quote invoice <strong>{{ $payment->invoice_number }}</strong>.
            </p>
        </div>
    </div>
</div>
@endsection

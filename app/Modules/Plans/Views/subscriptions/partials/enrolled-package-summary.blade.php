@php
    /** @var \App\Modules\Plans\Models\Subscription $subscription */
    $pkg = $subscription->enrolledPackagePresentation();
    $variant = $variant ?? 'patient';
@endphp
<div class="subscription-enrolled-package {{ $variant === 'admin' ? 'subscription-enrolled-package--admin border-top pt-3 mt-3' : '' }}">
    <h6 class="mb-2">
        <i class="fas fa-file-contract me-2 text-primary"></i>
        @if($variant === 'admin')
            Enrolled package &amp; catalogue alignment
        @else
            Your enrolled payment package
        @endif
    </h6>

    @if(count($pkg['mismatch_messages']) > 0)
        <div class="alert alert-warning small py-2 mb-2 mb-md-3">
            @foreach($pkg['mismatch_messages'] as $msg)
                <div class="mb-1">{{ $msg }}</div>
            @endforeach
            <div class="mb-0 text-muted">Standard catalogue wording is shown only when list price and care-term fields match this tier.</div>
        </div>
    @endif

    <p class="small mb-1">
        <strong>{{ $pkg['frequency_label'] }}</strong>
        @if($pkg['show_catalog_marketing_line'] && $pkg['catalog_description'])
            <span class="text-muted"> — {{ $pkg['catalog_description'] }}</span>
        @endif
    </p>

    @if($pkg['recorded_term_summary'])
        <p class="small text-muted mb-2">{{ $pkg['recorded_term_summary'] }}</p>
    @endif

    @if($variant === 'admin')
        <div class="row small g-2 mb-2">
            <div class="col-6 col-md-3">
                <span class="text-muted d-block">Base (ex-GST)</span>
                <strong>₹{{ number_format($pkg['invoice_base'], 2) }}</strong>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted d-block">GST ({{ rtrim(rtrim(number_format($pkg['gst_rate'], 2), '0'), '.') }}%)</span>
                <strong>₹{{ number_format($pkg['invoice_gst'], 2) }}</strong>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted d-block">Total (incl. GST)</span>
                <strong>₹{{ number_format($pkg['invoice_total'], 2) }}</strong>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted d-block">Paid</span>
                <strong class="text-success">₹{{ number_format($pkg['invoice_paid'], 2) }}</strong>
            </div>
        </div>
        <div class="small mb-0">
            @if($pkg['aligned_with_catalog'])
                <span class="badge bg-success">Aligned with plan catalogue</span>
            @else
                <span class="badge bg-warning text-dark">Review required — does not match catalogue row</span>
            @endif
            @if($pkg['catalog_list_price_ex_gst'])
                <span class="text-muted ms-2">Catalogue list (ex-GST): ₹{{ number_format($pkg['catalog_list_price_ex_gst'], 2) }}</span>
            @endif
        </div>
    @else
        @if($pkg['aligned_with_catalog'] && $pkg['catalog_list_price_ex_gst'])
            <p class="small text-muted mb-0">Listed package price (ex-GST): ₹{{ number_format($pkg['catalog_list_price_ex_gst'], 0) }} — matches your invoice base before GST.</p>
        @elseif(! $pkg['aligned_with_catalog'])
            @if($pkg['catalog_list_price_ex_gst'])
                <p class="small text-muted mb-1">Catalogue reference (ex-GST) for this tier: ₹{{ number_format($pkg['catalog_list_price_ex_gst'], 0) }}</p>
            @endif
            <p class="small text-muted mb-0">Your invoice base (ex-GST): ₹{{ number_format($pkg['invoice_base'], 0) }} — total and paid amounts are shown above.</p>
        @endif
    @endif
</div>

@php
    /** @var \App\Modules\Services\Models\ServiceRequest $serviceRequest */
    $total = (float) $serviceRequest->total_amount;
    $prepaid = (float) $serviceRequest->prepaid_amount;
    $balance = $serviceRequest->balanceDue();
@endphp
<div class="sr-payment-summary">
    <div class="sr-payment-summary__row">
        <span class="sr-payment-summary__label">Visit charge</span>
        <span class="sr-payment-summary__value">
            @if($serviceRequest->isCoveredBySubscription())
                <span class="text-success fw-semibold">FREE</span>
                <small class="text-muted d-block">Subscription</small>
            @else
                ₹{{ number_format($total, 0) }}
            @endif
        </span>
    </div>
    @if($total > 0)
    <div class="sr-payment-summary__row">
        <span class="sr-payment-summary__label">Collected</span>
        <span class="sr-payment-summary__value text-success">₹{{ number_format($prepaid, 0) }}</span>
    </div>
    <div class="sr-payment-summary__row sr-payment-summary__row--emphasis">
        <span class="sr-payment-summary__label">Balance due</span>
        <span class="sr-payment-summary__value {{ $balance > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
            ₹{{ number_format($balance, 0) }}
        </span>
    </div>
    @endif
    <div class="sr-payment-summary__status mt-2">
        <span class="badge bg-{{ $serviceRequest->paymentStatusBadgeClass() }}">
            {{ $serviceRequest->paymentStatusLabel() }}
        </span>
    </div>
    @if($total > 0 && $balance > 0 && !auth()->user()->isAdmin())
    <p class="sr-payment-summary__hint mb-0 mt-2">
        <i class="fas fa-info-circle me-1"></i>
        Our team will contact you to collect the balance. You can also pay at the MMHC office.
    </p>
    @endif
</div>

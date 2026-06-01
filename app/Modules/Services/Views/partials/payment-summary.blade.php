@php
    /** @var \App\Modules\Services\Models\ServiceRequest $serviceRequest */
    $total = (float) $serviceRequest->total_amount;
    $coveredByPlan = $serviceRequest->isCoveredBySubscription();
@endphp
<div class="sr-payment-summary">
    <div class="sr-payment-summary__row">
        <span class="sr-payment-summary__label">Visit charge</span>
        <span class="sr-payment-summary__value">
            @if($coveredByPlan)
                <span class="text-success fw-semibold">FREE</span>
                <small class="text-muted d-block">Included in your healthcare plan</small>
            @else
                ₹{{ number_format($total, 0) }}
                <small class="text-muted d-block">Per-visit fee (paid when you book)</small>
            @endif
        </span>
    </div>
    <div class="sr-payment-summary__status mt-2">
        <span class="badge bg-{{ $serviceRequest->paymentStatusBadgeClass() }}">
            @if($coveredByPlan)
                Covered by subscription
            @elseif($serviceRequest->payment_status === 'paid')
                Visit fee recorded
            @else
                {{ $serviceRequest->paymentStatusLabel() }}
            @endif
        </span>
    </div>
</div>

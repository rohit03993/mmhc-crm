@extends('auth::layout')

@section('title', 'Pay visit fee - MMHC CRM')
@section('page-title', 'Pay visit fee')

@section('head')
    @include('services::partials.mobile-assets')
@endsection

@section('content')
@php
    $amount = (float) $serviceRequest->total_amount;
@endphp
<div class="container-fluid px-3 px-md-4 py-3 hc-mobile-shell" data-mmhc-ptr>
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h2 class="h4 mb-1">Pay visit fee</h2>
                    <p class="text-muted small mb-3">Booking #{{ $serviceRequest->id }} · {{ $serviceRequest->serviceType->name ?? 'Service' }}</p>

                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded-3 bg-light">
                        <span class="fw-semibold">Amount due</span>
                        <span class="fs-4 fw-bold text-success">₹{{ number_format($amount, 2) }}</span>
                    </div>

                    <ul class="list-unstyled small text-muted mb-4">
                        <li class="mb-1"><i class="fas fa-calendar me-2"></i>{{ $serviceRequest->start_date?->format('M d, Y') }} → {{ $serviceRequest->end_date?->format('M d, Y') }}</li>
                        <li class="mb-1"><i class="fas fa-map-marker-alt me-2"></i>{{ \Illuminate\Support\Str::limit($serviceRequest->location, 80) }}</li>
                        @if($serviceRequest->assignedStaff || $serviceRequest->preferredStaff)
                            <li><i class="fas fa-user-nurse me-2"></i>{{ ($serviceRequest->assignedStaff ?? $serviceRequest->preferredStaff)->name }}</li>
                        @endif
                    </ul>

                    @if(!empty($razorpayEnabled))
                        <div class="alert alert-info small">
                            <i class="fas fa-shield-alt me-1"></i>
                            Pay securely with Razorpay (UPI, cards, wallets). Staff are notified after payment succeeds.
                        </div>
                        <button type="button" id="mmhcPayVisitBtn" class="btn btn-success btn-lg w-100 mb-2" onclick="startVisitRazorpayCheckout()">
                            <i class="fas fa-credit-card me-2"></i>Pay ₹{{ number_format($amount, 2) }} now
                        </button>
                        <p class="small text-muted mb-0 text-center">You can also pay later from My Requests. Office / cash collection remains available via MMHC admin.</p>
                    @else
                        <div class="alert alert-warning mb-0">
                            Online payment is temporarily unavailable. Please contact MMHC to pay by cash or UPI at the office. Your booking is saved as unpaid.
                        </div>
                    @endif

                    <div class="d-flex gap-2 mt-3">
                        <a href="{{ route('services.my-requests') }}" class="btn btn-outline-secondary flex-fill">My Requests</a>
                        <a href="{{ route('services.show', $serviceRequest) }}" class="btn btn-outline-primary flex-fill">View booking</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if(!empty($razorpayEnabled))
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
async function startVisitRazorpayCheckout() {
    const btn = document.getElementById('mmhcPayVisitBtn');
    const old = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Opening Razorpay...';
    }
    try {
        const orderResp = await fetch(@json(route('services.razorpay.order', $serviceRequest)), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token()),
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        });
        const orderData = await orderResp.json();
        if (!orderResp.ok || !orderData.success) {
            throw new Error(orderData.message || 'Failed to create payment order.');
        }

        const options = {
            key: orderData.key,
            amount: orderData.amount,
            currency: orderData.currency,
            order_id: orderData.order_id,
            name: 'MMHC',
            description: 'Visit fee #' + @json($serviceRequest->id),
            prefill: {
                name: orderData.customer?.name || '',
                email: orderData.customer?.email || '',
                contact: orderData.customer?.contact || ''
            },
            handler: async function (response) {
                const verifyResp = await fetch(@json(route('services.razorpay.verify', $serviceRequest)), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(response)
                });
                const verifyData = await verifyResp.json();
                if (!verifyResp.ok || !verifyData.success) {
                    throw new Error(verifyData.message || 'Payment verification failed.');
                }
                window.location.href = verifyData.redirect_url || @json(route('services.my-requests'));
            }
        };

        const rzp = new Razorpay(options);
        rzp.on('payment.failed', function () {
            alert('Payment failed or cancelled. Please try again.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = old;
            }
        });
        rzp.open();
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = old;
        }
    } catch (error) {
        console.error(error);
        alert(error.message || 'Unable to start online payment right now.');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = old;
        }
    }
}
</script>
@endif
@endsection

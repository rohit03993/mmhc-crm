@extends('auth::layout')

@section('title', 'Student membership - MMHC CRM')
@section('page-title', 'Student membership')

@section('content')
@php
    $monthly = (int) ($display['monthly_reference_inr'] ?? 100);
    $years = (int) ($display['duration_years'] ?? 10);
    $membershipTotal = (int) ($display['launch_price_inr'] ?? ($monthly * 12 * $years));
    $headline = $display['headline'] ?? 'Join the Student Journey';
    $subheadline = $display['subheadline'] ?? '';
    $pendingOriginal = $pending ? (float) ($pending->amount_before_discount ?? $pending->total_amount) : $membershipTotal;
    $pendingDiscount = $pending ? (float) ($pending->discount_amount ?? 0) : 0;
    $pendingFinal = $pending ? (float) $pending->total_amount : $membershipTotal;
@endphp

<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                            <i class="fas fa-graduation-cap fa-2x text-primary"></i>
                        </div>
                        <h2 class="h5 mb-2">{{ $headline }}</h2>
                        @if($subheadline)
                            <p class="text-muted small mb-0">{{ $subheadline }}</p>
                        @endif
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if(session('info'))
                        <div class="alert alert-info">{{ session('info') }}</div>
                    @endif

                    <div class="alert alert-light border small mb-4">
                        <i class="fas fa-unlock-alt text-primary me-1"></i>
                        While payment is pending you can still use <strong>Profile</strong> and <strong>Community</strong>.
                        Academics unlocks after membership is active.
                    </div>

                    @if(!$plan)
                        <div class="alert alert-warning mb-0">
                            Student membership is not set up on this server yet. Please contact MMHC support.
                        </div>
                    @else
                        <p class="text-muted small mb-3">Students only · ₹{{ number_format($monthly) }}/month × {{ $years }} years</p>

                        <div class="bg-light rounded-3 p-3 mb-3">
                            <p class="small text-muted mb-3 mb-md-2">
                                Equivalent to <strong>₹{{ number_format($monthly) }}/month</strong> for <strong>{{ $years }} years</strong>
                                (12 × {{ $years }} = {{ $years * 12 }} months).
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold">Membership total</span>
                                <span class="text-muted" id="priceOriginal">₹{{ number_format($membershipTotal) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-success d-none" id="priceDiscountRow">
                                <span>Coupon discount</span>
                                <span id="priceDiscount">− ₹0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                                <span class="fw-semibold">You pay (one-time)</span>
                                <span class="fs-4 fw-bold text-success" id="priceFinal">₹{{ number_format($membershipTotal) }}</span>
                            </div>
                        </div>

                        @if(!$pending)
                        <div class="mb-4">
                            <label class="form-label small fw-semibold"><i class="fas fa-ticket-alt me-1"></i>Have a coupon code?</label>
                            <div class="input-group">
                                <input type="text" id="couponInput" class="form-control text-uppercase" placeholder="Enter code" maxlength="64" value="{{ old('coupon_code') }}">
                                <button type="button" class="btn btn-outline-primary" id="applyCouponBtn">Apply</button>
                            </div>
                            <div id="couponMessage" class="small mt-2"></div>
                        </div>
                        @elseif($pendingDiscount > 0)
                        <div class="alert alert-success small mb-3">
                            <i class="fas fa-tag me-1"></i>Coupon <strong>{{ $pending->coupon_code }}</strong> applied —
                            ₹{{ number_format($pendingOriginal, 0) }} → <strong>₹{{ number_format($pendingFinal, 0) }}</strong>
                        </div>
                        @endif

                        <ul class="list-unstyled small mb-4">
                            @foreach(($plan->features ?? []) as $feature)
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>{{ $feature }}</li>
                            @endforeach
                        </ul>

                        @if($pending)
                            <a href="{{ route('subscriptions.payment-confirmation', $pending->id) }}" class="btn btn-primary btn-lg w-100 mb-2">
                                <i class="fas fa-credit-card me-2"></i>Complete payment (₹{{ number_format($pendingFinal, 0) }})
                            </a>
                            <p class="text-center text-muted small mb-0">You already started checkout. Tap above to finish payment.</p>
                        @else
                            <form method="POST" action="{{ route('student-subscription.subscribe') }}" id="subscribeForm">
                                @csrf
                                <input type="hidden" name="coupon_code" id="couponCodeHidden" value="">
                                <button type="submit" class="btn btn-primary btn-lg w-100" id="subscribeBtn">
                                    <i class="fas fa-rocket me-2"></i>Subscribe now — <span id="subscribeAmount">₹{{ number_format($membershipTotal) }}</span> one-time
                                </button>
                            </form>
                            <p class="text-center text-muted small mt-3 mb-0">
                                After Razorpay payment, your {{ $years }}-year student membership activates immediately and you can download your invoice.
                            </p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($plan && !$pending)
<script>
(function () {
    const launchPrice = {{ $membershipTotal }};
    let appliedCode = '';
    const input = document.getElementById('couponInput');
    const hidden = document.getElementById('couponCodeHidden');
    const msg = document.getElementById('couponMessage');
    const btn = document.getElementById('applyCouponBtn');

    function formatInr(n) {
        return '₹' + Number(n).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    }

    function resetPricing() {
        appliedCode = '';
        hidden.value = '';
        document.getElementById('priceFinal').textContent = formatInr(launchPrice);
        document.getElementById('subscribeAmount').textContent = formatInr(launchPrice);
        document.getElementById('priceDiscountRow').classList.add('d-none');
        msg.textContent = '';
        msg.className = 'small mt-2';
    }

    btn?.addEventListener('click', async function () {
        const code = (input?.value || '').trim();
        if (!code) {
            resetPricing();
            return;
        }

        btn.disabled = true;
        msg.textContent = 'Checking code…';
        msg.className = 'small mt-2 text-muted';

        try {
            const resp = await fetch('{{ route('student-subscription.validate-coupon') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ coupon_code: code })
            });
            const data = await resp.json();

            if (!resp.ok || !data.success) {
                resetPricing();
                msg.textContent = data.message || 'Invalid coupon.';
                msg.className = 'small mt-2 text-danger';
                return;
            }

            appliedCode = data.coupon_code;
            hidden.value = appliedCode;
            document.getElementById('priceOriginal').textContent = formatInr(data.original_amount);
            document.getElementById('priceDiscount').textContent = '− ' + formatInr(data.discount_amount);
            document.getElementById('priceDiscountRow').classList.remove('d-none');
            document.getElementById('priceFinal').textContent = formatInr(data.final_amount);
            document.getElementById('subscribeAmount').textContent = formatInr(data.final_amount);
            msg.textContent = data.message;
            msg.className = 'small mt-2 text-success';
        } catch (e) {
            msg.textContent = 'Could not validate coupon. Try again.';
            msg.className = 'small mt-2 text-danger';
        } finally {
            btn.disabled = false;
        }
    });
})();
</script>
@endif
@endsection

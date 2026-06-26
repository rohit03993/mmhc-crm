@extends('auth::layout')

@section('title', 'Subscription Plans')
@section('page-title', 'Plans')

@section('head')
@include('services::partials.mobile-assets')
@endsection

@section('content')
<div class="mobile-app-container hc-mobile-shell" data-mmhc-ptr>
<!-- Mobile Header -->
<div class="app-mobile-header d-md-none">
    <div class="d-flex align-items-center">
        <button class="btn btn-link text-white p-0 me-3" onclick="history.back()">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h5 class="text-white mb-0">Subscription Plans</h5>
    </div>
</div>

<div class="container-fluid px-3 py-4">
    <div class="hc-m-hero d-md-none mb-3">
        <p class="hc-m-hero__label">Healthcare plans</p>
        <h2 class="hc-m-hero__title">Choose your plan</h2>
        <p class="hc-m-hero__lede">10 years of home care coverage. Services are free for subscribed patients.</p>
    </div>
    <!-- Desktop Header -->
    <div class="d-none d-md-block mb-4">
        <h4 class="page-title">Subscription Plans</h4>
        <p class="text-muted">Choose a plan that suits your family's healthcare needs</p>
                </div>

    <!-- Info Banner -->
    @auth
        @if(auth()->user()->isPatient() && empty($patientCheckoutAvailable))
        <div class="alert alert-warning mb-4">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                <div>
                    <strong>Checkout not configured.</strong> Online payment (Razorpay) or manual UPI must be enabled on this server before you can subscribe. Please contact MMHC support.
                </div>
            </div>
        </div>
        @elseif(auth()->user()->isPatient())
        <div class="alert alert-info mb-4">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle me-2 mt-1"></i>
                <div>
                    <strong>How payment works:</strong>
                    @if(!empty($razorpayEnabled))
                        Pay online with Razorpay (cards, UPI, wallets).
                        @if($patientManualEnabled && $patientManualWithRazorpay)
                            Manual UPI + screenshot is also available at checkout.
                        @endif
                    @else
                        Complete payment via UPI and upload proof — MMHC verifies within 24 hours.
                    @endif
                </div>
            </div>
        </div>
        @endif
    @endauth
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-start">
            <i class="fas fa-info-circle me-2 mt-1"></i>
            <div>
                <strong>Note:</strong> All plans include 10 years of total care coverage. Services are FREE for subscribed patients.
            </div>
        </div>
    </div>

    <!-- Plans Grid -->
    <div class="row g-3">
        @forelse($plans as $plan)
        <div class="col-12 col-md-6 col-lg-3">
            <div class="subscription-plan-card {{ $plan->is_popular ? 'popular-plan' : '' }}">
                @if($plan->is_popular)
                <div class="popular-badge">
                    <i class="fas fa-star me-1"></i> Most Popular
                </div>
                @endif
                
                <div class="plan-header">
                    <h5 class="plan-name">{{ $plan->name }}</h5>
                    <p class="plan-members text-muted small mb-2">
                        <i class="fas fa-users me-1"></i> {{ $plan->members_included }}
                    </p>
                    <div class="plan-price">
                        <span class="price-amount">₹{{ number_format($plan->monthly_price ?? $plan->price, 0) }}</span>
                        <span class="price-period">/month</span>
                    </div>
                    <p class="plan-description small text-muted mt-2">{{ $plan->description }}</p>
                    </div>
                    
                <div class="plan-features">
                    <h6 class="features-title">Features:</h6>
                    <ul class="features-list">
                            @foreach($plan->features as $feature)
                        <li>
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    
                <div class="plan-payment-options">
                    <h6 class="payment-title">Payment Options:</h6>
                    <div class="payment-options-list">
                        @if(isset($plan->payment_options))
                            @foreach($plan->payment_options as $frequency => $option)
                            <div class="payment-option-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $option['label'] ?? ucfirst(str_replace('_', ' ', $frequency)) }}</strong>
                                        <small class="d-block text-muted">{{ $option['description'] ?? '' }}</small>
                                    </div>
                                    <span class="payment-amount">₹{{ number_format($option['price'] ?? 0, 0) }}</span>
                                </div>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="plan-actions">
                    @auth
                        @if(auth()->user()->isPatient())
                            @if(empty($patientCheckoutAvailable))
                            <button class="btn btn-secondary w-100" disabled title="Payment not configured on server">
                                Checkout unavailable
                            </button>
                            @else
                            <a href="{{ route('plans.show', $plan) }}" class="btn btn-primary w-100">
                                <i class="fas fa-arrow-right me-2"></i>Subscribe Now
                            </a>
                            @endif
                        @else
                            <button class="btn btn-secondary w-100" disabled>
                                Only for Patients
                            </button>
                        @endif
                    @else
                        <a href="{{ route('auth.login') }}" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Subscribe
                        </a>
                        @endauth
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No plans available</h5>
                    <p class="text-muted">Please check back later for available healthcare plans.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
</div>

<style>
.subscription-plan-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 20px;
    position: relative;
    transition: transform 0.3s, box-shadow 0.3s;
    border: 2px solid transparent;
}

.subscription-plan-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.subscription-plan-card.popular-plan {
    border-color: #007bff;
    background: linear-gradient(135deg, #fff 0%, #f8f9ff 100%);
}

.popular-badge {
    position: absolute;
    top: -12px;
    right: 20px;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,123,255,0.3);
}

.plan-header {
    text-align: center;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 20px;
}

.plan-name {
    font-size: 20px;
    font-weight: 700;
    color: #212529;
    margin-bottom: 8px;
}

.plan-members {
    font-size: 13px;
}

.plan-price {
    margin: 16px 0;
}

.price-amount {
    font-size: 32px;
    font-weight: 700;
    color: #007bff;
}

.price-period {
    font-size: 16px;
    color: #6c757d;
    margin-left: 4px;
}

.plan-description {
    font-size: 13px;
    line-height: 1.5;
}

.plan-features {
    margin-bottom: 20px;
}

.features-title {
    font-size: 14px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 12px;
}

.features-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.features-list li {
    padding: 8px 0;
    font-size: 13px;
    color: #495057;
    display: flex;
    align-items: flex-start;
}

.features-list li i {
    margin-top: 2px;
    flex-shrink: 0;
}

.plan-payment-options {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
}

.payment-title {
    font-size: 14px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 12px;
}

.payment-options-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.payment-option-item {
    background: white;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.payment-option-item strong {
    font-size: 13px;
    color: #212529;
}

.payment-option-item small {
    font-size: 11px;
}

.payment-amount {
    font-size: 16px;
    font-weight: 700;
    color: #007bff;
}

.plan-actions {
    margin-top: 20px;
}

.plan-actions .btn {
    border-radius: 10px;
    padding: 12px;
    font-weight: 600;
    font-size: 14px;
}

@media (max-width: 768px) {
    .subscription-plan-card {
        padding: 16px;
    }
    
    .plan-name {
        font-size: 18px;
    }
    
    .price-amount {
        font-size: 28px;
    }
}
</style>
@endsection

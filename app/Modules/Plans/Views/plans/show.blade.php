@extends('auth::layout')

@section('title', $plan->name.' — Plan')
@section('page-title', 'Plan details')

@section('head')
@include('services::partials.mobile-assets')
@endsection

@section('content')
<div class="mobile-app-container hc-mobile-shell" data-mmhc-ptr>
<!-- Mobile Header -->
<div class="app-mobile-header d-md-none">
    <div class="d-flex align-items-center">
        <a href="{{ route('plans.index') }}" class="btn btn-link text-white p-0 me-3" aria-label="Back to plans">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h5 class="text-white mb-0">Plan Details</h5>
    </div>
</div>

<div class="container-fluid px-3 py-4">
    <div class="hc-m-hero d-md-none mb-3">
        <p class="hc-m-hero__label">Subscribe</p>
        <h2 class="hc-m-hero__title">{{ $plan->name }}</h2>
        <p class="hc-m-hero__lede">{{ $plan->members_included }} · ₹{{ number_format($plan->monthly_price ?? $plan->price, 0) }}/month</p>
    </div>
    <div class="row">
        <div class="col-12 col-lg-8 mx-auto">
            <!-- Plan Card -->
            <div class="subscription-plan-detail-card {{ $plan->is_popular ? 'popular-plan' : '' }}">
                @if($plan->is_popular)
                <div class="popular-badge">
                    <i class="fas fa-star me-1"></i> Most Popular
                </div>
                @endif

                <div class="plan-header text-center">
                    <h3 class="plan-name">{{ $plan->name }}</h3>
                    <p class="plan-members text-muted mb-3">
                        <i class="fas fa-users me-1"></i> {{ $plan->members_included }}
                    </p>
                    <div class="plan-price">
                        <span class="price-amount">₹{{ number_format($plan->monthly_price ?? $plan->price, 0) }}</span>
                        <span class="price-period">/month</span>
                    </div>
                    <p class="plan-description mt-3">{{ $plan->description }}</p>
                </div>

                <!-- Features -->
                <div class="plan-section">
                    <h5 class="section-title">
                        <i class="fas fa-check-circle text-success me-2"></i>Features Included
                    </h5>
                    <ul class="features-list">
                        @foreach($plan->features as $feature)
                        <li>
                            <i class="fas fa-check text-success me-2"></i>
                            <span>{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Payment Options -->
                <div class="plan-section">
                    <h5 class="section-title">
                        <i class="fas fa-credit-card text-primary me-2"></i>Choose Payment Option
                    </h5>
                    
                    @auth
                        @if(auth()->user()->isPatient())
                            @php
                                $activeSubscription = auth()->user()->activeSubscription;
                            @endphp
                            @if($activeSubscription)
                            <div class="alert alert-info mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Current Subscription:</strong> {{ $activeSubscription->plan?->name ?? 'Unknown Plan' }}
                                        <br>
                                        <small>Expires: {{ $activeSubscription->end_date->format('M d, Y') }} ({{ $activeSubscription->days_remaining }} days remaining)</small>
                                    </div>
                                    <a href="{{ route('subscriptions.show', $activeSubscription) }}" class="btn btn-sm btn-outline-primary">
                                        View Details
                                    </a>
                                </div>
                            </div>
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-sync-alt me-2"></i>
                                <strong>Upgrade/Downgrade Available:</strong> You can upgrade or downgrade your plan. Prorated refund will be applied for remaining days.
                            </div>
                            @endif
                            
                            @if($activeSubscription)
                            <form action="{{ route('subscriptions.subscribe') }}" method="POST" id="subscribeForm">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <input type="hidden" name="upgrade" value="1">
                                @if(request()->has('ref'))
                                <input type="hidden" name="referrer_id" value="{{ request()->query('ref') }}">
                                @endif
                                
                                <div class="payment-options-grid">
                                    @if(isset($plan->payment_options))
                                        @foreach($plan->payment_options as $frequency => $option)
                                        <label class="payment-option-card">
                                            <input type="radio" name="payment_frequency" value="{{ $frequency }}" 
                                                   {{ $loop->first ? 'checked' : '' }} required>
                                            <div class="payment-option-content">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <strong class="option-label">{{ $option['label'] ?? ucfirst(str_replace('_', ' ', $frequency)) }}</strong>
                                                        <p class="option-description small mb-0">{{ $option['description'] ?? '' }}</p>
                                                    </div>
                                                    <span class="option-price">₹{{ number_format($option['price'] ?? 0, 0) }}</span>
                                                </div>
                                                @if(isset($option['payable_years']) && isset($option['care_benefits_years']))
                                                <div class="option-benefits">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        {{ $option['payable_years'] }} years payable + {{ $option['care_benefits_years'] }} years extra = {{ $option['payable_years'] + $option['care_benefits_years'] }} years total
                                                    </small>
                                                </div>
                                                @endif
                                            </div>
                                        </label>
                                        @endforeach
                                    @endif
                                </div>

                                <div class="form-group mt-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="auto_renew" id="auto_renew" value="1">
                                        <label class="form-check-label" for="auto_renew">
                                            Auto-renew subscription
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 mt-4">
                                    <i class="fas fa-sync-alt me-2"></i>Upgrade/Downgrade Plan
                                </button>
                            </form>
                            @elseif(!$activeSubscription)
                            <form action="{{ route('subscriptions.subscribe') }}" method="POST" id="subscribeForm">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                @if(request()->has('ref'))
                                <input type="hidden" name="referrer_id" value="{{ request()->query('ref') }}">
                                @endif
                                
                                <div class="payment-options-grid">
                                    @if(isset($plan->payment_options))
                                        @foreach($plan->payment_options as $frequency => $option)
                                        <label class="payment-option-card">
                                            <input type="radio" name="payment_frequency" value="{{ $frequency }}" 
                                                   {{ $loop->first ? 'checked' : '' }} required>
                                            <div class="payment-option-content">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <strong class="option-label">{{ $option['label'] ?? ucfirst(str_replace('_', ' ', $frequency)) }}</strong>
                                                        <p class="option-description small mb-0">{{ $option['description'] ?? '' }}</p>
                                                    </div>
                                                    <span class="option-price">₹{{ number_format($option['price'] ?? 0, 0) }}</span>
                                                </div>
                                                @if(isset($option['payable_years']) && isset($option['care_benefits_years']))
                                                <div class="option-benefits">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        {{ $option['payable_years'] }} years payable + {{ $option['care_benefits_years'] }} years extra = {{ $option['payable_years'] + $option['care_benefits_years'] }} years total
                                                    </small>
                                                </div>
                                                @endif
                                            </div>
                                        </label>
                                        @endforeach
                                    @endif
                                </div>

                                <div class="form-group mt-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="auto_renew" id="auto_renew" value="1">
                                        <label class="form-check-label" for="auto_renew">
                                            Auto-renew subscription
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 mt-4">
                                    <i class="fas fa-credit-card me-2"></i>Subscribe Now
                                </button>
                            </form>
                            @endif
                        @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Only patients can subscribe to plans.
                        </div>
                        @endif
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Please <a href="{{ route('auth.login') }}">login</a> to subscribe to this plan.
                    </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<style>
.subscription-plan-detail-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 24px;
    position: relative;
    border: 2px solid transparent;
}

.subscription-plan-detail-card.popular-plan {
    border-color: #007bff;
}

.popular-badge {
    position: absolute;
    top: -12px;
    right: 24px;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,123,255,0.3);
}

.plan-header {
    padding-bottom: 24px;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 24px;
}

.plan-name {
    font-size: 28px;
    font-weight: 700;
    color: #212529;
    margin-bottom: 12px;
}

.plan-price {
    margin: 20px 0;
}

.price-amount {
    font-size: 48px;
    font-weight: 700;
    color: #007bff;
}

.price-period {
    font-size: 20px;
    color: #6c757d;
    margin-left: 4px;
}

.plan-section {
    margin-bottom: 32px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #212529;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
}

.features-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.features-list li {
    padding: 12px 0;
    font-size: 15px;
    color: #495057;
    display: flex;
    align-items: flex-start;
    border-bottom: 1px solid #f0f0f0;
}

.features-list li:last-child {
    border-bottom: none;
}

.payment-options-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.payment-option-card {
    position: relative;
    cursor: pointer;
}

.payment-option-card input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.payment-option-content {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 16px;
    transition: all 0.3s;
}

.payment-option-card input[type="radio"]:checked + .payment-option-content {
    background: #e7f3ff;
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.2);
}

.option-label {
    font-size: 16px;
    color: #212529;
}

.option-description {
    color: #6c757d;
    margin-top: 4px;
}

.option-price {
    font-size: 20px;
    font-weight: 700;
    color: #007bff;
}

.option-benefits {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #dee2e6;
}

@media (max-width: 768px) {
    .subscription-plan-detail-card {
        padding: 16px;
    }
    
    .plan-name {
        font-size: 24px;
    }
    
    .price-amount {
        font-size: 36px;
    }
}
</style>
@endsection

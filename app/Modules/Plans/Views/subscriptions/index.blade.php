@extends('auth::layout')

@section('title', 'My Subscriptions')
@section('page-title', 'Subscriptions')

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
        <h5 class="text-white mb-0">My Subscriptions</h5>
    </div>
</div>

<div class="container-fluid px-3 py-4">
    <div class="hc-m-hero d-md-none mb-3">
        <p class="hc-m-hero__label">Your plan</p>
        <h2 class="hc-m-hero__title">Subscriptions</h2>
        <p class="hc-m-hero__lede">Manage coverage, upgrades, and payment status.</p>
    </div>
    <!-- Desktop Header -->
    <div class="d-none d-md-block mb-4">
        <h4 class="page-title">My Subscriptions</h4>
        <p class="text-muted">Manage your healthcare subscription plans</p>
    </div>

    @if($activeSubscription)
    <div class="alert alert-success mb-4">
        <div class="d-flex align-items-start">
            <i class="fas fa-check-circle fa-2x me-3 mt-1"></i>
            <div class="flex-grow-1">
                <h6 class="mb-1">Active subscription</h6>
                <p class="mb-1">
                    <strong>{{ $activeSubscription->plan?->name ?? 'Your plan' }}</strong>
                    — valid through <strong>{{ $activeSubscription->end_date->format('M d, Y') }}</strong>.
                </p>
                <small class="text-muted d-block">
                    All listed care and services under this plan are available while your subscription stays active.
                </small>
            </div>
        </div>
    </div>
    @endif

    @if($pendingSubscription)
    <div class="alert alert-warning mb-4">
        <div class="d-flex align-items-start">
            <i class="fas fa-clock fa-2x me-3 mt-1"></i>
            <div class="flex-grow-1">
                <h6 class="mb-1">Pending subscription</h6>
                <p class="mb-1">
                    <strong>{{ $pendingSubscription->plan?->name ?? 'Your plan' }}</strong>
                    @if($pendingSubscription->end_date)
                        — if approved, coverage runs through <strong>{{ $pendingSubscription->end_date->format('M d, Y') }}</strong>.
                    @else
                        — awaiting payment or approval before it becomes active.
                    @endif
                </p>
                <p class="mb-1 small">
                    Payment: <strong class="text-{{ $pendingSubscription->payment_status === 'paid' ? 'success' : 'dark' }}">{{ ucfirst(str_replace('_', ' ', $pendingSubscription->payment_status ?? 'pending')) }}</strong>
                    @if($pendingSubscription->payment_status !== 'paid')
                        — complete payment or verification steps to activate this plan.
                    @endif
                </p>
                <a href="{{ route('subscriptions.show', $pendingSubscription->id) }}" class="alert-link small">View subscription details</a>
            </div>
        </div>
    </div>
    @endif

    @if(!$activeSubscription && !$pendingSubscription)
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle me-2"></i>
            <div>
                <strong>No active subscription</strong> —
                <a href="{{ route('plans.index') }}" class="alert-link">Browse plans</a> to subscribe.
            </div>
        </div>
    </div>
    @endif

    @if($activeSubscription && isset($availablePlans) && $availablePlans->count() > 0)
    <div class="upgrade-section mb-4">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-sync-alt me-2"></i>Upgrade or Downgrade Your Plan
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    You can change your subscription plan anytime. Prorated refund will be applied for remaining days on your current plan.
                </p>
                <div class="row g-3">
                    @foreach($availablePlans as $plan)
                        @if(!$activeSubscription || !$activeSubscription->plan || $activeSubscription->plan->id != $plan->id)
                        <div class="col-12 col-md-6 col-lg-4 d-flex">
                            <div class="plan-card-upgrade w-100 d-flex flex-column h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2 flex-shrink-0">
                                    <h6 class="mb-0">{{ $plan->name }}</h6>
                                    @if($plan->is_popular)
                                    <span class="badge bg-warning text-dark">Popular</span>
                                    @endif
                                </div>
                                @if($plan->monthly_price)
                                <p class="text-muted small mb-2 flex-shrink-0">
                                    Starting from ₹{{ number_format($plan->monthly_price, 0) }}/month
                                </p>
                                @endif
                                <div class="plan-upgrade-body flex-grow-1">
                                    <div class="row g-2 g-md-0">
                                        <div class="col-12 col-md-6 plan-upgrade-col-benefits">
                                            @if($plan->description)
                                            <p class="small text-muted mb-2 mb-md-3">{{ $plan->description }}</p>
                                            @endif
                                            @if(is_array($plan->features) && count($plan->features))
                                            <p class="small fw-semibold text-secondary mb-1">
                                                <i class="fas fa-stethoscope me-1 text-primary"></i>What you get
                                            </p>
                                            <ul class="plan-upgrade-feature-list small mb-0">
                                                @foreach($plan->features as $feature)
                                                <li>{{ is_string($feature) ? $feature : ($feature['label'] ?? $feature['name'] ?? json_encode($feature)) }}</li>
                                                @endforeach
                                            </ul>
                                            @endif
                                        </div>
                                        <div class="col-12 col-md-6 plan-upgrade-col-payment">
                                            @if(isset($plan->payment_options) && is_array($plan->payment_options) && count($plan->payment_options))
                                            <p class="small fw-semibold text-secondary mb-2">
                                                <i class="fas fa-wallet me-1 text-primary"></i>Payment options
                                            </p>
                                            <div class="plan-upgrade-payment-list">
                                                @foreach($plan->payment_options as $frequency => $option)
                                                <div class="plan-upgrade-payment-row">
                                                    <div class="plan-upgrade-payment-label">
                                                        <strong>{{ $option['label'] ?? ucfirst(str_replace('_', ' ', (string) $frequency)) }}</strong>
                                                        @if(! empty($option['description']))
                                                        <small class="d-block text-muted">{{ $option['description'] }}</small>
                                                        @endif
                                                    </div>
                                                    <div class="plan-upgrade-payment-amount">
                                                        ₹{{ number_format((float) ($option['price'] ?? 0), 0) }}
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-auto pt-2 border-top flex-shrink-0">
                                    <a href="{{ route('plans.show', $plan->id) }}?upgrade=1&current_subscription={{ $activeSubscription ? $activeSubscription->id : '' }}" 
                                       class="btn btn-sm btn-outline-primary w-100">
                                        @php
                                            $isDowngrade = $activeSubscription && $activeSubscription->plan && ($activeSubscription->plan->price ?? 0) > ($plan->price ?? 0);
                                        @endphp
                                        <i class="fas fa-arrow-{{ $isDowngrade ? 'down' : 'up' }} me-1"></i>
                                        {{ $isDowngrade ? 'Downgrade' : 'Upgrade' }} to {{ $plan->name }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Subscriptions List -->
    <div class="row g-3">
        @php
            $subscriptionsToShow = isset($filteredSubscriptions) ? $filteredSubscriptions : $subscriptions;
        @endphp
        @forelse($subscriptionsToShow as $subscription)
        <div class="col-12">
            <div class="subscription-card status-{{ $subscription->status }}">
                <div class="subscription-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="subscription-plan-name mb-1">{{ $subscription->plan?->name ?? 'Unknown Plan' }}</h5>
                            @if($subscription->plan)
                            <p class="subscription-members text-muted small mb-0">
                                <i class="fas fa-users me-1"></i> {{ $subscription->plan->members_included }}
                            </p>
                            @endif
                        </div>
                        <span class="subscription-badge badge bg-{{ $subscription->status_color }}">
                            {{ $subscription->status_display }}
                        </span>
                    </div>
                </div>

                <div class="subscription-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="subscription-info-item">
                                <small class="text-muted d-block">Payment Frequency</small>
                                <strong>{{ ucfirst(str_replace('_', ' ', $subscription->payment_frequency)) }}</strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="subscription-info-item">
                                <small class="text-muted d-block">Total Amount</small>
                                <strong>₹{{ number_format($subscription->total_amount, 0) }}</strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="subscription-info-item">
                                <small class="text-muted d-block">Paid Amount</small>
                                <strong class="text-success">₹{{ number_format($subscription->paid_amount, 0) }}</strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="subscription-info-item">
                                <small class="text-muted d-block">Payment Status</small>
                                <strong class="text-{{ $subscription->payment_status === 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $subscription->payment_status)) }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    @php
                        $displayFeatures = $subscription->planFeaturesForDisplay();
                    @endphp
                    <div class="mt-3 pt-3 border-top">
                        @include('plans::subscriptions.partials.enrolled-package-summary', ['subscription' => $subscription, 'variant' => 'patient'])
                    </div>

                    @if($subscription->plan && ($subscription->plan->description || count($displayFeatures)))
                    <div class="subscription-care-facilities mt-3 pt-3 border-top">
                        <h6 class="mb-2">
                            <i class="fas fa-stethoscope me-2 text-primary"></i>Care &amp; facilities included with this plan
                        </h6>
                        @if($subscription->plan->description)
                        <p class="small text-muted mb-2 mb-md-3">{{ $subscription->plan->description }}</p>
                        @endif
                        @if(count($displayFeatures))
                        <ul class="subscription-feature-list small mb-0">
                            @foreach($displayFeatures as $feature)
                            <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    @endif

                    <div class="subscription-dates mt-3 pt-3 border-top">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted d-block">Start Date</small>
                                <strong>{{ $subscription->start_date->format('M d, Y') }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">End Date</small>
                                <strong>{{ $subscription->end_date->format('M d, Y') }}</strong>
                            </div>
                        </div>
                    </div>

                    @if($subscription->payment_screenshot || $subscription->transaction_id)
                    <div class="payment-proof-section mt-3 pt-3 border-top">
                        <h6 class="mb-2">
                            <i class="fas fa-receipt me-2"></i>Payment Details
                        </h6>
                        @if($subscription->payment_screenshot)
                        <div class="payment-proof-item mb-2">
                            <strong class="d-block mb-2">
                                <i class="fas fa-image me-2"></i>Payment Screenshot:
                            </strong>
                            <a href="{{ route('subscriptions.payment-screenshot', ['id' => $subscription->id]) }}" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>View Screenshot
                            </a>
                        </div>
                        @endif
                        @if($subscription->transaction_id)
                        <div class="payment-proof-item mb-2">
                            <strong class="d-block mb-1">
                                <i class="fas fa-receipt me-2"></i>Transaction ID:
                            </strong>
                            <code>{{ $subscription->transaction_id }}</code>
                        </div>
                        @endif
                        @if($subscription->payment_notes)
                        <div class="payment-proof-item">
                            <strong class="d-block mb-1">
                                <i class="fas fa-sticky-note me-2"></i>Notes:
                            </strong>
                            <p class="mb-0 small">{{ $subscription->payment_notes }}</p>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="subscription-actions">
                    <a href="{{ route('subscriptions.show', $subscription->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i>View Details
                    </a>
                    @if(in_array($subscription->status, ['pending', 'cancelled']))
                    <button class="btn btn-outline-danger btn-sm" onclick="deleteSubscription({{ $subscription->id }})">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                    @endif
                    @if($subscription->status === 'active' && $subscription->end_date > now())
                    <button class="btn btn-outline-danger btn-sm" onclick="cancelSubscription({{ $subscription->id }})">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    @endif
                    @if($subscription->status === 'expired')
                    <button class="btn btn-primary btn-sm" onclick="renewSubscription({{ $subscription->id }})">
                        <i class="fas fa-redo me-1"></i>Renew
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="empty-state-card">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Subscriptions</h5>
                <p class="text-muted">You haven't subscribed to any plans yet.</p>
                <a href="{{ route('plans.index') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-plus me-2"></i>Browse Plans
                </a>
            </div>
        </div>
        @endforelse
    </div>
</div>
</div>
<style>
.subscription-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 16px;
    border-left: 4px solid #dee2e6;
}

.subscription-card.status-active {
    border-left-color: #28a745;
}

.subscription-card.status-pending {
    border-left-color: #ffc107;
}

.subscription-card.status-expired {
    border-left-color: #dc3545;
}

.subscription-card.status-cancelled {
    border-left-color: #6c757d;
}

.subscription-header {
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e9ecef;
}

.subscription-plan-name {
    font-size: 18px;
    font-weight: 700;
    color: #212529;
}

.subscription-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.subscription-enrolled-package {
    background: #f0f7ff;
    border-radius: 12px;
    padding: 14px 16px;
    border: 1px solid #cfe2ff;
}

.subscription-care-facilities {
    background: #f8f9fc;
    border-radius: 12px;
    padding: 14px 16px;
}

.subscription-feature-list {
    padding-left: 1.15rem;
    margin-bottom: 0;
}

.subscription-feature-list li {
    margin-bottom: 0.35rem;
}

.subscription-feature-list li:last-child {
    margin-bottom: 0;
}

.subscription-info-item {
    text-align: center;
}

.subscription-info-item strong {
    display: block;
    font-size: 16px;
    color: #212529;
    margin-top: 4px;
}

.subscription-actions {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.payment-proof-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 12px;
}

.payment-proof-item {
    margin-bottom: 8px;
}

.payment-proof-item:last-child {
    margin-bottom: 0;
}

.empty-state-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 60px 20px;
    text-align: center;
}

.upgrade-section .card {
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.upgrade-section .card-header {
    border-radius: 16px 16px 0 0 !important;
}

.plan-card-upgrade {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    padding: 16px;
    transition: all 0.3s ease;
}

.plan-upgrade-body {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 4px;
}

@media (min-width: 768px) {
    .plan-upgrade-col-payment {
        border-left: 1px solid #e9ecef;
        padding-left: 14px !important;
    }

    .plan-upgrade-col-benefits {
        padding-right: 14px !important;
    }
}

@media (max-width: 767.98px) {
    .plan-upgrade-col-payment {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e9ecef;
    }
}

.plan-upgrade-feature-list {
    padding-left: 1.1rem;
    margin-bottom: 0;
}

.plan-upgrade-feature-list li {
    margin-bottom: 0.3rem;
}

.plan-upgrade-feature-list li:last-child {
    margin-bottom: 0;
}

.plan-upgrade-payment-list {
    padding-top: 2px;
}

.plan-upgrade-payment-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.8125rem;
}

.plan-upgrade-payment-row:last-child {
    border-bottom: none;
    padding-bottom: 2px;
}

.plan-upgrade-payment-label {
    flex: 1 1 auto;
    min-width: 0;
}

.plan-upgrade-payment-label strong {
    font-size: 0.8125rem;
}

.plan-upgrade-payment-amount {
    flex: 0 0 auto;
    font-weight: 700;
    color: #495057;
    white-space: nowrap;
}

.plan-card-upgrade:hover {
    background: #fff;
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .subscription-card {
        padding: 16px;
    }
    
    .subscription-info-item {
        text-align: left;
        margin-bottom: 12px;
    }
}
</style>

<script>
function deleteSubscription(id) {
    if (confirm('Are you sure you want to delete this subscription? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ url("subscriptions") }}/' + id;
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        // Add method spoofing for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function cancelSubscription(id) {
    if (confirm('Are you sure you want to cancel this subscription?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("subscriptions.cancel", ":id") }}'.replace(':id', id);
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        // Add method spoofing for POST
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'POST';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function renewSubscription(id) {
    if (confirm('Do you want to renew this subscription?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("subscriptions.renew", ":id") }}'.replace(':id', id);
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        // Add method spoofing for POST
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'POST';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection


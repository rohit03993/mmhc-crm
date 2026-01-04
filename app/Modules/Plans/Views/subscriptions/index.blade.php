@extends('auth::layout')

@section('content')
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
    <!-- Desktop Header -->
    <div class="d-none d-md-block mb-4">
        <h4 class="page-title">My Subscriptions</h4>
        <p class="text-muted">Manage your healthcare subscription plans</p>
    </div>

    <!-- Active Subscription Banner -->
    @if($activeSubscription)
    <div class="alert alert-success mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h6 class="mb-1">Active Subscription</h6>
                <p class="mb-0">
                    <strong>{{ $activeSubscription->plan->name ?? 'Unknown Plan' }}</strong> - 
                    Expires on {{ $activeSubscription->end_date->format('M d, Y') }}
                    ({{ $activeSubscription->days_remaining }} days remaining)
                </p>
                <small class="text-muted">
                    All services are FREE while your subscription is active!
                </small>
            </div>
        </div>
    </div>
    
    <!-- Upgrade/Downgrade Section -->
    @if(isset($availablePlans) && $availablePlans->count() > 0)
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
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="plan-card-upgrade h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">{{ $plan->name }}</h6>
                                    @if($plan->is_popular)
                                    <span class="badge bg-warning text-dark">Popular</span>
                                    @endif
                                </div>
                                @if($plan->monthly_price)
                                <p class="text-muted small mb-2">
                                    Starting from ₹{{ number_format($plan->monthly_price, 0) }}/month
                                </p>
                                @endif
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
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
    @else
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle me-2"></i>
            <div>
                <strong>No Active Subscription</strong> - 
                <a href="{{ route('plans.index') }}" class="alert-link">Subscribe to a plan</a> to get FREE services!
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
                            <h5 class="subscription-plan-name mb-1">{{ $subscription->plan->name ?? 'Unknown Plan' }}</h5>
                            @if($subscription->plan)
                            <p class="subscription-members text-muted small mb-0">
                                <i class="fas fa-users me-1"></i> {{ $subscription->plan->members_included }}
                            </p>
                            @endif
                        </div>
                        <span class="subscription-badge badge-{{ $subscription->status_color }}">
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

                    @if($subscription->payable_years > 0 || $subscription->care_benefits_years > 0)
                    <div class="subscription-benefits mt-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-gift text-primary me-2"></i>
                            <small class="text-muted">
                                <strong>{{ $subscription->payable_years }} years payable</strong> + 
                                <strong>{{ $subscription->care_benefits_years }} years extra</strong> = 
                                <strong class="text-primary">{{ $subscription->payable_years + $subscription->care_benefits_years }} years total care</strong>
                            </small>
                        </div>
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

@include('auth::components.bottom-nav')

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


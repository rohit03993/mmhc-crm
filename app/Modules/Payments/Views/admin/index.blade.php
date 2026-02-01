@extends('auth::layout')

@section('title', 'Staff Payment Management - Admin')

@section('head')
<style>
    .payment-type-card {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    .payment-type-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
    }
    .payment-type-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .staff-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .payment-breakdown {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    .breakdown-item {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
    }
    .breakdown-amount {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2563eb;
    }
    .breakdown-label {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }
    .filter-tabs {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
    }
    .filter-tab {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
    }
    .filter-tab.active {
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        color: white;
    }
    .filter-tab:not(.active) {
        background: #f8f9fa;
        color: #6c757d;
    }
    .total-pending-banner {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        text-align: center;
    }
    .total-pending-amount {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-money-bill-wave me-2"></i>Staff Payment Management
        </h2>
        <a href="{{ route('admin.payments.history') }}" class="btn btn-outline-primary">
            <i class="fas fa-history me-1"></i>Payment History
        </a>
    </div>

    <!-- Total Pending Banner -->
    <div class="total-pending-banner">
        <div class="total-pending-amount">₹{{ number_format($totalPending, 2) }}</div>
        <div>Total Pending Payments</div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="{{ route('admin.payments.index', ['type' => 'all']) }}" 
           class="filter-tab {{ $filterType === 'all' ? 'active' : '' }}">
            All Payments
        </a>
        <a href="{{ route('admin.payments.index', ['type' => 'service_request']) }}" 
           class="filter-tab {{ $filterType === 'service_request' ? 'active' : '' }}">
            Service Requests
        </a>
        <a href="{{ route('admin.payments.index', ['type' => 'patient_reward']) }}" 
           class="filter-tab {{ $filterType === 'patient_reward' ? 'active' : '' }}">
            Patient Rewards
        </a>
        <a href="{{ route('admin.payments.index', ['type' => 'subscription_referral']) }}" 
           class="filter-tab {{ $filterType === 'subscription_referral' ? 'active' : '' }}">
            Subscription Referrals
        </a>
    </div>

    @if(count($pendingPayments) > 0)
        @foreach($pendingPayments as $item)
            <div class="staff-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">{{ $item['staff']->name }}</h5>
                        <p class="text-muted mb-0">
                            <i class="fas fa-user-tag me-1"></i>{{ ucfirst($item['staff']->role) }}
                            <span class="ms-3">
                                <i class="fas fa-phone me-1"></i>{{ $item['staff']->phone }}
                            </span>
                        </p>
                    </div>
                    <div class="text-end">
                        <div class="h4 mb-0 text-primary">₹{{ number_format($item['payments']['total'], 2) }}</div>
                        <small class="text-muted">Total Pending</small>
                    </div>
                </div>

                <!-- Payment Breakdown -->
                <div class="payment-breakdown">
                    <div class="breakdown-item">
                        <div class="breakdown-amount">₹{{ number_format($item['payments']['service_request']['amount'], 2) }}</div>
                        <div class="breakdown-label">
                            <i class="fas fa-briefcase me-1"></i>Service Requests
                            <br><small>({{ $item['payments']['service_request']['count'] }} pending)</small>
                        </div>
                    </div>
                    <div class="breakdown-item">
                        <div class="breakdown-amount">₹{{ number_format($item['payments']['patient_reward']['amount'], 2) }}</div>
                        <div class="breakdown-label">
                            <i class="fas fa-gift me-1"></i>Patient Rewards
                            <br><small>({{ $item['payments']['patient_reward']['count'] }} entries)</small>
                            @if(isset($item['payments']['patient_reward']['meets_threshold']) && !$item['payments']['patient_reward']['meets_threshold'])
                                <br><small class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Below ₹500 threshold</small>
                            @endif
                        </div>
                    </div>
                    <div class="breakdown-item">
                        <div class="breakdown-amount">₹{{ number_format($item['payments']['subscription_referral']['amount'], 2) }}</div>
                        <div class="breakdown-label">
                            <i class="fas fa-star me-1"></i>Subscription Referrals
                            <br><small>({{ $item['payments']['subscription_referral']['count'] }} subscriptions)</small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    @if($item['payments']['service_request']['amount'] > 0)
                        <a href="{{ route('admin.payments.form', ['staff' => $item['staff']->id, 'type' => 'service_request']) }}" 
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-money-bill me-1"></i>Pay Service Requests
                        </a>
                    @endif
                    @if($item['payments']['patient_reward']['amount'] > 0)
                        <a href="{{ route('admin.payments.form', ['staff' => $item['staff']->id, 'type' => 'patient_reward']) }}" 
                           class="btn btn-sm btn-warning position-relative">
                            <i class="fas fa-gift me-1"></i>Pay Patient Rewards
                            @if(isset($item['payments']['patient_reward']['meets_threshold']) && !$item['payments']['patient_reward']['meets_threshold'])
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" title="Below ₹500 threshold - Admin can still process payment">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                            @endif
                        </a>
                    @endif
                    @if($item['payments']['subscription_referral']['amount'] > 0)
                        <a href="{{ route('admin.payments.form', ['staff' => $item['staff']->id, 'type' => 'subscription_referral']) }}" 
                           class="btn btn-sm btn-success">
                            <i class="fas fa-star me-1"></i>Pay Subscription Referrals
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5>No Pending Payments</h5>
                <p class="text-muted">All staff payments have been processed.</p>
            </div>
        </div>
    @endif
</div>
@endsection


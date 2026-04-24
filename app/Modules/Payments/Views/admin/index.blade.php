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
    .totals-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        margin-top: 0.75rem;
    }
    .totals-item {
        background: rgba(255,255,255,0.16);
        border: 1px solid rgba(255,255,255,0.25);
        border-radius: 10px;
        padding: 0.75rem 1rem;
    }
    .totals-label {
        font-size: 0.8rem;
        opacity: 0.9;
    }
    .totals-value {
        font-size: 1.25rem;
        font-weight: 700;
    }
    .history-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .quick-pay-card {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
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
        <div>Payable Now (Auto-Settlement)</div>
        <div class="totals-row">
            <div class="totals-item">
                <div class="totals-label">Payable Now</div>
                <div class="totals-value">₹{{ number_format($totalPending, 2) }}</div>
            </div>
            <div class="totals-item">
                <div class="totals-label">Service Queue Total</div>
                <div class="totals-value">₹{{ number_format($totalServiceQueue ?? 0, 2) }}</div>
            </div>
        </div>
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

    <!-- All Staff Payment Actions -->
    <div class="mt-4">
        <h5 class="mb-3">
            <i class="fas fa-users-cog me-2"></i>All Staff Payment Actions
        </h5>
        @if(isset($staffMembers) && $staffMembers->count() > 0)
            @foreach($staffMembers as $staff)
                <div class="quick-pay-card">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div>
                            <strong>{{ $staff->name }}</strong>
                            <small class="text-muted d-block">{{ ucfirst($staff->role) }} • {{ $staff->phone }}</small>
                            @php
                                $overview = $staffPaymentOverview[$staff->id] ?? null;
                            @endphp
                            @if($overview)
                                <small class="text-muted d-block">
                                    Payable Now: <strong>₹{{ number_format($overview['payable_now_total'] ?? 0, 2) }}</strong>
                                    @if(($overview['service_queue_total'] ?? 0) > ($overview['service_payable_now'] ?? 0))
                                        • Service Queue: <strong>₹{{ number_format($overview['service_queue_total'] ?? 0, 2) }}</strong>
                                    @endif
                                </small>
                            @endif
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.payments.form', ['staff' => $staff->id, 'type' => 'service_request']) }}" class="btn btn-sm btn-primary">
                                Service ₹{{ number_format($overview['service_queue_total'] ?? 0, 0) }}
                            </a>
                            <a href="{{ route('admin.payments.form', ['staff' => $staff->id, 'type' => 'patient_reward']) }}" class="btn btn-sm btn-warning">
                                Reward ₹{{ number_format($overview['patient_reward'] ?? 0, 0) }}
                            </a>
                            <a href="{{ route('admin.payments.form', ['staff' => $staff->id, 'type' => 'subscription_referral']) }}" class="btn btn-sm btn-success">
                                Subscription ₹{{ number_format($overview['subscription_referral'] ?? 0, 0) }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Recent Processed Payments -->
    <div class="mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-history me-2"></i>Recent Processed Payments
            </h5>
            <div class="text-muted">
                <small>Total Paid: <strong>₹{{ number_format($totalPaidOverall ?? 0, 2) }}</strong></small>
            </div>
        </div>

        @if(isset($recentPayments) && $recentPayments->count() > 0)
            @foreach($recentPayments as $payment)
                <div class="history-card">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div><strong>{{ $payment->staff->name ?? 'N/A' }}</strong></div>
                            <small class="text-muted">{{ ucfirst($payment->staff->role ?? 'staff') }}</small>
                        </div>
                        <div class="col-md-2">
                            <span class="badge bg-light text-dark">
                                {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}
                            </span>
                        </div>
                        <div class="col-md-2">
                            <strong class="text-primary">₹{{ number_format($payment->amount, 2) }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">
                                {{ optional($payment->paid_at)->format('M d, Y h:i A') }}
                            </small>
                            @if($payment->transaction_id)
                                <small class="text-muted d-block">
                                    Txn: {{ $payment->transaction_id }}
                                </small>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">
                                By: {{ $payment->admin->name ?? 'Admin' }}
                            </small>
                            @if($payment->payment_screenshot)
                                <a href="{{ storage_asset($payment->payment_screenshot) }}" target="_blank" class="btn btn-sm btn-outline-secondary mt-1">
                                    <i class="fas fa-image me-1"></i>Screenshot
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="card">
                <div class="card-body text-center py-4">
                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">No processed payments found yet.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection


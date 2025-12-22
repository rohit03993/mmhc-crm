@extends('auth::layout')

@section('content')
<div class="container-fluid px-3 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">Subscription Management</h4>
    </div>

    <!-- Statistics Cards -->
    @if(isset($stats))
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card stat-total">
                <div class="stat-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['total_subscriptions'] }}</div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-active">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['active_subscriptions'] }}</div>
                    <div class="stat-label">Active</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-pending">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['pending_subscriptions'] }}</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-expired">
                <div class="stat-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['expired_subscriptions'] }}</div>
                    <div class="stat-label">Expired</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filter Tabs -->
    <div class="filter-tabs mb-4">
        <a href="{{ route('admin.subscriptions') }}?status=all" 
           class="filter-tab {{ request('status') == 'all' || !request('status') ? 'active' : '' }}">
            All ({{ $counts['all'] ?? 0 }})
        </a>
        <a href="{{ route('admin.subscriptions') }}?status=pending" 
           class="filter-tab {{ request('status') == 'pending' ? 'active' : '' }}">
            Pending ({{ $counts['pending'] ?? 0 }})
        </a>
        <a href="{{ route('admin.subscriptions') }}?status=active" 
           class="filter-tab {{ request('status') == 'active' ? 'active' : '' }}">
            Active ({{ $counts['active'] ?? 0 }})
        </a>
        <a href="{{ route('admin.subscriptions') }}?status=expired" 
           class="filter-tab {{ request('status') == 'expired' ? 'active' : '' }}">
            Expired ({{ $counts['expired'] ?? 0 }})
        </a>
    </div>

    <!-- Subscriptions List -->
    <div class="subscriptions-list">
        @forelse($subscriptions as $subscription)
        <div class="subscription-card">
            <div class="subscription-card-header">
                <div>
                    <h5 class="subscription-user-name">{{ $subscription->user->name }}</h5>
                    <p class="subscription-plan-name mb-0">{{ $subscription->plan->name }}</p>
                </div>
                <span class="badge badge-{{ $subscription->status_color }}">
                    {{ $subscription->status_display }}
                </span>
            </div>

            <div class="subscription-card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <small class="text-muted d-block">Amount</small>
                        <strong>₹{{ number_format($subscription->total_amount, 0) }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted d-block">Payment Status</small>
                        <strong class="text-{{ $subscription->payment_status === 'paid' ? 'success' : 'warning' }}">
                            {{ ucfirst(str_replace('_', ' ', $subscription->payment_status)) }}
                        </strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted d-block">Start Date</small>
                        <strong>{{ $subscription->start_date->format('M d, Y') }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted d-block">End Date</small>
                        <strong>{{ $subscription->end_date->format('M d, Y') }}</strong>
                    </div>
                </div>

                @if($subscription->payment_screenshot || $subscription->transaction_id)
                <div class="payment-proof-section mt-3">
                    <strong class="d-block mb-2">
                        <i class="fas fa-receipt me-2"></i>Payment Proof:
                    </strong>
                    @if($subscription->payment_screenshot)
                    <a href="{{ asset('storage/' . $subscription->payment_screenshot) }}" 
                       target="_blank" 
                       class="btn btn-sm btn-outline-primary me-2">
                        <i class="fas fa-image me-1"></i>View Screenshot
                    </a>
                    @endif
                    @if($subscription->transaction_id)
                    <code class="me-2">{{ $subscription->transaction_id }}</code>
                    @endif
                </div>
                @endif

                <!-- Subscription Actions -->
                @if($subscription->status === 'pending')
                <div class="subscription-status-actions mt-3">
                    <form action="{{ route('admin.subscriptions.approve', $subscription) }}" 
                          method="POST" 
                          class="d-inline me-2">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-check me-1"></i>Approve Subscription
                        </button>
                    </form>
                    <form action="{{ route('admin.subscriptions.reject', $subscription) }}" 
                          method="POST" 
                          class="d-inline"
                          onsubmit="return confirm('Are you sure you want to reject this subscription?');">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-times me-1"></i>Reject Subscription
                        </button>
                    </form>
                </div>
                @endif

                <!-- Payment Verification Actions -->
                @if($subscription->payment_status !== 'paid' && ($subscription->payment_screenshot || $subscription->transaction_id))
                <div class="payment-actions mt-3">
                    <form action="{{ route('admin.subscriptions.verify-payment', $subscription) }}" 
                          method="POST" 
                          class="d-inline me-2">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-check-circle me-1"></i>Verify Payment
                        </button>
                    </form>
                    <button type="button" 
                            class="btn btn-danger btn-sm" 
                            onclick="showRejectModal({{ $subscription->id }})">
                        <i class="fas fa-times-circle me-1"></i>Reject Payment
                    </button>
                </div>
                @endif
            </div>

            <div class="subscription-card-footer">
                <a href="{{ route('admin.subscriptions.view', $subscription) }}" 
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-eye me-1"></i>View Details
                </a>
                <small class="text-muted ms-3">
                    Created: {{ $subscription->created_at->format('M d, Y') }}
                </small>
            </div>
        </div>
        @empty
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No subscriptions found.
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($subscriptions->hasPages())
    <div class="mt-4">
        {{ $subscriptions->links() }}
    </div>
    @endif
</div>

<!-- Reject Payment Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason">Reason for Rejection</label>
                        <textarea name="rejection_reason" 
                                  id="rejection_reason" 
                                  class="form-control" 
                                  rows="4" 
                                  required
                                  placeholder="Please provide a reason for rejecting this payment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.filter-tabs {
    display: flex;
    gap: 8px;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 8px;
}

.filter-tab {
    padding: 8px 16px;
    text-decoration: none;
    color: #6c757d;
    border-radius: 8px 8px 0 0;
    font-weight: 600;
    transition: all 0.2s;
}

.filter-tab.active {
    color: #667eea;
    background: #f8f9fa;
    border-bottom: 2px solid #667eea;
}

.subscription-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 16px;
}

.subscription-card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    padding-bottom: 16px;
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 16px;
}

.subscription-user-name {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 4px;
}

.subscription-plan-name {
    color: #6c757d;
    font-size: 14px;
}

.payment-proof-section {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

.subscription-status-actions {
    padding: 12px;
    background: #fff3cd;
    border-radius: 8px;
    border: 1px solid #ffc107;
}

.payment-actions {
    padding: 12px;
    background: #d1ecf1;
    border-radius: 8px;
    border: 1px solid #0dcaf0;
    margin-top: 12px;
}

.subscription-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid #e9ecef;
    margin-top: 16px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-total .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-active .stat-icon {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.stat-pending .stat-icon {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
}

.stat-expired .stat-icon {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #212529;
    line-height: 1;
}

.stat-label {
    font-size: 14px;
    color: #6c757d;
    margin-top: 4px;
}
</style>

<script>
function showRejectModal(subscriptionId) {
    const form = document.getElementById('rejectForm');
    form.action = `/admin/subscriptions/${subscriptionId}/reject-payment`;
    
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
@endsection


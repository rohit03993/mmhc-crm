@extends('auth::layout')

@section('content')
<div class="container-fluid px-3 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.subscriptions') }}" class="btn btn-link text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Back to Subscriptions
            </a>
            <h4 class="page-title mb-0 mt-2">Subscription Details</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-8">
            <!-- Subscription Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Subscription Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">User</small>
                            <strong>{{ $subscription->user->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $subscription->user->email }}</small>
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">Plan</small>
                            <strong>{{ $subscription->plan->name }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge badge-{{ $subscription->status_color }} fs-6">
                                {{ $subscription->status_display }}
                            </span>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Payment Frequency</small>
                            <strong>{{ ucfirst(str_replace('_', ' ', $subscription->payment_frequency)) }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Total Amount</small>
                            <strong>₹{{ number_format($subscription->total_amount, 0) }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Paid Amount</small>
                            <strong class="text-success">₹{{ number_format($subscription->paid_amount, 0) }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Payment Status</small>
                            <strong class="text-{{ $subscription->payment_status === 'paid' ? 'success' : ($subscription->payment_status === 'failed' ? 'danger' : 'warning') }}">
                                {{ ucfirst(str_replace('_', ' ', $subscription->payment_status)) }}
                            </strong>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">Start Date</small>
                            <strong>{{ $subscription->start_date->format('M d, Y') }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">End Date</small>
                            <strong>{{ $subscription->end_date->format('M d, Y') }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">Days Remaining</small>
                            <strong>{{ $subscription->days_remaining }} days</strong>
                        </div>
                    </div>

                    @if($subscription->payable_years > 0 || $subscription->care_benefits_years > 0)
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-gift me-2"></i>
                        <strong>{{ $subscription->payable_years }} years payable</strong> + 
                        <strong>{{ $subscription->care_benefits_years }} years extra</strong> = 
                        <strong>{{ $subscription->payable_years + $subscription->care_benefits_years }} years total care coverage</strong>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payment Proof Card -->
            @if($subscription->payment_screenshot || $subscription->transaction_id)
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>Payment Proof
                    </h5>
                </div>
                <div class="card-body">
                    @if($subscription->payment_screenshot)
                    <div class="mb-3">
                        <strong class="d-block mb-2">
                            <i class="fas fa-image me-2"></i>Payment Screenshot:
                        </strong>
                        <a href="{{ asset('storage/' . $subscription->payment_screenshot) }}" 
                           target="_blank" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>View Screenshot
                        </a>
                        <img src="{{ asset('storage/' . $subscription->payment_screenshot) }}" 
                             alt="Payment Screenshot" 
                             class="img-fluid mt-3 rounded"
                             style="max-height: 400px;">
                    </div>
                    @endif

                    @if($subscription->transaction_id)
                    <div class="mb-3">
                        <strong class="d-block mb-2">
                            <i class="fas fa-receipt me-2"></i>Transaction ID:
                        </strong>
                        <code class="fs-5">{{ $subscription->transaction_id }}</code>
                    </div>
                    @endif

                    @if($subscription->payment_notes)
                    <div class="mb-3">
                        <strong class="d-block mb-2">
                            <i class="fas fa-sticky-note me-2"></i>Payment Notes:
                        </strong>
                        <p class="mb-0">{{ $subscription->payment_notes }}</p>
                    </div>
                    @endif

                    @if($subscription->payment_status !== 'paid' && ($subscription->payment_screenshot || $subscription->transaction_id))
                    <div class="payment-verification-actions mt-4 pt-3 border-top">
                        <h6 class="mb-3">Payment Verification</h6>
                        <form action="{{ route('admin.subscriptions.verify-payment', $subscription) }}" 
                              method="POST" 
                              class="d-inline me-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle me-2"></i>Verify Payment & Activate Subscription
                            </button>
                        </form>
                        <button type="button" 
                                class="btn btn-danger btn-lg" 
                                onclick="showRejectModal()">
                            <i class="fas fa-times-circle me-2"></i>Reject Payment
                        </button>
                    </div>
                    @endif

                    @if($subscription->payment_verified_by)
                    <div class="alert alert-success mt-3 mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Payment Verified</strong> by {{ $subscription->paymentVerifiedBy->name ?? 'Admin' }} 
                        on {{ $subscription->payment_verified_at->format('M d, Y h:i A') }}
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Subscription Actions Card -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cog me-2"></i>Subscription Actions
                    </h5>
                </div>
                <div class="card-body">
                    @if($subscription->status === 'pending')
                    <div class="subscription-actions">
                        <form action="{{ route('admin.subscriptions.approve', $subscription) }}" 
                              method="POST" 
                              class="d-inline me-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check me-2"></i>Approve Subscription
                            </button>
                        </form>
                        <form action="{{ route('admin.subscriptions.reject', $subscription) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Are you sure you want to reject this subscription?');">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="fas fa-times me-2"></i>Reject Subscription
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Subscription status is <strong>{{ $subscription->status_display }}</strong>. 
                        @if($subscription->approved_by)
                        Approved by {{ $subscription->approvedBy->name ?? 'Admin' }} 
                        on {{ $subscription->approved_at->format('M d, Y h:i A') }}
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-12 col-lg-4">
            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Quick Stats
                    </h5>
                </div>
                <div class="card-body">
                    <div class="stat-item mb-3">
                        <small class="text-muted d-block">Subscription ID</small>
                        <strong>#{{ $subscription->id }}</strong>
                    </div>
                    <div class="stat-item mb-3">
                        <small class="text-muted d-block">Created</small>
                        <strong>{{ $subscription->created_at->format('M d, Y h:i A') }}</strong>
                    </div>
                    <div class="stat-item mb-3">
                        <small class="text-muted d-block">Last Updated</small>
                        <strong>{{ $subscription->updated_at->format('M d, Y h:i A') }}</strong>
                    </div>
                    @if($subscription->auto_renew)
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-sync me-2"></i>Auto-renewal enabled
                    </div>
                    @endif
                </div>
            </div>

            <!-- User Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>User Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="user-info-item mb-3">
                        <small class="text-muted d-block">Name</small>
                        <strong>{{ $subscription->user->name }}</strong>
                    </div>
                    <div class="user-info-item mb-3">
                        <small class="text-muted d-block">Email</small>
                        <strong>{{ $subscription->user->email }}</strong>
                    </div>
                    <div class="user-info-item mb-3">
                        <small class="text-muted d-block">Phone</small>
                        <strong>{{ $subscription->user->phone ?? 'N/A' }}</strong>
                    </div>
                    <div class="user-info-item">
                        <small class="text-muted d-block">User ID</small>
                        <strong>{{ $subscription->user->unique_id ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Payment Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.subscriptions.reject-payment', $subscription) }}" method="POST">
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
                        <small class="text-muted">This reason will be visible to the user.</small>
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
.card {
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-radius: 12px;
    margin-bottom: 20px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    font-weight: 600;
}

.stat-item, .user-info-item {
    padding-bottom: 12px;
    border-bottom: 1px solid #e9ecef;
}

.stat-item:last-child, .user-info-item:last-child {
    border-bottom: none;
}

.payment-verification-actions {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.subscription-actions {
    text-align: center;
}
</style>

<script>
function showRejectModal() {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
@endsection


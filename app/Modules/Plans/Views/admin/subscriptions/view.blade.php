@extends('auth::layout')

@section('content')
@php
    $paymentProvider = $subscription->payment_provider ?: (($subscription->payment_screenshot || $subscription->transaction_id) ? 'manual' : null);
    $gatewayMethod = strtolower((string) data_get($subscription->gateway_payload, 'method', ''));
    $paymentModeLabel = match (true) {
        $paymentProvider === 'razorpay' && $gatewayMethod !== '' => strtoupper($gatewayMethod),
        $paymentProvider === 'razorpay' => 'ONLINE',
        $paymentProvider === 'manual' => 'MANUAL PROOF',
        default => 'N/A',
    };
@endphp
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
                            <span class="badge bg-{{ $subscription->status_color }} fs-6">
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

                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Payment Provider</small>
                            <span class="badge rounded-pill {{ $paymentProvider === 'razorpay' ? 'bg-info text-dark' : 'bg-secondary text-white' }}">
                                {{ strtoupper((string) ($paymentProvider ?: 'N/A')) }}
                            </span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Payment Mode</small>
                            <span class="badge rounded-pill bg-light text-dark border">
                                {{ $paymentModeLabel }}
                            </span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Gateway Status</small>
                            <span class="badge rounded-pill {{ in_array($subscription->gateway_status, ['captured', 'processed']) ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ ucfirst((string) ($subscription->gateway_status ?: 'N/A')) }}
                            </span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Transaction ID</small>
                            <strong class="small text-break">{{ $subscription->transaction_id ?: 'N/A' }}</strong>
                        </div>
                    </div>

                    @if($subscription->payment_provider === 'razorpay' || $subscription->razorpay_order_id || $subscription->razorpay_payment_id)
                    <div class="pmt-meta-box">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Razorpay Order ID</small>
                                <code class="pmt-code">{{ $subscription->razorpay_order_id ?: 'N/A' }}</code>
                            </div>
                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Razorpay Payment ID</small>
                                <code class="pmt-code">{{ $subscription->razorpay_payment_id ?: 'N/A' }}</code>
                            </div>
                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Webhook Event ID</small>
                                <code class="pmt-code">{{ $subscription->razorpay_event_id ?: 'N/A' }}</code>
                            </div>
                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Webhook Received At</small>
                                <strong>{{ $subscription->webhook_received_at ? $subscription->webhook_received_at->format('M d, Y h:i A') : 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>
                    @endif

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

                    @include('plans::subscriptions.partials.enrolled-package-summary', ['subscription' => $subscription, 'variant' => 'admin'])

                    @if($subscription->isDemoSeeded())
                    <div class="border-top pt-3 mt-3">
                        <h6 class="text-muted small text-uppercase mb-2">Demo data tools</h6>
                        <p class="small text-muted mb-2 mb-md-3">This subscription is tagged as <strong>demo-seeded</strong> (notes marker). Resync overwrites base, GST, total, paid amount, care years, and end date from the current plan catalogue for its payment frequency — real customer rows are never affected by this action.</p>
                        <form action="{{ route('admin.subscriptions.reconcile-demo-catalogue', $subscription) }}" method="POST" onsubmit="return confirm('Overwrite this demo subscription from the plan catalogue?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-sync-alt me-1"></i>Resync from plan catalogue (demo only)
                            </button>
                        </form>
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
                        <a href="{{ route('subscriptions.payment-screenshot', $subscription->id) }}" 
                           target="_blank" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>View Screenshot
                        </a>
                        <img src="{{ route('subscriptions.payment-screenshot', $subscription->id) }}" 
                             alt="Payment Screenshot" 
                             class="img-fluid mt-3 rounded"
                             style="max-height: 400px;"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div style="display:none;" class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Screenshot not found. Please check file path: {{ $subscription->payment_screenshot }}
                        </div>
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
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Workflow:</strong> Click "Verify Payment" to approve payment and automatically activate the subscription.
                        </div>
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

            <!-- Subscription Status Card -->
            @if($subscription->status !== 'pending')
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Subscription Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Subscription status is <strong>{{ $subscription->status_display }}</strong>. 
                        @if($subscription->approved_by)
                        Approved by {{ $subscription->approvedBy->name ?? 'Admin' }} 
                        on {{ $subscription->approved_at->format('M d, Y h:i A') }}
                        @endif
                    </div>
                </div>
            </div>
            @endif
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

.pmt-meta-box {
    margin-bottom: 1rem;
    padding: 0.9rem 1rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}

.pmt-code {
    display: inline-block;
    width: 100%;
    padding: 0.35rem 0.45rem;
    border-radius: 6px;
    background: #eef2ff;
    color: #1e293b;
    white-space: normal;
    word-break: break-all;
}
</style>

<script>
function showRejectModal() {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
@endsection


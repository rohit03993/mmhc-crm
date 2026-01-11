@extends('auth::layout')

@section('content')
<!-- Mobile Header -->
<div class="app-mobile-header d-md-none">
    <div class="d-flex align-items-center">
        <a href="{{ route('subscriptions.index') }}" class="btn btn-link text-white p-0 me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h5 class="text-white mb-0">Subscription Details</h5>
    </div>
</div>

<div class="container-fluid px-3 py-4 subscription-page-container">
    <div class="row">
        <div class="col-12 col-lg-8 mx-auto">
            <!-- Subscription Details Card -->
            <div class="subscription-detail-card">
                <div class="subscription-detail-header">
                    <div>
                        <h3 class="subscription-plan-name">{{ $subscription->plan->name }}</h3>
                        <p class="subscription-members text-muted mb-2">
                            <i class="fas fa-users me-1"></i> {{ $subscription->plan->members_included }}
                        </p>
                        <span class="subscription-badge badge-{{ $subscription->status_color }}">
                            {{ $subscription->status_display }}
                        </span>
                    </div>
                </div>

                <div class="subscription-detail-body">
                    <!-- Subscription Info -->
                    <div class="detail-section">
                        <h5 class="section-title">
                            <i class="fas fa-info-circle text-primary me-2"></i>Subscription Information
                        </h5>
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div class="info-item">
                                    <small class="text-muted d-block">Payment Frequency</small>
                                    <strong>{{ ucfirst(str_replace('_', ' ', $subscription->payment_frequency)) }}</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="info-item">
                                    <small class="text-muted d-block">Total Amount</small>
                                    <strong>₹{{ number_format($subscription->total_amount, 0) }}</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="info-item">
                                    <small class="text-muted d-block">Paid Amount</small>
                                    <strong class="text-success">₹{{ number_format($subscription->paid_amount, 0) }}</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="info-item">
                                    <small class="text-muted d-block">Payment Status</small>
                                    <strong class="text-{{ $subscription->payment_status === 'paid' ? 'success' : 'warning' }}">
                                        {{ ucfirst(str_replace('_', ' ', $subscription->payment_status)) }}
                                    </strong>
                                </div>
                            </div>
                        </div>

                        @if($subscription->payable_years > 0 || $subscription->care_benefits_years > 0)
                        <div class="benefits-info mt-3">
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
                        
                        @if($subscription->referrer)
                        <div class="referrer-info mt-3">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-user-md me-2"></i>
                                <strong>Referred by:</strong> {{ $subscription->referrer->name }} 
                                <span class="badge bg-success ms-2">{{ $subscription->referrer->isNurse() ? 'Nurse' : 'Caregiver' }}</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Dates -->
                    <div class="detail-section">
                        <h5 class="section-title">
                            <i class="fas fa-calendar text-primary me-2"></i>Subscription Period
                        </h5>
                        <div class="row">
                            <div class="col-6">
                                <div class="info-item">
                                    <small class="text-muted d-block">Start Date</small>
                                    <strong>{{ $subscription->start_date->format('M d, Y') }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-item">
                                    <small class="text-muted d-block">End Date</small>
                                    <strong>{{ $subscription->end_date->format('M d, Y') }}</strong>
                                </div>
                            </div>
                        </div>
                        @if($subscription->status === 'active' && $subscription->end_date > now())
                        <div class="mt-3">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-clock me-2"></i>
                                <strong>{{ $subscription->days_remaining }} days remaining</strong> in your subscription
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Payment Section - Show when payment is pending or failed -->
                    @if($subscription->payment_status !== 'paid' && in_array($subscription->payment_status, ['pending', 'failed']) && $subscription->status === 'pending')
                    <div class="detail-section payment-section">
                        <h5 class="section-title">
                            <i class="fas fa-credit-card text-primary me-2"></i>Complete Payment
                        </h5>
                        
                        @if($subscription->payment_status === 'failed')
                        <div class="alert alert-danger mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Payment Rejected:</strong> Your previous payment was rejected. Please make payment again.
                        </div>
                        @endif
                        
                        <div class="payment-instructions">
                            <!-- Payment Breakdown -->
                            <div class="payment-breakdown mb-3">
                                <h6 class="mb-3"><i class="fas fa-calculator text-primary me-2"></i>Payment Breakdown</h6>
                                <div class="breakdown-item">
                                    <div class="d-flex justify-content-between">
                                        <span>Base Amount:</span>
                                        <strong>₹{{ number_format($subscription->base_amount ?? $subscription->total_amount, 2) }}</strong>
                                    </div>
                                </div>
                                <div class="breakdown-item">
                                    <div class="d-flex justify-content-between">
                                        <span>GST ({{ number_format($subscription->gst_rate ?? 18, 2) }}%):</span>
                                        <strong>₹{{ number_format($subscription->gst_amount ?? 0, 2) }}</strong>
                                    </div>
                                </div>
                                <div class="breakdown-item total-amount">
                                    <div class="d-flex justify-content-between">
                                        <span><strong>Total Amount:</strong></span>
                                        <strong class="text-primary" style="font-size: 1.2em;">₹{{ number_format($subscription->total_amount, 2) }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Payment Instructions</h6>
                                <ol class="mb-0 ps-3">
                                    <li>Click on "Pay ₹{{ number_format($subscription->total_amount, 2) }}" button below to open your payment app</li>
                                    <li>Make payment of <strong>₹{{ number_format($subscription->total_amount, 2) }}</strong> (amount will be pre-filled)</li>
                                    <li>After payment, you will be redirected to upload payment screenshot</li>
                                    <li>Admin will verify payment and activate your subscription</li>
                                </ol>
                            </div>

                            <!-- UPI Payment Button -->
                            <div class="payment-methods">
                                <div class="payment-method-card text-center">
                                    <h6 class="mb-3"><i class="fas fa-mobile-alt me-2"></i>UPI Payment</h6>
                                    <div class="upi-id-box">
                                        <input type="hidden" id="upiId" value="{{ config('subscription.upi_id', 'mmhc@paytm') }}">
                                        <button type="button" 
                                                class="btn btn-primary btn-lg w-100" 
                                                onclick="openUPI()">
                                            <i class="fas fa-external-link-alt me-2"></i>Pay ₹{{ number_format($subscription->total_amount, 2) }}
                                        </button>
                                        <small class="text-muted d-block mt-2">
                                            Opens PhonePe, Paytm, or Google Pay automatically
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Payment Submitted Message -->
                    @if($subscription->payment_status === 'partially_paid' && $subscription->status === 'pending')
                    <div class="detail-section payment-section">
                        <h5 class="section-title">
                            <i class="fas fa-clock text-warning me-2"></i>Payment Submitted
                        </h5>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Payment screenshot submitted!</strong> Your payment is under review. Admin will verify and activate your subscription within 24 hours.
                            <div class="mt-3">
                                <a href="{{ route('subscriptions.payment-confirmation', $subscription) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Payment Confirmation
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Payment History -->
                    @if($subscription->payment_screenshot || $subscription->transaction_id)
                    <div class="detail-section">
                        <h5 class="section-title">
                            <i class="fas fa-history text-primary me-2"></i>Payment Details
                        </h5>
                        @if($subscription->payment_screenshot)
                        <div class="payment-proof-item">
                            <strong><i class="fas fa-image me-2"></i>Payment Screenshot:</strong>
                            <a href="{{ route('subscriptions.payment-screenshot', ['id' => $subscription->id]) }}" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-primary ms-2">
                                <i class="fas fa-eye me-1"></i>View Screenshot
                            </a>
                        </div>
                        @endif
                        @if($subscription->transaction_id)
                        <div class="payment-proof-item mt-2">
                            <strong><i class="fas fa-receipt me-2"></i>Transaction ID:</strong>
                            <code class="ms-2">{{ $subscription->transaction_id }}</code>
                        </div>
                        @endif
                        @if($subscription->payment_notes)
                        <div class="payment-proof-item mt-2">
                            <strong><i class="fas fa-sticky-note me-2"></i>Notes:</strong>
                            <p class="mb-0 ms-2">{{ $subscription->payment_notes }}</p>
                        </div>
                        @endif
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-clock me-2"></i>
                            Payment verification pending. Admin will review and activate your subscription.
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('auth::components.bottom-nav')

<style>
/* Mobile padding for bottom nav - prevents content from being blocked */
@media (max-width: 767px) {
    .subscription-page-container {
        padding-bottom: 20px !important;
        margin-bottom: 80px !important;
    }
}

.subscription-detail-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 24px;
    margin-bottom: 20px;
}

.subscription-detail-header {
    padding-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 24px;
}

.subscription-plan-name {
    font-size: 24px;
    font-weight: 700;
    color: #212529;
    margin-bottom: 8px;
}

.subscription-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.detail-section {
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 1px solid #e9ecef;
}

.detail-section:last-child {
    border-bottom: none;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #212529;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
}

.info-item {
    text-align: center;
}

.info-item strong {
    display: block;
    font-size: 16px;
    color: #212529;
    margin-top: 4px;
}

.payment-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 24px;
    border: 2px solid #e9ecef;
}

.payment-method-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e9ecef;
}

.upi-id-box input {
    font-family: monospace;
    font-size: 16px;
    font-weight: 600;
    text-align: center;
}

.payment-proof-item {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

.payment-breakdown {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 16px;
}

.breakdown-item {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.breakdown-item:last-child {
    border-bottom: none;
}

.breakdown-item.total-amount {
    margin-top: 8px;
    padding-top: 12px;
    border-top: 2px solid #007bff;
}

@media (max-width: 768px) {
    .subscription-detail-card {
        padding: 16px;
    }
    
    .info-item {
        text-align: left;
        margin-bottom: 12px;
    }
}
</style>

<script>
// UPI Deep Linking - Opens payment apps automatically and redirects to confirmation page
function openUPI() {
    const upiId = document.getElementById('upiId').value;
    const amount = {{ $subscription->total_amount }};
    const merchantName = '{{ config("subscription.upi_merchant_name", "MMHC") }}';
    const confirmationUrl = '{{ route("subscriptions.payment-confirmation", $subscription) }}?from_upi=1';
    
    // UPI deep link format: upi://pay?pa=UPI_ID&pn=MERCHANT&am=AMOUNT&cu=INR
    const upiLink = `upi://pay?pa=${encodeURIComponent(upiId)}&pn=${encodeURIComponent(merchantName)}&am=${amount.toFixed(2)}&cu=INR&tn=MMHC Subscription Payment`;
    
    // Store flag to show popup when user returns
    sessionStorage.setItem('showPaymentPopup', 'true');
    
    // Try to open UPI app
    window.location.href = upiLink;
    
    // Fallback: Redirect to confirmation page after delay (if UPI app doesn't open)
    setTimeout(() => {
        if (document.hasFocus()) {
            window.location.href = confirmationUrl;
        }
    }, 1500);
}

// Removed copyUPIId function - UPI ID is now hidden for security
    upiId.select();
    upiId.setSelectionRange(0, 99999);
    
    // Use modern clipboard API if available
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(upiId.value).then(() => {
            showCopyFeedback();
        });
    } else {
        // Fallback for older browsers
        document.execCommand('copy');
        showCopyFeedback();
    }
}

function showCopyFeedback() {
    const btn = event?.target?.closest('button') || document.querySelector('button[onclick*="copyUPIId"]');
    if (btn) {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-primary');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
        }, 2000);
    }
}
</script>
@endsection


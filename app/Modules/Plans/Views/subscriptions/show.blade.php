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

<div class="container-fluid px-3 py-4">
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

                    <!-- Payment Section -->
                    @if($subscription->payment_status !== 'paid' && $subscription->status === 'pending')
                    <div class="detail-section payment-section">
                        <h5 class="section-title">
                            <i class="fas fa-credit-card text-primary me-2"></i>Complete Payment
                        </h5>
                        
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
                                    <li>Click on UPI ID below to open your payment app OR scan the QR code</li>
                                    <li>Make payment of <strong>₹{{ number_format($subscription->total_amount, 2) }}</strong></li>
                                    <li>Upload payment screenshot OR enter transaction ID</li>
                                    <li>Admin will verify payment and activate your subscription</li>
                                </ol>
                            </div>

                            <!-- QR Code and UPI ID -->
                            <div class="payment-methods">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <div class="payment-method-card">
                                            <h6 class="mb-3"><i class="fas fa-qrcode me-2"></i>Scan QR Code</h6>
                                            <div class="qr-code-placeholder">
                                                @if(config('subscription.qr_code'))
                                                    <img src="{{ asset('storage/' . config('subscription.qr_code')) }}" 
                                                         alt="Payment QR Code" 
                                                         class="img-fluid"
                                                         style="max-width: 100%; height: auto; border-radius: 8px;">
                                                @else
                                                    <i class="fas fa-qrcode fa-5x text-muted"></i>
                                                    <p class="text-muted small mt-2">QR Code not configured. Please contact admin.</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="payment-method-card">
                                            <h6 class="mb-3"><i class="fas fa-mobile-alt me-2"></i>UPI Payment</h6>
                                            <div class="upi-id-box">
                                                <input type="text" 
                                                       id="upiId" 
                                                       value="{{ config('subscription.upi_id', 'mmhc@paytm') }}" 
                                                       readonly 
                                                       class="form-control">
                                                <div class="d-flex gap-2 mt-2">
                                                    <button type="button" class="btn btn-primary flex-fill" onclick="openUPI()">
                                                        <i class="fas fa-external-link-alt me-2"></i>Pay with UPI
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary" onclick="copyUPIId()">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </div>
                                                <small class="text-muted d-block mt-2 text-center">
                                                    Opens PhonePe, Paytm, or Google Pay
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Submission Form -->
                            <div class="payment-submission mt-4">
                                <form action="{{ route('subscriptions.submit-payment', $subscription) }}" 
                                      method="POST" 
                                      enctype="multipart/form-data"
                                      id="paymentForm">
                                    @csrf
                                    
                                    <div class="payment-options-tabs">
                                        <button type="button" 
                                                class="tab-btn active" 
                                                data-tab="screenshot"
                                                onclick="switchTab('screenshot')">
                                            <i class="fas fa-image me-2"></i>Upload Screenshot
                                        </button>
                                        <button type="button" 
                                                class="tab-btn" 
                                                data-tab="transaction"
                                                onclick="switchTab('transaction')">
                                            <i class="fas fa-receipt me-2"></i>Enter Transaction ID
                                        </button>
                                    </div>

                                    <!-- Screenshot Upload Tab -->
                                    <div id="screenshotTab" class="tab-content active">
                                        <div class="form-group mt-3">
                                            <label for="payment_screenshot" class="form-label">
                                                <i class="fas fa-upload me-2"></i>Upload Payment Screenshot
                                            </label>
                                            <input type="file" 
                                                   name="payment_screenshot" 
                                                   id="payment_screenshot" 
                                                   class="form-control"
                                                   accept="image/*"
                                                   onchange="previewImage(this)">
                                            <small class="text-muted">Upload screenshot of your payment (JPG, PNG, Max 5MB)</small>
                                            <div id="imagePreview" class="mt-2"></div>
                                        </div>
                                    </div>

                                    <!-- Transaction ID Tab -->
                                    <div id="transactionTab" class="tab-content">
                                        <div class="form-group mt-3">
                                            <label for="transaction_id" class="form-label">
                                                <i class="fas fa-receipt me-2"></i>Transaction ID / UPI Reference Number
                                            </label>
                                            <input type="text" 
                                                   name="transaction_id" 
                                                   id="transaction_id" 
                                                   class="form-control"
                                                   placeholder="Enter transaction ID or UPI reference number">
                                            <small class="text-muted">Enter the transaction ID from your payment app</small>
                                        </div>
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="payment_notes" class="form-label">
                                            <i class="fas fa-sticky-note me-2"></i>Additional Notes (Optional)
                                        </label>
                                        <textarea name="payment_notes" 
                                                  id="payment_notes" 
                                                  class="form-control" 
                                                  rows="3"
                                                  placeholder="Any additional information about your payment..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 mt-4">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Payment Proof
                                    </button>
                                </form>
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
                            <a href="{{ asset('storage/' . $subscription->payment_screenshot) }}" 
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

.qr-code-placeholder {
    text-align: center;
    padding: 40px 20px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 2px dashed #dee2e6;
}

.upi-id-box input {
    font-family: monospace;
    font-size: 16px;
    font-weight: 600;
    text-align: center;
}

.payment-options-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 0;
}

.tab-btn {
    flex: 1;
    padding: 12px;
    border: 2px solid #e9ecef;
    background: white;
    border-radius: 8px 8px 0 0;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.tab-btn.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.tab-content {
    display: none;
    background: white;
    padding: 20px;
    border: 2px solid #e9ecef;
    border-top: none;
    border-radius: 0 0 8px 8px;
}

.tab-content.active {
    display: block;
}

#imagePreview img {
    max-width: 100%;
    max-height: 300px;
    border-radius: 8px;
    border: 2px solid #e9ecef;
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
// UPI Deep Linking - Opens payment apps automatically
function openUPI() {
    const upiId = document.getElementById('upiId').value;
    const amount = {{ $subscription->total_amount }};
    const merchantName = '{{ config("subscription.upi_merchant_name", "MMHC") }}';
    
    // UPI deep link format: upi://pay?pa=UPI_ID&pn=MERCHANT&am=AMOUNT&cu=INR
    const upiLink = `upi://pay?pa=${encodeURIComponent(upiId)}&pn=${encodeURIComponent(merchantName)}&am=${amount.toFixed(2)}&cu=INR`;
    
    // Try to open UPI app
    window.location.href = upiLink;
    
    // Fallback: If UPI app doesn't open, show copy option
    setTimeout(() => {
        // If still on page after 1 second, UPI app might not be installed
        // Show message to copy UPI ID manually
        if (document.hasFocus()) {
            copyUPIId();
            alert('UPI app not found. UPI ID copied to clipboard. Please paste it in your payment app.');
        }
    }, 1000);
}

function copyUPIId() {
    const upiId = document.getElementById('upiId');
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
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-primary', 'btn-primary');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            if (btn.classList.contains('btn-outline-primary')) {
                btn.classList.add('btn-outline-primary');
            } else {
                btn.classList.add('btn-primary');
            }
        }, 2000);
    }
}

function switchTab(tab) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tab + 'Tab').classList.add('active');
    document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
    
    // Clear form inputs when switching
    if (tab === 'screenshot') {
        document.getElementById('transaction_id').value = '';
    } else {
        document.getElementById('payment_screenshot').value = '';
        document.getElementById('imagePreview').innerHTML = '';
    }
}

function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            preview.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Form validation
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const screenshotTab = document.getElementById('screenshotTab').classList.contains('active');
    const transactionTab = document.getElementById('transactionTab').classList.contains('active');
    
    if (screenshotTab && !document.getElementById('payment_screenshot').files.length) {
        e.preventDefault();
        alert('Please upload a payment screenshot');
        return false;
    }
    
    if (transactionTab && !document.getElementById('transaction_id').value.trim()) {
        e.preventDefault();
        alert('Please enter transaction ID');
        return false;
    }
});
</script>
@endsection


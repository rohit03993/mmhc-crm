@extends('auth::layout')

@section('content')
<!-- Mobile Header -->
<div class="app-mobile-header d-md-none">
    <div class="d-flex align-items-center">
        <a href="{{ route('subscriptions.show', $subscription) }}" class="btn btn-link text-white p-0 me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h5 class="text-white mb-0">Payment Confirmation</h5>
    </div>
</div>

<div class="container-fluid px-3 py-4">
    <div class="row">
        <div class="col-12 col-lg-8 mx-auto">
            <!-- Payment Confirmation Card -->
            <div class="payment-confirmation-card">
                <div class="payment-confirmation-header text-center mb-4">
                    <div class="payment-icon mb-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
                    </div>
                    <h3 class="mb-2">Payment Confirmation</h3>
                    <p class="text-muted">Please upload your payment screenshot to complete the process</p>
                </div>

                <!-- Subscription Details -->
                <div class="detail-section mb-4">
                    <h5 class="section-title">
                        <i class="fas fa-info-circle text-primary me-2"></i>Subscription Details
                    </h5>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="info-item">
                                <small class="text-muted d-block">Plan Name</small>
                                <strong>{{ $subscription->plan->name }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-item">
                                <small class="text-muted d-block">Total Amount</small>
                                <strong class="text-primary">₹{{ number_format($subscription->total_amount, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Breakdown -->
                    <div class="payment-breakdown mt-3">
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
                </div>

                <!-- UPI Payment Button (if not paid yet) -->
                @if($subscription->payment_status !== 'paid')
                <div class="payment-methods mb-4">
                    <div class="payment-method-card text-center">
                        <h6 class="mb-3"><i class="fas fa-mobile-alt me-2"></i>Make Payment</h6>
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
                @endif

                <!-- Success Message (if payment proof already submitted) -->
                @if($subscription->payment_screenshot || $subscription->transaction_id)
                <div class="payment-success-message">
                    <div class="success-icon mb-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
                    </div>
                    <h4 class="text-success mb-3">Payment Screenshot Submitted Successfully!</h4>
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Thank you!</strong> You have submitted the screenshot successfully. Our team will contact you within 24 hours and activate the subscription if payment is done.
                    </div>
                    <a href="{{ route('subscriptions.show', $subscription) }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Subscription
                    </a>
                </div>
                @else
                <!-- Payment Submission Form -->
                <div class="payment-submission">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-upload text-primary me-2"></i>Upload Payment Screenshot
                    </h5>
                    
                    <form action="{{ route('subscriptions.submit-payment', $subscription) }}" 
                          method="POST" 
                          enctype="multipart/form-data"
                          id="paymentForm">
                        @csrf
                        
                        <div class="form-group">
                            <label for="payment_screenshot" class="form-label">
                                <i class="fas fa-upload me-2"></i>Upload Payment Screenshot
                            </label>
                            <input type="file" 
                                   name="payment_screenshot" 
                                   id="payment_screenshot" 
                                   class="form-control"
                                   accept="image/*"
                                   onchange="previewImage(this)"
                                   required>
                            <small class="text-muted">Upload screenshot of your payment (JPG, PNG, Max 5MB)</small>
                            <div id="imagePreview" class="mt-2"></div>
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
                            <i class="fas fa-paper-plane me-2"></i>Submit Payment Screenshot
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('auth::components.bottom-nav')

<!-- Upload Screenshot Popup Modal -->
<div id="uploadScreenshotPopup" class="upload-popup-modal" style="display: none;">
    <div class="upload-popup-content">
        <div class="upload-popup-header">
            <h5 class="mb-0">
                <i class="fas fa-upload me-2"></i>Upload Payment Screenshot
            </h5>
            <button type="button" class="btn-close" onclick="closeUploadPopup()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="upload-popup-body">
            <p class="mb-3">Please upload the screenshot of your payment to complete the process.</p>
            <button type="button" class="btn btn-primary w-100" onclick="closeUploadPopup(); setTimeout(function() { document.getElementById('payment_screenshot')?.click(); }, 300);">
                <i class="fas fa-upload me-2"></i>Upload Screenshot Now
            </button>
        </div>
    </div>
</div>

<style>
.upload-popup-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.3s ease-out;
}

.upload-popup-content {
    background: white;
    border-radius: 16px;
    padding: 24px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease-out;
}

.upload-popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #e9ecef;
}

.upload-popup-header h5 {
    font-size: 20px;
    font-weight: 600;
    color: #212529;
}

.btn-close {
    background: none;
    border: none;
    font-size: 20px;
    color: #6c757d;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
}

.btn-close:hover {
    background: #f8f9fa;
    color: #212529;
}

.upload-popup-body {
    text-align: center;
}

.upload-popup-body p {
    color: #495057;
    font-size: 15px;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@media (max-width: 768px) {
    .upload-popup-content {
        padding: 20px;
        margin: 20px;
    }
}
</style>

<style>
.payment-confirmation-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 24px;
    margin-bottom: 20px;
}

.payment-confirmation-header {
    padding-bottom: 24px;
    border-bottom: 2px solid #e9ecef;
}

.payment-icon {
    animation: scaleIn 0.5s ease-out;
}

@keyframes scaleIn {
    from {
        transform: scale(0);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.detail-section {
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid #e9ecef;
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

.payment-breakdown {
    background: #f8f9fa;
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

.payment-success-message {
    text-align: center;
    padding: 40px 20px;
}

.success-icon {
    animation: scaleIn 0.5s ease-out;
}

#imagePreview img {
    max-width: 100%;
    max-height: 300px;
    border-radius: 8px;
    border: 2px solid #e9ecef;
}

@media (max-width: 768px) {
    .payment-confirmation-card {
        padding: 16px;
    }
    
    .info-item {
        text-align: left;
        margin-bottom: 12px;
    }
}
</style>

<script>
// Check if user is returning from UPI app and show popup
document.addEventListener('DOMContentLoaded', function() {
    // Check if we should show the upload popup (user returned from UPI app)
    const urlParams = new URLSearchParams(window.location.search);
    const fromUPI = urlParams.get('from_upi') === '1';
    const showPopup = sessionStorage.getItem('showPaymentPopup') === 'true';
    
    if (fromUPI || showPopup) {
        // Clear the flag
        sessionStorage.removeItem('showPaymentPopup');
        
        // Show popup modal
        showUploadPopup();
    }
});

// Show popup to upload screenshot
function showUploadPopup() {
    const popup = document.getElementById('uploadScreenshotPopup');
    if (popup) {
        popup.style.display = 'flex';
        // Scroll to top to show the popup
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Close popup
function closeUploadPopup() {
    const popup = document.getElementById('uploadScreenshotPopup');
    if (popup) {
        popup.style.display = 'none';
    }
}

// Close popup when clicking outside
document.addEventListener('click', function(e) {
    const popup = document.getElementById('uploadScreenshotPopup');
    if (popup && e.target === popup) {
        closeUploadPopup();
    }
});

// UPI Deep Linking - Opens payment apps automatically
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

// Removed copyUPIId and showCopyFeedback functions - UPI ID is now hidden for security
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
const paymentForm = document.getElementById('paymentForm');
if (paymentForm) {
    paymentForm.addEventListener('submit', function(e) {
        if (!document.getElementById('payment_screenshot').files.length) {
            e.preventDefault();
            alert('Please upload a payment screenshot');
            return false;
        }
    });
}
</script>
@endsection


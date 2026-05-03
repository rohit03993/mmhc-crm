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
                    @php
                        $razorpayEnabled = (bool) config('payments.razorpay.enabled');
                        $manualPaymentEnabled = (bool) config('payments.subscription.manual_enabled', true);
                    @endphp

                    @if($razorpayEnabled)
                    <div class="payment-method-card text-center mb-3">
                        <h6 class="mb-3"><i class="fas fa-credit-card me-2"></i>Pay Online (Razorpay)</h6>
                        <button
                            type="button"
                            class="btn btn-success btn-lg w-100 mb-2"
                            onclick="startRazorpayCheckout()"
                        >
                            <i class="fas fa-bolt me-2"></i>Pay Securely via Razorpay
                        </button>
                        <small class="text-muted d-block">Cards, UPI, Wallets and Netbanking supported</small>
                    </div>
                    @endif

                    @if($manualPaymentEnabled)
                    <div class="payment-method-card text-center">
                        <h6 class="mb-3"><i class="fas fa-mobile-alt me-2"></i>Make Payment</h6>
                        <div class="upi-id-box">
                            @php
                                $upiId = config('subscription.upi_id', 'mmhc@paytm');
                                $amount = $subscription->total_amount;
                                $merchantName = config('subscription.upi_merchant_name', 'MMHC');
                                // Generate UPI payment link (works on mobile, tries on desktop too)
                                $upiLink = "upi://pay?pa=" . urlencode($upiId) . "&pn=" . urlencode($merchantName) . "&am=" . number_format($amount, 2, '.', '') . "&cu=INR&tn=MMHC Subscription Payment";
                                // Generate QR code data
                                $qrData = "upi://pay?pa=" . urlencode($upiId) . "&pn=" . urlencode($merchantName) . "&am=" . number_format($amount, 2, '.', '') . "&cu=INR&tn=MMHC%20Subscription%20Payment";
                            @endphp
                            <input type="hidden" id="upiId" value="{{ $upiId }}">
                            <input type="hidden" id="upiLink" value="{{ $upiLink }}">
                            <input type="hidden" id="qrData" value="{{ $qrData }}">
                            <input type="hidden" id="amount" value="{{ $amount }}">
                            
                            <!-- Same button for both mobile and desktop -->
                            <button type="button" 
                                    class="btn btn-primary btn-lg w-100 mb-3" 
                                    onclick="openUPI()">
                                <i class="fas fa-external-link-alt me-2"></i>Pay ₹{{ number_format($subscription->total_amount, 2) }}
                            </button>
                            <small class="text-muted d-block">
                                Opens PhonePe, Paytm, or Google Pay automatically
                            </small>
                        </div>
                    </div>
                    @endif
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
                @elseif($manualPaymentEnabled)
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
                @else
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>Manual screenshot payment is disabled. Please use Razorpay online payment.
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

/* Desktop Payment Modal Styles */
.desktop-payment-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.3s ease-out;
}

.desktop-payment-content {
    background: white;
    border-radius: 16px;
    padding: 0;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease-out;
}

.desktop-payment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 2px solid #e9ecef;
}

.desktop-payment-header h5 {
    font-size: 20px;
    font-weight: 600;
    color: #212529;
    margin: 0;
}

.btn-close-modal {
    background: none;
    border: none;
    font-size: 24px;
    color: #6c757d;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
}

.btn-close-modal:hover {
    background: #f8f9fa;
    color: #212529;
}

.desktop-payment-body {
    padding: 24px;
}

.upi-id-display-modal {
    background: white;
    padding: 12px 20px;
    border-radius: 8px;
    border: 2px solid #007bff;
    font-family: monospace;
    color: #007bff;
}

#qrCodeContainerModal {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px solid #e9ecef;
}

#qrCodeContainerModal canvas {
    border-radius: 8px;
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
        transform: translateY(30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
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
const razorpayEnabled = @json((bool) config('payments.razorpay.enabled'));

async function startRazorpayCheckout() {
    if (!razorpayEnabled) {
        alert('Razorpay is not enabled in this environment.');
        return;
    }

    try {
        const orderResp = await fetch('{{ route('subscriptions.razorpay.order', $subscription) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        });
        const orderData = await orderResp.json();

        if (!orderResp.ok || !orderData.success) {
            throw new Error(orderData.message || 'Failed to create Razorpay order.');
        }

        const options = {
            key: orderData.key,
            amount: orderData.amount,
            currency: orderData.currency,
            order_id: orderData.order_id,
            name: 'MMHC',
            description: 'Subscription Payment',
            prefill: {
                name: orderData.customer?.name || '',
                email: orderData.customer?.email || '',
                contact: orderData.customer?.contact || ''
            },
            handler: async function (response) {
                await verifyRazorpayPayment(response);
            }
        };

        const rzp = new Razorpay(options);
        rzp.on('payment.failed', function () {
            alert('Payment failed or cancelled. You can retry or use screenshot upload.');
        });
        rzp.open();
    } catch (error) {
        console.error(error);
        alert(error.message || 'Unable to start online payment right now.');
    }
}

async function verifyRazorpayPayment(response) {
    const verifyResp = await fetch('{{ route('subscriptions.razorpay.verify', $subscription) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(response)
    });
    const verifyData = await verifyResp.json();

    if (!verifyResp.ok || !verifyData.success) {
        throw new Error(verifyData.message || 'Payment verification failed.');
    }

    window.location.href = verifyData.redirect_url;
}

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

// UPI Deep Linking - Opens payment apps automatically (works on mobile, shows modal on desktop)
function openUPI() {
    const upiLink = document.getElementById('upiLink').value;
    const upiId = document.getElementById('upiId').value;
    const amount = parseFloat(document.getElementById('amount').value);
    const confirmationUrl = '{{ route("subscriptions.payment-confirmation", $subscription) }}?from_upi=1';
    
    // Detect if we're on mobile or desktop
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    
    if (isMobile) {
        // Mobile: Store flag to show popup when user returns
        sessionStorage.setItem('showPaymentPopup', 'true');
        
        // Try to open UPI app (works on mobile)
        window.location.href = upiLink;
        
        // Fallback: Redirect to confirmation page after delay (if UPI app doesn't open)
        setTimeout(() => {
            if (document.hasFocus()) {
                window.location.href = confirmationUrl;
            }
        }, 1500);
    } else {
        // Desktop: Try UPI deep link first, but it will likely fail
        // So immediately show desktop payment modal with UPI ID and QR code
        const hadFocus = document.hasFocus();
        
        // Try to open UPI link (will fail on desktop)
        window.location.href = upiLink;
        
        // Check if UPI app opened (on desktop, it won't, so page stays focused)
        setTimeout(() => {
            // If page is still focused after 500ms, UPI app didn't open (desktop scenario)
            if (document.hasFocus() || hadFocus) {
                // Show desktop payment modal
                showDesktopPaymentModal(upiId, amount);
            }
        }, 500);
    }
}

// Copy UPI ID and Amount to clipboard (Desktop)
function copyUPIId() {
    const upiId = document.getElementById('upiId').value;
    const amount = {{ $subscription->total_amount }};
    const textToCopy = `${upiId}\nAmount: ₹${amount.toFixed(2)}\n\nCopy this UPI ID and make payment from your phone (PhonePe, Paytm, Google Pay, etc.)`;
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(textToCopy).then(() => {
            showCopyFeedback();
        }).catch(() => {
            // Fallback for older browsers
            fallbackCopyTextToClipboard(textToCopy);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyTextToClipboard(textToCopy);
    }
}

// Fallback copy method for older browsers
function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showCopyFeedback();
    } catch (err) {
        alert('Failed to copy. Please copy manually: ' + text);
    }
    
    document.body.removeChild(textArea);
}

// Show copy feedback
function showCopyFeedback() {
    // Find all copy buttons and show feedback
    const copyButtons = document.querySelectorAll('[onclick="copyUPIId()"]');
    copyButtons.forEach(btn => {
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-primary', 'btn-primary');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            if (btn.classList.contains('btn-sm')) {
                btn.classList.add('btn-outline-primary');
            } else {
                btn.classList.add('btn-primary');
            }
        }, 2000);
    });
}

// Show desktop payment modal with UPI ID and QR code
function showDesktopPaymentModal(upiId, amount) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('desktopPaymentModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'desktopPaymentModal';
        modal.className = 'desktop-payment-modal';
        modal.innerHTML = `
            <div class="desktop-payment-content">
                <div class="desktop-payment-header">
                    <h5 class="mb-0">
                        <i class="fas fa-qrcode me-2"></i>Make Payment via UPI
                    </h5>
                    <button type="button" class="btn-close-modal" onclick="closeDesktopPaymentModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="desktop-payment-body">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <label class="text-muted small d-block mb-2">UPI ID</label>
                            <div class="d-flex align-items-center justify-content-center">
                                <code class="upi-id-display-modal fs-5 fw-bold me-2" id="modalUpiId">${upiId}</code>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary" 
                                        onclick="copyUPIId()"
                                        title="Copy UPI ID">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small d-block mb-2">Amount</label>
                            <div class="fs-4 fw-bold text-primary" id="modalAmount">₹${amount.toFixed(2)}</div>
                        </div>
                        <div id="qrCodeContainerModal" class="mb-3"></div>
                        <button type="button" 
                                class="btn btn-primary btn-lg w-100" 
                                onclick="copyUPIId()">
                            <i class="fas fa-copy me-2"></i>Copy UPI ID & Amount
                        </button>
                        <small class="text-muted d-block mt-2">
                            Scan QR code or copy UPI ID and make payment from your phone
                        </small>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    // Update modal content
    const upiIdEl = document.getElementById('modalUpiId');
    const amountEl = document.getElementById('modalAmount');
    if (upiIdEl) upiIdEl.textContent = upiId;
    if (amountEl) amountEl.textContent = `₹${amount.toFixed(2)}`;
    
    // Show modal
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Generate QR code after modal is shown
    setTimeout(() => {
        generateQRCodeModal();
    }, 100);
}

// Close desktop payment modal
function closeDesktopPaymentModal() {
    const modal = document.getElementById('desktopPaymentModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('desktopPaymentModal');
    if (modal && e.target === modal) {
        closeDesktopPaymentModal();
    }
});

// Generate QR Code for Desktop Modal (using QRious library)
function generateQRCodeModal() {
    const qrData = document.getElementById('qrData').value;
    const qrContainer = document.getElementById('qrCodeContainerModal');
    
    if (qrContainer && qrData) {
        // Clear previous QR code
        qrContainer.innerHTML = '';
        
        // Create QR code using QRious library
        const canvas = document.createElement('canvas');
        canvas.id = 'qrcodeModal';
        canvas.width = 200;
        canvas.height = 200;
        qrContainer.appendChild(canvas);
        
        // Load QRious library dynamically if not already loaded
        if (typeof QRious === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js';
            script.onload = function() {
                new QRious({
                    element: canvas,
                    value: qrData,
                    size: 200,
                    background: 'white',
                    foreground: 'black',
                    level: 'H'
                });
            };
            document.head.appendChild(script);
        } else {
            new QRious({
                element: canvas,
                value: qrData,
                size: 200,
                background: 'white',
                foreground: 'black',
                level: 'H'
            });
        }
    }
}

// Initialize on page load
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
@if(config('payments.razorpay.enabled'))
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endif
@endsection


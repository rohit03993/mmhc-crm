@extends('auth::layout')

@section('title', 'Process Payment - Admin')

@section('head')
<style>
    .payment-info-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .staff-payment-info {
        background: linear-gradient(135deg, #e8f1fb 0%, #dce9f7 100%);
        color: #1e3a5f;
        border: 1px solid #c5d7ef;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .staff-payment-info .text-muted { color: #4a6589 !important; }
    .payment-record-submit {
        background-color: #198754;
        border-color: #198754;
        font-weight: 600;
    }
    .payment-record-submit:hover {
        background-color: #157347;
        border-color: #146c43;
    }
    .qr-code-preview {
        max-width: 200px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .upi-id-display {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        font-size: 1.2rem;
        font-weight: 600;
        text-align: center;
        margin-top: 1rem;
    }
    .payment-details-list {
        list-style: none;
        padding: 0;
    }
    .payment-details-list li {
        padding: 0.75rem;
        border-bottom: 1px solid #e9ecef;
    }
    .payment-details-list li:last-child {
        border-bottom: none;
    }
    .alert-sm {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        margin-bottom: 0;
    }
</style>

<script>
// UPI Deep Linking for Staff Payments
function openUPIForStaff(upiId, amount, staffName) {
    const merchantName = 'MMHC';
    const note = `Payment to ${staffName}`;
    
    // UPI deep link format: upi://pay?pa=UPI_ID&pn=MERCHANT&am=AMOUNT&cu=INR&tn=NOTE
    const upiLink = `upi://pay?pa=${encodeURIComponent(upiId)}&pn=${encodeURIComponent(merchantName)}&am=${amount.toFixed(2)}&cu=INR&tn=${encodeURIComponent(note)}`;
    
    // Try to open UPI app
    window.location.href = upiLink;
    
    // Show notification
    setTimeout(() => {
        alert('If UPI app did not open, please copy the UPI ID and make payment manually.');
    }, 1000);
}

// Copy UPI ID to clipboard
function copyUPIId(upiId) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(upiId).then(() => {
            showCopyFeedback();
        });
    } else {
        // Fallback for older browsers
        const tempInput = document.createElement('input');
        tempInput.value = upiId;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        showCopyFeedback();
    }
}

function initAdminPaymentCategorySelect() {
    const sel = document.getElementById('adminPaymentCategorySelect');
    if (!sel) {
        return;
    }
    const baseUrl = sel.getAttribute('data-form-base-url');
    if (!baseUrl) {
        return;
    }
    sel.addEventListener('change', function () {
        if (!this.value) {
            return;
        }
        window.location.assign(baseUrl + '?type=' + encodeURIComponent(this.value));
    });
}

function showCopyFeedback() {
    // Create a temporary toast notification
    const toast = document.createElement('div');
    toast.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3';
    toast.style.zIndex = '9999';
    toast.innerHTML = '<i class="fas fa-check-circle me-2"></i>UPI ID copied to clipboard!';
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 2000);
}

document.addEventListener('DOMContentLoaded', initAdminPaymentCategorySelect);

function toggleAdminPaymentModeFields() {
    const mode = document.getElementById('paymentModeSelect')?.value || 'manual';
    const txn = document.getElementById('transaction_id');
    const screenshot = document.getElementById('payment_screenshot');
    const manualFields = document.querySelectorAll('.manual-only-field');
    const gatewayHint = document.getElementById('gatewayModeHint');

    const isManual = mode === 'manual';
    manualFields.forEach(el => el.style.display = isManual ? '' : 'none');
    if (txn) txn.required = isManual;
    if (screenshot) screenshot.required = isManual;
    if (gatewayHint) gatewayHint.style.display = isManual ? 'none' : '';
}

document.addEventListener('DOMContentLoaded', toggleAdminPaymentModeFields);
</script>
@endsection

@section('content')
@php
    $paymentTypeLabels = [
        'service_request' => 'Services',
        'patient_reward' => 'Patient rewards',
        'staff_referral' => 'Staff referrals',
        'subscription_referral' => 'Subscription referrals',
    ];
    $suggestedAmountStr = $selectedTypePending >= 0.01 ? number_format($selectedTypePending, 2, '.', '') : '';
    $upiPrefillAmount = max(0.01, (float) ($pendingPayments[$paymentType]['amount'] ?? 0));
@endphp
<div class="container-fluid px-3 px-md-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-money-bill-wave me-2"></i>Process Payment
        </h2>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Payments
        </a>
    </div>

    <!-- Staff Payment Info -->
    <div class="staff-payment-info">
        <h5 class="mb-3">{{ $staff->name }}</h5>
        <div class="row">
            <div class="col-md-6">
                <p class="mb-1"><i class="fas fa-user-tag me-2"></i>{{ ucfirst($staff->role) }}</p>
                <p class="mb-1"><i class="fas fa-phone me-2"></i>{{ $staff->phone }}</p>
                <p class="mb-0"><i class="fas fa-envelope me-2"></i>{{ $staff->email }}</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="h3 mb-0">₹{{ number_format($pendingPayments['total'], 2) }}</div>
                <small class="text-muted">Total pending (all categories)</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Payment Details -->
        <div class="col-md-5">
            <div class="payment-info-card">
                <h5 class="mb-3">
                    <i class="fas fa-info-circle me-2"></i>Payment Details
                </h5>
                
                @if($paymentType === 'service_request')
                    @if($pendingPayments['service_request']['amount'] > 0)
                        <div class="mb-3">
                            <strong>Service Requests:</strong> ₹{{ number_format($pendingPayments['service_request']['amount'], 2) }}
                            <br><small class="text-muted">({{ $pendingPayments['service_request']['count'] }} pending)</small>
                        </div>
                    @endif
                @endif

                @if($paymentType === 'patient_reward')
                    @if($pendingPayments['patient_reward']['amount'] > 0)
                        <div class="mb-3">
                            <strong>Patient Rewards:</strong> ₹{{ number_format($pendingPayments['patient_reward']['amount'], 2) }}
                            <br><small class="text-muted">({{ $pendingPayments['patient_reward']['count'] }} entries)</small>
                            @if(isset($pendingPayments['patient_reward']['meets_threshold']) && !$pendingPayments['patient_reward']['meets_threshold'])
                                <div class="alert alert-warning alert-sm mt-2 mb-0 py-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Threshold Not Reached:</strong> Amount is below ₹500 minimum threshold. You can still process this payment as admin.
                                </div>
                            @endif
                        </div>
                    @endif
                @endif

                @if($paymentType === 'staff_referral')
                    @if($pendingPayments['staff_referral']['amount'] > 0)
                        <div class="mb-3">
                            <strong>Staff Referrals:</strong> ₹{{ number_format($pendingPayments['staff_referral']['amount'], 2) }}
                            <br><small class="text-muted">({{ $pendingPayments['staff_referral']['count'] }} entries)</small>
                        </div>
                    @endif
                @endif

                @if($paymentType === 'subscription_referral')
                    @if($pendingPayments['subscription_referral']['amount'] > 0)
                        <div class="mb-3">
                            <strong>Subscription Referrals:</strong> ₹{{ number_format($pendingPayments['subscription_referral']['amount'], 2) }}
                            <br><small class="text-muted">({{ $pendingPayments['subscription_referral']['count'] }} subscriptions)</small>
                        </div>
                    @endif
                @endif

                @if(
                    ($paymentType === 'service_request' && (float) $pendingPayments['service_request']['amount'] < 0.01)
                    || ($paymentType === 'patient_reward' && (float) $pendingPayments['patient_reward']['amount'] < 0.01)
                    || ($paymentType === 'staff_referral' && (float) $pendingPayments['staff_referral']['amount'] < 0.01)
                    || ($paymentType === 'subscription_referral' && (float) $pendingPayments['subscription_referral']['amount'] < 0.01)
                )
                    <p class="text-muted small mb-0">No payable items in this category right now. You can still record a manual payment below.</p>
                @endif

                <hr>

                @if($paymentDetails && $paymentDetails->count() > 0)
                    <h6 class="mb-2">Payment Items:</h6>
                    <ul class="payment-details-list">
                        @foreach($paymentDetails->take(10) as $detail)
                            <li>
                                @if($paymentType === 'service_request')
                                    Service #{{ $detail->id }} - {{ $detail->serviceType->name ?? 'N/A' }}
                                    <br><small>₹{{ number_format($detail->total_staff_payout, 2) }}</small>
                                @elseif($paymentType === 'patient_reward')
                                    {{ $detail->patient_name }} - {{ $detail->patient_phone }}
                                    <br><small>₹{{ number_format($detail->reward_amount, 2) }}</small>
                                @elseif($paymentType === 'subscription_referral')
                                    @if(isset($detail->final_amount))
                                        Subscription #{{ $detail->source_id }}
                                        @if($detail->sourceSubscription && $detail->sourceSubscription->plan)
                                            - {{ $detail->sourceSubscription->plan->name }}
                                        @endif
                                        <br><small>₹{{ number_format($detail->final_amount, 2) }}</small>
                                    @else
                                        Subscription #{{ $detail->id }} - {{ $detail->plan->name ?? 'N/A' }}
                                        <br><small>₹{{ number_format($detail->referral_commission_amount, 2) }}</small>
                                    @endif
                                @elseif($paymentType === 'staff_referral')
                                    @if(isset($detail->final_amount))
                                        Referral #{{ $detail->source_id }}
                                        <br><small>₹{{ number_format($detail->final_amount, 2) }}</small>
                                    @else
                                        Referral #{{ $detail->id }}
                                        @if($detail->referred)
                                            - {{ $detail->referred->name }}
                                        @endif
                                        <br><small>₹{{ number_format($detail->reward_amount, 2) }}</small>
                                    @endif
                                @endif
                            </li>
                        @endforeach
                        @if($paymentDetails->count() > 10)
                            <li class="text-muted">... and {{ $paymentDetails->count() - 10 }} more</li>
                        @endif
                    </ul>
                @endif
            </div>

            <!-- Staff Payment Methods -->
            <div class="payment-info-card">
                <h5 class="mb-3">
                    <i class="fas fa-qrcode me-2"></i>Staff Payment Methods
                </h5>
                
                @if($staff->qr_code_path)
                    <div class="text-center mb-3">
                        <img src="{{ storage_asset($staff->qr_code_path) }}" alt="QR Code" class="qr-code-preview">
                    </div>
                @else
                    <p class="text-muted text-center">No QR code uploaded</p>
                @endif

                @if($staff->upi_id)
                    <div class="upi-id-display mb-3">
                        <i class="fas fa-mobile-alt me-2"></i>{{ $staff->upi_id }}
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="openUPIForStaff(@json($staff->upi_id), {{ $upiPrefillAmount }}, @json($staff->name))">
                            <i class="fas fa-external-link-alt me-2"></i>Open UPI App
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyUPIId('{{ $staff->upi_id }}')">
                            <i class="fas fa-copy me-2"></i>Copy UPI ID
                        </button>
                    </div>
                @else
                    <p class="text-muted text-center">No UPI ID provided</p>
                @endif

                <hr>
                <form action="{{ route('admin.payments.staff.upi.update', ['staff' => $staff->id]) }}" method="POST" class="mt-3">
                    @csrf
                    <label class="form-label"><strong>Set / Update staff UPI (Admin)</strong></label>
                    <div class="input-group">
                        <input type="text"
                               name="upi_id"
                               class="form-control"
                               value="{{ old('upi_id', $staff->upi_id) }}"
                               placeholder="example@bank"
                               required>
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-save me-1"></i>Save UPI
                        </button>
                    </div>
                    @error('upi_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Shown in the staff account and used when you pay them via UPI (manual payout).</small>
                </form>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="col-md-7">
            @if(!$canAutoSettle)
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No auto-settle pending amount found for this category right now. You can still record this payment manually.
                </div>
            @endif
            <!-- Payment Instructions -->
            <div class="alert alert-info mb-3">
                <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i><strong>Manual payout process:</strong></h6>
                <ol class="mb-0 ps-3">
                    <li>Pay the staff member via bank transfer or UPI (use their UPI / QR on the left if needed).</li>
                    <li>Enter the amount, transaction ID, and upload a payment screenshot below.</li>
                    <li>Submit to record the payout and mark pending items as settled.</li>
                </ol>
                @if($razorpayXPayoutAllowed)
                    <p class="small text-muted mb-0 mt-2">Optional: RazorpayX automatic payout is enabled on this server — you may choose it in Payment mode.</p>
                @endif
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom py-3">
                    <h5 class="mb-0 text-dark">
                        <i class="fas fa-money-check me-2 text-primary"></i>Record payment (after you have paid the staff)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="adminPaymentCategorySelect">Payment category</label>
                        <select id="adminPaymentCategorySelect"
                                class="form-select form-select-lg"
                                autocomplete="off"
                                data-form-base-url="{{ route('admin.payments.form', ['staff' => $staff->id]) }}">
                            @foreach($allowedTypes as $t)
                                @php $pendingForType = (float) ($pendingPayments[$t]['amount'] ?? 0); @endphp
                                <option value="{{ $t }}" @selected($paymentType === $t)>
                                    {{ $paymentTypeLabels[$t] ?? $t }}
                                    — ₹{{ number_format($pendingForType, 2) }}@if($pendingForType < 0.01) (no pending)@endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Changing category reloads this page and updates the item list and suggested amount for that category.</small>
                    </div>

                    <form action="{{ route('admin.payments.process', $staff->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="payment_type" value="{{ $paymentType }}">

                        @if($razorpayXPayoutAllowed && $manualPayoutEnabled)
                            <div class="mb-3">
                                <label class="form-label">Payment mode <span class="text-danger">*</span></label>
                                <select name="payment_mode" id="paymentModeSelect" class="form-select" onchange="toggleAdminPaymentModeFields()">
                                    <option value="manual" @selected(old('payment_mode', 'manual') === 'manual')>
                                        Manual payout (transaction ID + screenshot)
                                    </option>
                                    <option value="razorpayx" @selected(old('payment_mode') === 'razorpayx')>
                                        RazorpayX UPI payout (automatic)
                                    </option>
                                </select>
                                <small id="gatewayModeHint" class="text-muted">RazorpayX requires staff UPI and valid gateway credentials.</small>
                            </div>
                            @if(!$staff->upi_id)
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Staff UPI is missing for RazorpayX. Add UPI on the left or use manual payout.
                                </div>
                            @endif
                        @elseif($manualPayoutEnabled)
                            <input type="hidden" name="payment_mode" value="manual">
                            <div class="alert alert-secondary py-2 small mb-3">
                                <i class="fas fa-hand-holding-usd me-1"></i>
                                <strong>Manual payout only</strong> — pay staff outside the app, then record proof here.
                            </div>
                        @else
                            <div class="alert alert-danger mb-3">
                                Manual staff payouts are disabled in configuration. Contact system administrator.
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Amount paid (₹) <span class="text-danger">*</span></label>
                            <input type="number"
                                   name="amount"
                                   class="form-control form-control-lg"
                                   step="0.01"
                                   min="0.01"
                                   value="{{ old('amount', $suggestedAmountStr) }}"
                                   required>
                            <small class="text-muted">Suggested from pending total for <strong>{{ $paymentTypeLabels[$paymentType] ?? $paymentType }}</strong>; you may edit before submit.</small>
                        </div>

                        <div class="mb-3 manual-only-field">
                            <label class="form-label">Transaction ID <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="transaction_id"
                                   id="transaction_id"
                                   class="form-control"
                                   value="{{ old('transaction_id') }}"
                                   placeholder="Enter transaction ID from payment app">
                            <small class="text-muted">Required for manual payout mode.</small>
                        </div>

                        <div class="mb-3 manual-only-field">
                            <label class="form-label">Payment confirmation screenshot <span class="text-danger">*</span></label>
                            <input type="file"
                                   name="payment_screenshot"
                                   id="payment_screenshot"
                                   class="form-control"
                                   accept="image/*">
                            <small class="text-muted">Required for manual payout mode.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes about this payment (optional)...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-lg text-white payment-record-submit">
                                <i class="fas fa-check-circle me-2"></i>Record payment &amp; mark as paid
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


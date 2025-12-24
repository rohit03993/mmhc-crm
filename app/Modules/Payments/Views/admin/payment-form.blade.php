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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
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
</style>
@endsection

@section('content')
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
                <small>Total Pending</small>
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
                
                @if($paymentType === 'all' || $paymentType === 'service_request')
                    @if($pendingPayments['service_request']['amount'] > 0)
                        <div class="mb-3">
                            <strong>Service Requests:</strong> ₹{{ number_format($pendingPayments['service_request']['amount'], 2) }}
                            <br><small class="text-muted">({{ $pendingPayments['service_request']['count'] }} pending)</small>
                        </div>
                    @endif
                @endif

                @if($paymentType === 'all' || $paymentType === 'patient_reward')
                    @if($pendingPayments['patient_reward']['amount'] > 0)
                        <div class="mb-3">
                            <strong>Patient Rewards:</strong> ₹{{ number_format($pendingPayments['patient_reward']['amount'], 2) }}
                            <br><small class="text-muted">({{ $pendingPayments['patient_reward']['count'] }} entries)</small>
                        </div>
                    @endif
                @endif

                @if($paymentType === 'all' || $paymentType === 'staff_referral')
                    @if($pendingPayments['staff_referral']['amount'] > 0)
                        <div class="mb-3">
                            <strong>Staff Referrals:</strong> ₹{{ number_format($pendingPayments['staff_referral']['amount'], 2) }}
                            <br><small class="text-muted">({{ $pendingPayments['staff_referral']['count'] }} referrals)</small>
                        </div>
                    @endif
                @endif

                @if($paymentType === 'all' || $paymentType === 'subscription_referral')
                    @if($pendingPayments['subscription_referral']['amount'] > 0)
                        <div class="mb-3">
                            <strong>Subscription Referrals:</strong> ₹{{ number_format($pendingPayments['subscription_referral']['amount'], 2) }}
                            <br><small class="text-muted">({{ $pendingPayments['subscription_referral']['count'] }} subscriptions)</small>
                        </div>
                    @endif
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
                                @elseif($paymentType === 'staff_referral')
                                    Referred: {{ $detail->referred->name ?? 'N/A' }}
                                    <br><small>₹{{ number_format($detail->reward_amount, 2) }}</small>
                                @elseif($paymentType === 'subscription_referral')
                                    Subscription #{{ $detail->id }} - {{ $detail->plan->name ?? 'N/A' }}
                                    <br><small>₹{{ number_format($detail->referral_commission_amount, 2) }}</small>
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
                        <img src="{{ asset('storage/' . $staff->qr_code_path) }}" alt="QR Code" class="qr-code-preview">
                    </div>
                @else
                    <p class="text-muted text-center">No QR code uploaded</p>
                @endif

                @if($staff->upi_id)
                    <div class="upi-id-display">
                        <i class="fas fa-mobile-alt me-2"></i>{{ $staff->upi_id }}
                    </div>
                @else
                    <p class="text-muted text-center">No UPI ID provided</p>
                @endif
            </div>
        </div>

        <!-- Payment Form -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-money-check me-2"></i>Payment Form
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.payments.process', $staff->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <input type="hidden" name="payment_type" value="{{ $paymentType }}">

                        <div class="mb-3">
                            <label class="form-label">Payment Type</label>
                            <input type="text" class="form-control" value="{{ ucfirst(str_replace('_', ' ', $paymentType)) }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="amount" 
                                   class="form-control" 
                                   step="0.01" 
                                   min="0.01"
                                   value="{{ $paymentType === 'all' ? number_format($pendingPayments['total'], 2, '.', '') : number_format($pendingPayments[$paymentType]['amount'], 2, '.', '') }}"
                                   required>
                            <small class="text-muted">Enter the amount to be paid</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Transaction ID</label>
                            <input type="text" name="transaction_id" class="form-control" placeholder="Enter transaction ID">
                            <small class="text-muted">Optional: UPI transaction ID or reference number</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Screenshot</label>
                            <input type="file" name="payment_screenshot" class="form-control" accept="image/*">
                            <small class="text-muted">Optional: Upload payment confirmation screenshot</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes about this payment..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check-circle me-2"></i>Process Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


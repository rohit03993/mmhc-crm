@extends('auth::layout')

@section('title', 'Payment History - Staff')

@section('head')
<style>
    .payment-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    .payment-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
    }
    .payment-type-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .payment-amount {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2563eb;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-history me-2"></i>Payment History
        </h2>
        <a href="{{ route('staff.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>

    @if($payments->count() > 0)
        @foreach($payments as $payment)
            <div class="payment-card">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <span class="payment-type-badge 
                                    @if($payment->payment_type === 'service_request') bg-primary
                                    @elseif($payment->payment_type === 'patient_reward') bg-warning
                                    @elseif($payment->payment_type === 'staff_referral') bg-info
                                    @else bg-success
                                    @endif text-white">
                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-1">{{ $payment->admin->name ?? 'Admin' }}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $payment->paid_at->format('M d, Y h:i A') }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="payment-amount">₹{{ number_format($payment->amount, 2) }}</div>
                        @if($payment->transaction_id)
                            <small class="text-muted">
                                <i class="fas fa-receipt me-1"></i>{{ $payment->transaction_id }}
                            </small>
                        @endif
                    </div>
                    <div class="col-md-2 text-end">
                        @if($payment->payment_screenshot)
                            <a href="{{ storage_asset($payment->payment_screenshot) }}" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-image"></i> View Screenshot
                            </a>
                        @endif
                    </div>
                </div>
                @if($payment->notes)
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted">
                            <i class="fas fa-sticky-note me-1"></i>{{ $payment->notes }}
                        </small>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5>No Payment History</h5>
                <p class="text-muted">You haven't received any payments yet.</p>
            </div>
        </div>
    @endif
</div>
@endsection


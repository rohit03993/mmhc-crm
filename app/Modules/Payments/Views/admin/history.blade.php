@extends('auth::layout')

@section('title', 'Payment History - Admin')

@section('head')
<style>
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
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
    .total-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        text-align: center;
    }
    .total-amount {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-history me-2"></i>Payment History
        </h2>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Payments
        </a>
    </div>

    <!-- Total Summary -->
    <div class="total-summary">
        <div class="total-amount">₹{{ number_format($totalPaid, 2) }}</div>
        <div>Total Amount Paid</div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.payments.history') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Payment Type</label>
                <select name="type" class="form-select">
                    <option value="all" {{ $filterType === 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="service_request" {{ $filterType === 'service_request' ? 'selected' : '' }}>Service Requests</option>
                    <option value="patient_reward" {{ $filterType === 'patient_reward' ? 'selected' : '' }}>Patient Rewards</option>
                    <option value="staff_referral" {{ $filterType === 'staff_referral' ? 'selected' : '' }}>Staff Referrals</option>
                    <option value="subscription_referral" {{ $filterType === 'subscription_referral' ? 'selected' : '' }}>Subscription Referrals</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Staff Member</label>
                <select name="staff" class="form-select">
                    <option value="all" {{ $filterStaff === 'all' ? 'selected' : '' }}>All Staff</option>
                    @foreach($allStaff as $staffMember)
                        <option value="{{ $staffMember->id }}" {{ $filterStaff == $staffMember->id ? 'selected' : '' }}>
                            {{ $staffMember->name }} ({{ ucfirst($staffMember->role) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Payment List -->
    @if($payments->count() > 0)
        @foreach($payments as $payment)
            <div class="payment-card">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <h6 class="mb-1">{{ $payment->staff->name }}</h6>
                        <small class="text-muted">{{ ucfirst($payment->staff->role) }}</small>
                    </div>
                    <div class="col-md-2">
                        <span class="badge 
                            @if($payment->payment_type === 'service_request') bg-primary
                            @elseif($payment->payment_type === 'patient_reward') bg-warning
                            @elseif($payment->payment_type === 'staff_referral') bg-info
                            @elseif($payment->payment_type === 'subscription_referral') bg-success
                            @endif payment-type-badge">
                            {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}
                        </span>
                    </div>
                    <div class="col-md-2">
                        <strong class="text-primary">₹{{ number_format($payment->amount, 2) }}</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            {{ $payment->paid_at->format('M d, Y') }}
                        </small>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            {{ $payment->paid_at->format('h:i A') }}
                        </small>
                    </div>
                    <div class="col-md-3">
                        @if($payment->transaction_id)
                            <small class="d-block mb-1">
                                <strong>Transaction ID:</strong> {{ $payment->transaction_id }}
                            </small>
                        @endif
                        @if($payment->notes)
                            <small class="text-muted d-block">
                                <i class="fas fa-sticky-note me-1"></i>{{ Str::limit($payment->notes, 30) }}
                            </small>
                        @endif
                        @if($payment->payment_screenshot)
                            <a href="{{ storage_asset($payment->payment_screenshot) }}" target="_blank" class="btn btn-sm btn-outline-secondary mt-1">
                                <i class="fas fa-image me-1"></i>View Screenshot
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Pagination -->
        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5>No Payments Found</h5>
                <p class="text-muted">No payments match your filter criteria.</p>
            </div>
        </div>
    @endif
</div>
@endsection


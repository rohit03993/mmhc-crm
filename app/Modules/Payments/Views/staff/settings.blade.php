@extends('auth::layout')

@section('title', 'Payment Settings - Staff')
@section('page-title', 'Payment Settings')

@section('head')
@include('services::partials.mobile-assets')
<style>
    .settings-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .qr-preview {
        max-width: 250px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-top: 1rem;
    }
    .upi-display {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
        margin-top: 1rem;
    }
</style>
@endsection

@section('content')
<div class="mobile-app-container hc-mobile-shell" data-mmhc-ptr>
<div class="app-mobile-header d-md-none">
    <div class="d-flex align-items-center">
        <a href="{{ route('staff.dashboard') }}" class="app-back-btn"><i class="fas fa-arrow-left"></i></a>
        <h5 class="app-header-title text-white mb-0 ms-2">Payment Settings</h5>
    </div>
</div>
<div class="container-fluid px-3 px-md-4 py-4">
    <div class="d-none d-md-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-cog me-2"></i>Payment Settings
        </h2>
        <a href="{{ route('staff.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="settings-card">
                <h5 class="mb-4">
                    <i class="fas fa-wallet me-2"></i>Payment Information
                </h5>
                <p class="text-muted mb-4">
                    Add your UPI ID and QR code so that admin can make payments directly to you.
                </p>

                <form action="{{ route('staff.payments.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label">UPI ID</label>
                        <input type="text" 
                               name="upi_id" 
                               class="form-control" 
                               value="{{ old('upi_id', $user->upi_id) }}"
                               placeholder="e.g., yourname@paytm or yourname@phonepe">
                        <small class="text-muted">Enter your UPI ID for receiving payments</small>
                        @error('upi_id')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">QR Code</label>
                        <input type="file" 
                               name="qr_code" 
                               class="form-control" 
                               accept="image/*">
                        <small class="text-muted">Upload your payment QR code image (JPEG, PNG, JPG, GIF - Max 2MB)</small>
                        @error('qr_code')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        
                        @if($user->qr_code_path)
                            <div class="mt-3">
                                <p class="mb-2"><strong>Current QR Code:</strong></p>
                                <img src="{{ storage_asset($user->qr_code_path) }}" alt="QR Code" class="qr-preview">
                            </div>
                        @endif
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Save Settings
                        </button>
                    </div>
                </form>
            </div>

            <div class="settings-card">
                <h5 class="mb-3">
                    <i class="fas fa-info-circle me-2"></i>Payment Information
                </h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Admin will use this information to make payments to you
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Payments will be processed for: Service Requests, Patient Rewards, Staff Referrals, and Subscription Referrals
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        You can view your payment history in the "Payment History" section
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
</div>
@endsection


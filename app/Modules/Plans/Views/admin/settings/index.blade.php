@extends('auth::layout')

@section('content')
<div class="container-fluid px-3 py-4">
    <div class="row">
        <div class="col-12 col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cog me-2"></i>Subscription Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.subscription-settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- GST Rate -->
                        <div class="mb-4">
                            <label for="gst_rate" class="form-label">
                                <i class="fas fa-percentage me-2 text-primary"></i>GST Rate (%)
                            </label>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   max="100"
                                   class="form-control @error('gst_rate') is-invalid @enderror" 
                                   id="gst_rate" 
                                   name="gst_rate" 
                                   value="{{ old('gst_rate', $gstRate) }}"
                                   required>
                            <small class="text-muted">GST rate applied to subscription base amount (currently 18%)</small>
                            @error('gst_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Referral Commission Rate -->
                        <div class="mb-4">
                            <label for="referral_commission_rate" class="form-label">
                                <i class="fas fa-handshake me-2 text-success"></i>Referral Commission Rate (%)
                            </label>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   max="100"
                                   class="form-control @error('referral_commission_rate') is-invalid @enderror" 
                                   id="referral_commission_rate" 
                                   name="referral_commission_rate" 
                                   value="{{ old('referral_commission_rate', $commissionRate) }}"
                                   required>
                            <small class="text-muted">Commission rate for staff (nurse/caregiver) who refer patients to subscribe (default: 5%)</small>
                            @error('referral_commission_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- UPI ID -->
                        <div class="mb-4">
                            <label for="upi_id" class="form-label">
                                <i class="fas fa-mobile-alt me-2 text-info"></i>UPI ID
                            </label>
                            <input type="text" 
                                   class="form-control @error('upi_id') is-invalid @enderror" 
                                   id="upi_id" 
                                   name="upi_id" 
                                   value="{{ old('upi_id', $upiId) }}"
                                   placeholder="mmhc@paytm"
                                   required>
                            <small class="text-muted">UPI ID for manual subscription payments</small>
                            @error('upi_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Merchant Name -->
                        <div class="mb-4">
                            <label for="upi_merchant_name" class="form-label">
                                <i class="fas fa-store me-2 text-warning"></i>UPI Merchant Name
                            </label>
                            <input type="text" 
                                   class="form-control @error('upi_merchant_name') is-invalid @enderror" 
                                   id="upi_merchant_name" 
                                   name="upi_merchant_name" 
                                   value="{{ old('upi_merchant_name', $merchantName) }}"
                                   placeholder="MMHC"
                                   required>
                            <small class="text-muted">Merchant name displayed in UPI payment apps</small>
                            @error('upi_merchant_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- QR Code Upload -->
                        <div class="mb-4">
                            <label for="qr_code" class="form-label">
                                <i class="fas fa-qrcode me-2 text-danger"></i>Payment QR Code
                            </label>
                            @if($qrCode)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $qrCode) }}" 
                                     alt="Current QR Code" 
                                     class="img-thumbnail"
                                     style="max-width: 200px; height: auto;">
                                <p class="text-muted small mt-1">Current QR Code</p>
                            </div>
                            @endif
                            <input type="file" 
                                   class="form-control @error('qr_code') is-invalid @enderror" 
                                   id="qr_code" 
                                   name="qr_code" 
                                   accept="image/jpeg,image/png,image/jpg"
                                   onchange="previewQRCode(this)">
                            <small class="text-muted">Upload QR code image for payment (JPG, PNG, Max 2MB)</small>
                            <div id="qrCodePreview" class="mt-2"></div>
                            @error('qr_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewQRCode(input) {
    const preview = document.getElementById('qrCodePreview');
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'img-thumbnail';
            img.style.maxWidth = '200px';
            img.style.height = 'auto';
            preview.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection


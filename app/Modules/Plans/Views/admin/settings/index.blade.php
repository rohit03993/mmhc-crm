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
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <form action="{{ route('admin.subscription-settings.update') }}" method="POST">
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

                        <div class="mb-4">
                            <label for="gst_number" class="form-label">
                                <i class="fas fa-id-card me-2 text-success"></i>GST Number (GSTIN)
                            </label>
                            <input type="text"
                                   class="form-control @error('gst_number') is-invalid @enderror"
                                   id="gst_number"
                                   name="gst_number"
                                   value="{{ old('gst_number', $gstNumber) }}"
                                   placeholder="e.g. 27AAAAA0000A1Z5"
                                   maxlength="20"
                                   autocomplete="off">
                            <small class="text-muted">Shown on subscription tax invoices. Leave blank if you do not have a GSTIN — nothing will appear on invoices.</small>
                            @error('gst_number')
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
                            <small class="text-muted">UPI ID for manual subscription payments. Saved in the database and used on patient/student payment pages.</small>
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
@endsection


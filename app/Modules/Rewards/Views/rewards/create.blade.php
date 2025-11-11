@extends('auth::layout')

@section('title', 'Add Patient Details')
@section('page-title', 'Add Patient Details')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-plus me-2 text-primary"></i>
                        Patient Information
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Submit the patient details below. Each successful entry awards you <strong>1 reward point (₹10)</strong>.
                    </p>

                    <form method="POST" action="{{ route('rewards.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Patient Name</label>
                            <input type="text" name="patient_name" value="{{ old('patient_name') }}"
                                   class="form-control @error('patient_name') is-invalid @enderror" required>
                            @error('patient_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Patient Mobile Number</label>
                            <div class="input-group">
                                <span class="input-group-text">+91</span>
                                <input type="text"
                                       name="patient_phone"
                                       value="{{ old('patient_phone') }}"
                                       pattern="[0-9]{10}"
                                       maxlength="10"
                                       inputmode="numeric"
                                       class="form-control @error('patient_phone_digits') is-invalid @enderror"
                                       placeholder="Enter 10-digit mobile number"
                                       required>
                            </div>
                            <small class="text-muted">Indian mobile numbers only. Each number can be submitted once.</small>
                            @error('patient_phone_digits')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hospital Name</label>
                            <input type="text" name="hospital_name" value="{{ old('hospital_name') }}"
                                   class="form-control @error('hospital_name') is-invalid @enderror" required>
                            @error('hospital_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Treatment Details</label>
                            <textarea name="treatment_details" rows="4"
                                      class="form-control @error('treatment_details') is-invalid @enderror"
                                      placeholder="Brief description of treatment (optional)">{{ old('treatment_details') }}</textarea>
                            @error('treatment_details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('rewards.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Submit & Earn Reward
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


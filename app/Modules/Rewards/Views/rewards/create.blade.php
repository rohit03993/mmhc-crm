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
                            <input type="text" name="patient_phone" value="{{ old('patient_phone') }}"
                                   class="form-control @error('patient_phone') is-invalid @enderror" required>
                            @error('patient_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
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


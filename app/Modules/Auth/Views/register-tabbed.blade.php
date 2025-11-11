@extends('auth::layout')

@section('title', 'Register - MMHC CRM')

@section('head')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="{{ asset('images/med-logo.png') }}" alt="MeD Miracle Health Care" class="brand-logo brand-logo--auth">
                        <p class="text-muted mb-0">Create your account</p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Tab Navigation -->
                    <ul class="nav nav-pills nav-fill mb-4" id="registrationTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="patient-tab" data-bs-toggle="pill" data-bs-target="#patient-form" type="button" role="tab">
                                <i class="fas fa-user-injured me-2 d-none d-sm-inline"></i>
                                <span class="d-block d-sm-none">Patient</span>
                                <span class="d-none d-sm-block">Patient Registration</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="nurse-tab" data-bs-toggle="pill" data-bs-target="#nurse-form" type="button" role="tab">
                                <i class="fas fa-user-nurse me-2 d-none d-sm-inline"></i>
                                <span class="d-block d-sm-none">Nurse</span>
                                <span class="d-none d-sm-block">Nurse Registration</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="caregiver-tab" data-bs-toggle="pill" data-bs-target="#caregiver-form" type="button" role="tab">
                                <i class="fas fa-user-md me-2 d-none d-sm-inline"></i>
                                <span class="d-block d-sm-none">Caregiver</span>
                                <span class="d-none d-sm-block">Caregiver Registration</span>
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="registrationTabContent">
                        <!-- Patient Registration Form -->
                        <div class="tab-pane fade show active" id="patient-form" role="tabpanel">
                            <div class="row">
                                <!-- Mobile: Full width, Desktop: Half width -->
                                <div class="col-12 col-lg-6 mb-4 mb-lg-0">
                                    <div class="text-center mb-4">
                                        <i class="fas fa-user-injured fa-3x text-primary mb-3"></i>
                                        <h4 class="text-primary">Patient Registration</h4>
                                        <p class="text-muted">Get healthcare services at your doorstep</p>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <form method="POST" action="{{ route('auth.register.post') }}" id="patientForm">
                                        @csrf
                                        <input type="hidden" name="role" value="patient">
                                        
                                        <div class="mb-3">
                                            <label for="patient_name" class="form-label">Full Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-user"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control @error('name') is-invalid @enderror" 
                                                       id="patient_name" 
                                                       name="name" 
                                                       value="{{ old('name') }}" 
                                                       required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="patient_email" class="form-label">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-envelope"></i>
                                                </span>
                                                <input type="email" 
                                                       class="form-control @error('email') is-invalid @enderror" 
                                                       id="patient_email" 
                                                       name="email" 
                                                       value="{{ old('email') }}" 
                                                       required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="patient_phone" class="form-label">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-phone"></i>
                                                </span>
                                                <input type="tel" 
                                                       class="form-control @error('phone') is-invalid @enderror" 
                                                       id="patient_phone" 
                                                       name="phone" 
                                                       value="{{ old('phone') }}" 
                                                       pattern="[0-9]{10}"
                                                       maxlength="10"
                                                       placeholder="Enter 10-digit phone number"
                                                       required>
                                            </div>
                                            <div class="form-text">Enter exactly 10 digits (e.g., 9876543210)</div>
                                            @error('phone')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="patient_dob" class="form-label">Date of Birth</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-calendar"></i>
                                                </span>
                                                <input type="date" 
                                                       class="form-control" 
                                                       id="patient_dob" 
                                                       name="date_of_birth" 
                                                       value="{{ old('date_of_birth') }}">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="patient_address" class="form-label">Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </span>
                                                <textarea class="form-control" 
                                                          id="patient_address" 
                                                          name="address" 
                                                          rows="3" 
                                                          placeholder="Enter your full address">{{ old('address') }}</textarea>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="patient_password" class="form-label">Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                                <input type="password" 
                                                       class="form-control @error('password') is-invalid @enderror" 
                                                       id="patient_password" 
                                                       name="password" 
                                                       required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="patient_password_confirmation" class="form-label">Confirm Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                                <input type="password" 
                                                       class="form-control" 
                                                       id="patient_password_confirmation" 
                                                       name="password_confirmation" 
                                                       required>
                                            </div>
                                        </div>

                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="patient_terms" required>
                                            <label class="form-check-label" for="patient_terms">
                                                I agree to the <a href="#" class="text-primary">Terms and Conditions</a>
                                            </label>
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fas fa-user-plus me-2"></i>
                                                Register as Patient
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Nurse Registration Form -->
                        <div class="tab-pane fade" id="nurse-form" role="tabpanel">
                            <div class="row">
                                <!-- Mobile: Full width, Desktop: Half width -->
                                <div class="col-12 col-lg-6 mb-4 mb-lg-0">
                                    <div class="text-center mb-4">
                                        <i class="fas fa-user-nurse fa-3x text-info mb-3"></i>
                                        <h4 class="text-info">Nurse Registration</h4>
                                        <p class="text-muted">Licensed nursing professionals - Higher earning potential</p>
                                        <div class="alert alert-info">
                                            <small><strong>Nurse Benefits:</strong><br>
                                            • ₹2000/day (24h) • ₹1200/day (12h) • ₹800/day (8h)<br>
                                            • Licensed professional rates • Priority assignments</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <form method="POST" action="{{ route('auth.register.post') }}" id="nurseForm" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="role" value="nurse">
                                        
                                        <div class="mb-3">
                                            <label for="nurse_name" class="form-label">Full Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-user"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control @error('name') is-invalid @enderror" 
                                                       id="nurse_name" 
                                                       name="name" 
                                                       value="{{ old('name') }}" 
                                                       required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="nurse_email" class="form-label">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-envelope"></i>
                                                </span>
                                                <input type="email" 
                                                       class="form-control @error('email') is-invalid @enderror" 
                                                       id="nurse_email" 
                                                       name="email" 
                                                       value="{{ old('email') }}" 
                                                       required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="nurse_phone" class="form-label">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-phone"></i>
                                                </span>
                                                <input type="tel" 
                                                       class="form-control @error('phone') is-invalid @enderror" 
                                                       id="nurse_phone" 
                                                       name="phone" 
                                                       value="{{ old('phone') }}" 
                                                       pattern="[0-9]{10}"
                                                       maxlength="10"
                                                       placeholder="Enter 10-digit phone number"
                                                       required>
                                            </div>
                                            <div class="form-text">Enter exactly 10 digits (e.g., 9876543210)</div>
                                            @error('phone')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="nurse_dob" class="form-label">Date of Birth</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-calendar"></i>
                                                </span>
                                                <input type="date" 
                                                       class="form-control" 
                                                       id="nurse_dob" 
                                                       name="date_of_birth" 
                                                       value="{{ old('date_of_birth') }}">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="nurse_qualification" class="form-label">Nursing Qualification</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-graduation-cap"></i>
                                                </span>
                                                <select class="form-control" id="nurse_qualification" name="qualification" required>
                                                    <option value="">Select Qualification</option>
                                                    <option value="GNM" {{ old('qualification') == 'GNM' ? 'selected' : '' }}>GNM (General Nursing & Midwifery)</option>
                                                    <option value="B.Sc Nursing" {{ old('qualification') == 'B.Sc Nursing' ? 'selected' : '' }}>B.Sc Nursing</option>
                                                    <option value="M.Sc Nursing" {{ old('qualification') == 'M.Sc Nursing' ? 'selected' : '' }}>M.Sc Nursing</option>
                                                    <option value="ANM" {{ old('qualification') == 'ANM' ? 'selected' : '' }}>ANM (Auxiliary Nurse Midwife)</option>
                                                    <option value="Other" {{ old('qualification') == 'Other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="nurse_experience" class="form-label">Years of Experience</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-clock"></i>
                                                </span>
                                                <select class="form-control" id="nurse_experience" name="experience" required>
                                                    <option value="">Select Experience</option>
                                                    <option value="0-1" {{ old('experience') == '0-1' ? 'selected' : '' }}>0-1 years</option>
                                                    <option value="1-3" {{ old('experience') == '1-3' ? 'selected' : '' }}>1-3 years</option>
                                                    <option value="3-5" {{ old('experience') == '3-5' ? 'selected' : '' }}>3-5 years</option>
                                                    <option value="5-10" {{ old('experience') == '5-10' ? 'selected' : '' }}>5-10 years</option>
                                                    <option value="10+" {{ old('experience') == '10+' ? 'selected' : '' }}>10+ years</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="nurse_address" class="form-label">Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </span>
                                                <textarea class="form-control" 
                                                          id="nurse_address" 
                                                          name="address" 
                                                          rows="3" 
                                                          placeholder="Enter your full address">{{ old('address') }}</textarea>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="nurse_documents" class="form-label">Professional Documents</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-file-upload"></i>
                                                </span>
                                                <input type="file" 
                                                       class="form-control" 
                                                       id="nurse_documents" 
                                                       name="documents[]" 
                                                       multiple 
                                                       accept=".pdf,.jpg,.jpeg,.png">
                                            </div>
                                            <div class="form-text">Upload nursing license, certificates, ID proof (PDF, JPG, PNG - Max 2MB each)</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="nurse_password" class="form-label">Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                                <input type="password" 
                                                       class="form-control @error('password') is-invalid @enderror" 
                                                       id="nurse_password" 
                                                       name="password" 
                                                       required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="nurse_password_confirmation" class="form-label">Confirm Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                                <input type="password" 
                                                       class="form-control" 
                                                       id="nurse_password_confirmation" 
                                                       name="password_confirmation" 
                                                       required>
                                            </div>
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-info btn-lg">
                                                <i class="fas fa-user-nurse me-2"></i>
                                                Register as Nurse
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Caregiver Registration Form -->
                        <div class="tab-pane fade" id="caregiver-form" role="tabpanel">
                            <div class="row">
                                <!-- Mobile: Full width, Desktop: Half width -->
                                <div class="col-12 col-lg-6 mb-4 mb-lg-0">
                                    <div class="text-center mb-4">
                                        <i class="fas fa-user-md fa-3x text-success mb-3"></i>
                                        <h4 class="text-success">Caregiver Registration</h4>
                                        <p class="text-muted">General support staff - Start your healthcare journey</p>
                                        <div class="alert alert-success">
                                            <small><strong>Caregiver Benefits:</strong><br>
                                            • ₹1500/day (24h) • ₹900/day (12h) • ₹700/day (8h)<br>
                                            • General support rates • Flexible assignments</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <form method="POST" action="{{ route('auth.register.post') }}" id="caregiverForm" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="role" value="caregiver">
                                        
                                        <div class="mb-3">
                                            <label for="caregiver_name" class="form-label">Full Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-user"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control @error('name') is-invalid @enderror" 
                                                       id="caregiver_name" 
                                                       name="name" 
                                                       value="{{ old('name') }}" 
                                                       required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="caregiver_email" class="form-label">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-envelope"></i>
                                                </span>
                                                <input type="email" 
                                                       class="form-control @error('email') is-invalid @enderror" 
                                                       id="caregiver_email" 
                                                       name="email" 
                                                       value="{{ old('email') }}" 
                                                       required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="caregiver_phone" class="form-label">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-phone"></i>
                                                </span>
                                                <input type="tel" 
                                                       class="form-control @error('phone') is-invalid @enderror" 
                                                       id="caregiver_phone" 
                                                       name="phone" 
                                                       value="{{ old('phone') }}" 
                                                       pattern="[0-9]{10}"
                                                       maxlength="10"
                                                       placeholder="Enter 10-digit phone number"
                                                       required>
                                            </div>
                                            <div class="form-text">Enter exactly 10 digits (e.g., 9876543210)</div>
                                            @error('phone')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="caregiver_dob" class="form-label">Date of Birth</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-calendar"></i>
                                                </span>
                                                <input type="date" 
                                                       class="form-control" 
                                                       id="caregiver_dob" 
                                                       name="date_of_birth" 
                                                       value="{{ old('date_of_birth') }}">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="caregiver_qualification" class="form-label">Professional Qualification</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-graduation-cap"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="caregiver_qualification" 
                                                       name="qualification" 
                                                       value="{{ old('qualification') }}" 
                                                       placeholder="e.g., B.Sc Nursing, GNM, ANM">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="caregiver_experience" class="form-label">Years of Experience</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-briefcase"></i>
                                                </span>
                                                <select class="form-select" id="caregiver_experience" name="experience">
                                                    <option value="">Select Experience</option>
                                                    <option value="0-1" {{ old('experience') == '0-1' ? 'selected' : '' }}>0-1 years</option>
                                                    <option value="1-3" {{ old('experience') == '1-3' ? 'selected' : '' }}>1-3 years</option>
                                                    <option value="3-5" {{ old('experience') == '3-5' ? 'selected' : '' }}>3-5 years</option>
                                                    <option value="5+" {{ old('experience') == '5+' ? 'selected' : '' }}>5+ years</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="caregiver_address" class="form-label">Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </span>
                                                <textarea class="form-control" 
                                                          id="caregiver_address" 
                                                          name="address" 
                                                          rows="3" 
                                                          placeholder="Enter your full address">{{ old('address') }}</textarea>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="caregiver_documents" class="form-label">Upload Documents</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-file-upload"></i>
                                                </span>
                                                <input type="file" 
                                                       class="form-control" 
                                                       id="caregiver_documents" 
                                                       name="documents[]" 
                                                       multiple 
                                                       accept=".pdf,.jpg,.jpeg,.png">
                                            </div>
                                            <div class="form-text">Upload your certificates, licenses, and ID documents (PDF, JPG, PNG)</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="caregiver_password" class="form-label">Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                                <input type="password" 
                                                       class="form-control @error('password') is-invalid @enderror" 
                                                       id="caregiver_password" 
                                                       name="password" 
                                                       required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="caregiver_password_confirmation" class="form-label">Confirm Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                                <input type="password" 
                                                       class="form-control" 
                                                       id="caregiver_password_confirmation" 
                                                       name="password_confirmation" 
                                                       required>
                                            </div>
                                        </div>

                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="caregiver_terms" required>
                                            <label class="form-check-label" for="caregiver_terms">
                                                I agree to the <a href="#" class="text-primary">Terms and Conditions</a>
                                            </label>
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fas fa-user-plus me-2"></i>
                                                Register as Caregiver
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-muted">
                            Already have an account? 
                            <a href="{{ route('auth.login') }}" class="text-primary text-decoration-none">
                                Sign in here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Mobile-First Responsive Design */
.nav-pills .nav-link {
    border-radius: 0.5rem;
    margin: 0 0.25rem;
    transition: all 0.3s ease;
    font-size: 0.9rem;
    padding: 0.75rem 1rem;
}

.nav-pills .nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.nav-pills .nav-link:not(.active) {
    background-color: #f8f9fa;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.nav-pills .nav-link:hover:not(.active) {
    background-color: #e9ecef;
    color: #495057;
}

.tab-pane {
    min-height: 400px;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    min-width: 45px;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
}

.card {
    border-radius: 1rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Logo styles removed - now using text-based logo */

/* Mobile Optimizations */
@media (max-width: 768px) {
    .container {
        padding: 0.5rem;
    }
    
    .card-body {
        padding: 1.5rem !important;
    }
    
    .nav-pills .nav-link {
        font-size: 0.8rem;
        padding: 0.6rem 0.8rem;
        margin: 0 0.1rem;
    }
    
    .nav-pills .nav-link i {
        display: none; /* Hide icons on mobile to save space */
    }
    
    .tab-pane {
        min-height: auto;
    }
    
    .form-control {
        font-size: 16px; /* Prevents zoom on iOS */
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
    
    .fa-3x {
        font-size: 2rem !important; /* Smaller icons on mobile */
    }
    
    .mb-4 {
        margin-bottom: 1rem !important;
    }
}

@media (max-width: 576px) {
    .card-body {
        padding: 1rem !important;
    }
    
    .nav-pills {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .nav-pills .nav-link {
        width: 100%;
        margin: 0;
        text-align: center;
    }
    
    .input-group {
        flex-wrap: nowrap;
    }
    
    .input-group-text {
        min-width: 40px;
        font-size: 0.9rem;
    }
}

/* Tablet Optimizations */
@media (min-width: 768px) and (max-width: 1024px) {
    .col-lg-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }
    
    .nav-pills .nav-link {
        font-size: 0.85rem;
        padding: 0.7rem 0.9rem;
    }
}

/* Large Desktop Optimizations */
@media (min-width: 1200px) {
    .container {
        max-width: 1200px;
    }
    
    .card-body {
        padding: 3rem !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle tab switching
    const patientTab = document.getElementById('patient-tab');
    const caregiverTab = document.getElementById('caregiver-tab');
    const patientForm = document.getElementById('patient-form');
    const caregiverForm = document.getElementById('caregiver-form');

    // Phone number validation for both forms
    const patientPhone = document.getElementById('patient_phone');
    const caregiverPhone = document.getElementById('caregiver_phone');

    // Function to validate phone number
    function validatePhone(phoneInput) {
        const phone = phoneInput.value.replace(/\D/g, ''); // Remove non-digits
        const isValid = phone.length === 10;
        
        if (phoneInput.value && !isValid) {
            phoneInput.setCustomValidity('Phone number must be exactly 10 digits');
            phoneInput.classList.add('is-invalid');
        } else {
            phoneInput.setCustomValidity('');
            phoneInput.classList.remove('is-invalid');
        }
        
        return isValid;
    }

    // Function to format phone input (numbers only)
    function formatPhoneInput(phoneInput) {
        // Remove any non-digit characters
        let value = phoneInput.value.replace(/\D/g, '');
        
        // Limit to 10 digits
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        
        phoneInput.value = value;
        validatePhone(phoneInput);
    }

    // Add event listeners for phone validation
    if (patientPhone) {
        patientPhone.addEventListener('input', function() {
            formatPhoneInput(this);
        });
        
        patientPhone.addEventListener('blur', function() {
            validatePhone(this);
        });
    }

    if (caregiverPhone) {
        caregiverPhone.addEventListener('input', function() {
            formatPhoneInput(this);
        });
        
        caregiverPhone.addEventListener('blur', function() {
            validatePhone(this);
        });
    }

    // Add click handlers for better UX
    patientTab.addEventListener('click', function() {
        // Clear caregiver form if switching
        if (caregiverForm.querySelector('form').checkValidity()) {
            // Form is valid, ask for confirmation
            if (!confirm('Are you sure you want to switch tabs? Your caregiver form data will be lost.')) {
                return false;
            }
        }
    });

    caregiverTab.addEventListener('click', function() {
        // Clear patient form if switching
        if (patientForm.querySelector('form').checkValidity()) {
            // Form is valid, ask for confirmation
            if (!confirm('Are you sure you want to switch tabs? Your patient form data will be lost.')) {
                return false;
            }
        }
    });

    // Form submission validation
    const patientFormElement = document.getElementById('patientForm');
    const caregiverFormElement = document.getElementById('caregiverForm');

    if (patientFormElement) {
        patientFormElement.addEventListener('submit', function(e) {
            if (!validatePhone(patientPhone)) {
                e.preventDefault();
                patientPhone.focus();
                return false;
            }
        });
    }

    if (caregiverFormElement) {
        caregiverFormElement.addEventListener('submit', function(e) {
            if (!validatePhone(caregiverPhone)) {
                e.preventDefault();
                caregiverPhone.focus();
                return false;
            }
        });
    }
});
</script>
@endsection

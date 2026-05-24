@extends('auth::layout')

@section('title', 'Edit Profile - MMHC CRM')
@section('page-title', 'Edit Profile')

@section('content')
<!-- Mobile App View for Edit Profile -->
<div class="mobile-app-container">
    <!-- App Header (Mobile Only) -->
    <div class="app-header-mobile d-md-none">
        <div class="app-header-content">
            <div class="app-header-left">
                <a href="{{ route('profile.index') }}" class="app-back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <div class="app-header-title">Edit Profile</div>
                    <div class="app-header-subtitle">Update your information</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="app-content">
        <!-- Desktop Header -->
        <div class="d-none d-md-block mb-4">
            <h2 class="text-primary">Edit Profile Information</h2>
        </div>
        
        @if($errors->any())
            <div class="app-alert app-alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($user->hasPendingMobileContactVerification())
            <div class="app-alert app-alert-warning">
                <i class="fas fa-shield-alt me-2"></i>
                <div class="w-100">
                    <div class="fw-semibold mb-1">Mobile verification pending</div>
                    <div class="small mb-2">
                        Latest OTP destination:
                        <strong>{{ $latestContactOtpDestination ?: ($pendingContactTarget ?: 'not sent yet') }}</strong>.
                        Older OTPs are ignored automatically.
                        @if(!empty($user->contact_update_otp_sent_at))
                            <span class="text-muted">Sent {{ $user->contact_update_otp_sent_at->diffForHumans() }}.</span>
                        @endif
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <form method="POST" action="{{ route('profile.verify-contact-otp') }}" class="d-flex gap-2 flex-wrap">
                            @csrf
                            <input type="text" name="otp_code" class="app-form-input" maxlength="6" placeholder="Enter 6-digit OTP" style="max-width: 180px;" required>
                            <button type="submit" class="app-btn-submit" style="padding: 10px 14px; flex: unset;">
                                <i class="fas fa-check-circle me-1"></i>Verify OTP
                            </button>
                        </form>
                        <form method="POST" action="{{ route('profile.resend-contact-otp') }}">
                            @csrf
                            <button type="submit" class="app-btn-secondary" style="padding: 10px 14px; flex: unset;">
                                <i class="fas fa-paper-plane me-1"></i>Resend OTP
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Profile Form Card -->
        <div class="app-form-container">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                
                <!-- Personal Information Section -->
                <div class="app-form-section">
                    <div class="app-section-header">
                        <div class="app-section-icon-header">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3 class="app-section-title">Personal Information</h3>
                    </div>
                    
                    <div class="app-form-group">
                        <label for="name" class="app-form-label">
                            <i class="fas fa-user me-2"></i>Full Name
                        </label>
                        <input type="text" 
                               class="app-form-input @error('name') app-input-error @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}" 
                               placeholder="Enter your full name"
                               required>
                        @error('name')
                            <div class="app-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="app-form-group">
                        <label for="phone" class="app-form-label">
                            <i class="fas fa-phone me-2"></i>Phone Number
                        </label>
                        <input type="tel" 
                               class="app-form-input @error('phone') app-input-error @enderror" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone', $phoneForInput ?? $user->phone) }}" 
                               placeholder="Enter 10-digit phone number"
                               pattern="[0-9]{10}"
                               maxlength="10"
                               required>
                        @error('phone')
                            <div class="app-form-error">{{ $message }}</div>
                        @enderror
                        @if($user->phone_verified_at && empty($user->pending_phone))
                            <div class="app-form-help" style="color: #047857;">
                                <i class="fas fa-check-circle me-1"></i>This mobile number was confirmed with OTP.
                            </div>
                        @endif
                    </div>
                    <div class="app-form-help mb-3">
                        <i class="fas fa-info-circle me-1"></i>Sign-in is via SMS OTP on your mobile. Changing your number requires OTP verification on the new number.
                    </div>

                    <div class="app-form-group">
                        <label for="address" class="app-form-label">
                            <i class="fas fa-map-marker-alt me-2"></i>Address
                        </label>
                        <textarea class="app-form-input @error('address') app-input-error @enderror" 
                                  id="address" 
                                  name="address" 
                                  rows="3"
                                  placeholder="Enter your complete address">{{ old('address', $user->address) }}</textarea>
                        @error('address')
                            <div class="app-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="app-form-group">
                        <label for="date_of_birth" class="app-form-label">
                            <i class="fas fa-birthday-cake me-2"></i>Date of Birth
                        </label>
                        <input type="date" 
                               class="app-form-input @error('date_of_birth') app-input-error @enderror" 
                               id="date_of_birth" 
                               name="date_of_birth" 
                               value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}">
                        @error('date_of_birth')
                            <div class="app-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @if($user->role === 'caregiver')
                <!-- Professional Information Section -->
                <div class="app-form-section">
                    <div class="app-section-header">
                        <div class="app-section-icon-header">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3 class="app-section-title">Professional Information</h3>
                    </div>
                    
                    <div class="app-form-group">
                        <label for="experience_years" class="app-form-label">
                            <i class="fas fa-calendar-alt me-2"></i>Years of Experience
                        </label>
                        <input type="number" 
                               class="app-form-input @error('experience_years') app-input-error @enderror" 
                               id="experience_years" 
                               name="experience_years" 
                               min="0" 
                               max="50" 
                               placeholder="Enter years of experience"
                               value="{{ old('experience_years', $profile->experience_years) }}">
                        @error('experience_years')
                            <div class="app-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="app-form-group">
                        <label for="specialization" class="app-form-label">
                            <i class="fas fa-graduation-cap me-2"></i>Specialization
                        </label>
                        <input type="text" 
                               class="app-form-input @error('specialization') app-input-error @enderror" 
                               id="specialization" 
                               name="specialization" 
                               placeholder="e.g., Elderly Care, Pediatric Care"
                               value="{{ old('specialization', $profile->specialization) }}">
                        @error('specialization')
                            <div class="app-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="app-form-group">
                        <label for="availability_status" class="app-form-label">
                            <i class="fas fa-toggle-on me-2"></i>Availability Status
                        </label>
                        <select class="app-form-select @error('availability_status') app-input-error @enderror" 
                                id="availability_status" 
                                name="availability_status">
                            <option value="available" {{ old('availability_status', $profile->availability_status) == 'available' ? 'selected' : '' }}>
                                Available
                            </option>
                            <option value="busy" {{ old('availability_status', $profile->availability_status) == 'busy' ? 'selected' : '' }}>
                                Busy
                            </option>
                            <option value="unavailable" {{ old('availability_status', $profile->availability_status) == 'unavailable' ? 'selected' : '' }}>
                                Unavailable
                            </option>
                        </select>
                        @error('availability_status')
                            <div class="app-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                @endif

                <!-- Bio Section -->
                <div class="app-form-section">
                    <div class="app-section-header">
                        <div class="app-section-icon-header">
                            <i class="fas fa-edit"></i>
                        </div>
                        <h3 class="app-section-title">About You</h3>
                    </div>
                    
                    <div class="app-form-group">
                        <label for="bio" class="app-form-label">
                            <i class="fas fa-user-edit me-2"></i>Bio
                        </label>
                        <textarea class="app-form-input @error('bio') app-input-error @enderror" 
                                  id="bio" 
                                  name="bio" 
                                  rows="4" 
                                  placeholder="Tell us about yourself, your experience, and what makes you special...">{{ old('bio', $profile->bio) }}</textarea>
                        <div class="app-form-help">Share a brief description about yourself</div>
                        @error('bio')
                            <div class="app-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="app-form-actions">
                    <a href="{{ route('profile.index') }}" class="app-btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Cancel
                    </a>
                    <button type="submit" class="app-btn-submit">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Actions Card -->
        <div class="app-quick-actions">
            <div class="app-section-header">
                <h3 class="app-section-title">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h3>
            </div>
            
            <div class="app-action-grid">
                <a href="{{ route('documents.index') }}" class="app-action-card">
                    <div class="app-action-icon upload">
                        <i class="fas fa-upload"></i>
                    </div>
                    <h5>Upload Documents</h5>
                    <p>Add certificates, ID proofs, and more</p>
                </a>
                
                <a href="{{ route('documents.index') }}" class="app-action-card">
                    <div class="app-action-icon view">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h5>View Documents</h5>
                    <p>Manage your uploaded files</p>
                </a>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
</div>

<style>
/* Mobile App Container */
.mobile-app-container {
    position: relative;
    min-height: 100vh;
    background: #f5f7fa;
    padding-bottom: 80px !important;
    margin-top: 0;
}

/* App Header Styles */
.app-header-mobile {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 12px 16px;
    padding-top: max(12px, env(safe-area-inset-top));
}

.app-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.app-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.app-back-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    font-size: 1.1rem;
}

.app-header-title {
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 1.2;
}

.app-header-subtitle {
    font-size: 0.8rem;
    opacity: 0.9;
}

/* App Content */
.app-content {
    padding: 16px;
    padding-bottom: 90px !important;
    margin-top: 0;
}

/* Reuse styles from create page */
.app-alert {
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 16px;
    display: flex;
    align-items: start;
    gap: 8px;
}

.app-alert-danger {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.app-alert-warning {
    background: #fef9c3;
    color: #854d0e;
    border: 1px solid #fde68a;
}

.app-form-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}

.app-btn-secondary {
    flex: 1;
    padding: 14px;
    border-radius: 12px;
    background: #f8f9fa;
    color: #6c757d;
    border: 1px solid #e9ecef;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
}

.app-btn-submit {
    flex: 1;
    padding: 14px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease;
}

.app-btn-submit:active {
    transform: scale(0.98);
}

/* Form Container */
.app-form-container {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

/* Section Header with Icon */
.app-section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f0f0f0;
}

.app-section-icon-header {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
}

.app-section-title {
    font-size: 1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

/* Form Elements */
.app-form-section {
    margin-bottom: 24px;
}

.app-form-group {
    margin-bottom: 20px;
}

.app-form-label {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.app-form-label i {
    color: #667eea;
    width: 20px;
}

.app-form-input,
.app-form-select {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 0.95rem;
    background: white;
    color: #2c3e50;
    transition: all 0.2s ease;
    font-family: inherit;
}

.app-form-input:focus,
.app-form-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: #fafbff;
}

.app-form-input::placeholder {
    color: #9ca3af;
}

.app-input-readonly {
    background: #f8f9fa !important;
    border-color: #e9ecef !important;
    color: #6c757d !important;
    cursor: not-allowed;
}

.app-input-error {
    border-color: #dc2626 !important;
    background: #fef2f2 !important;
}

.app-form-error {
    font-size: 0.8rem;
    color: #dc2626;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.app-form-help {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.app-form-help i {
    color: #667eea;
}

/* Quick Actions */
.app-quick-actions {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.app-action-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.app-action-card {
    background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
    border: 2px solid #e9ecef;
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
    display: block;
}

.app-action-card:active {
    transform: scale(0.97);
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.app-action-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 1.5rem;
    color: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.app-action-icon.upload {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-action-icon.view {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.app-action-card h5 {
    font-size: 0.95rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 6px 0;
}

.app-action-card p {
    font-size: 0.8rem;
    color: #6c757d;
    margin: 0;
    line-height: 1.4;
}

/* Desktop View */
@media (min-width: 768px) {
    .app-content {
        padding: 24px;
        padding-bottom: 24px;
    }
    
    .app-form-container {
        padding: 32px;
    }
    
    .app-action-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
}

/* Mobile Optimizations */
@media (max-width: 576px) {
    .app-form-container {
        padding: 16px;
    }
    
    .app-section-icon-header {
        width: 36px;
        height: 36px;
        font-size: 1rem;
    }
    
    .app-action-icon {
        width: 48px;
        height: 48px;
        font-size: 1.3rem;
    }
    
    .app-action-card h5 {
        font-size: 0.85rem;
    }
    
    .app-action-card p {
        font-size: 0.75rem;
    }
}
</style>
@endsection

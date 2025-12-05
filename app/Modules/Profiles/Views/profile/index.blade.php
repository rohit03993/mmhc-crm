@extends('auth::layout')

@section('title', 'My Profile - MMHC CRM')
@section('page-title', 'My Profile')

@section('content')
<!-- Mobile App View for Profile -->
<div class="mobile-app-container">
    <!-- App Header (Mobile Only) -->
    <div class="app-header-mobile d-md-none">
        <div class="app-header-content">
            <div class="app-header-left">
                <a href="{{ route('dashboard') }}" class="app-back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <div class="app-header-title">My Profile</div>
                    <div class="app-header-subtitle">{{ $user->unique_id }}</div>
                </div>
            </div>
            <div class="app-header-right">
                <a href="{{ route('profile.edit') }}" class="app-header-icon">
                    <i class="fas fa-edit"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="app-content">
        <!-- Profile Header Card -->
        <div class="app-profile-header">
            <div class="app-profile-avatar">
                @if($profile->avatar_path)
                    <img src="{{ Storage::url($profile->avatar_path) }}" alt="Profile">
                @else
                    <div class="app-avatar-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                @endif
            </div>
            <h3 class="app-profile-name">{{ $user->name }}</h3>
            <div class="app-profile-badges">
                <span class="app-badge app-badge-primary">{{ ucfirst($user->role) }}</span>
                <span class="app-badge app-badge-secondary">{{ $user->unique_id }}</span>
            </div>
            
            <div class="app-profile-completion">
                <div class="app-progress-bar">
                    <div class="app-progress-fill" style="width: {{ $profile->getCompletionPercentage() }}%"></div>
                </div>
                <small>Profile {{ $profile->getCompletionPercentage() }}% Complete</small>
            </div>
            
            <a href="{{ route('profile.edit') }}" class="app-btn-edit">
                <i class="fas fa-edit me-2"></i>Edit Profile
            </a>
        </div>

        <!-- Profile Details - Modern Card Grid -->
        <div class="app-detail-section">
            <div class="app-section-header">
                <div class="app-section-icon-header">
                    <i class="fas fa-user"></i>
                </div>
                <h3 class="app-section-title">Profile Information</h3>
            </div>
            
            <div class="app-info-grid">
                <!-- Full Name Card -->
                <div class="app-info-card">
                    <div class="app-info-icon name">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="app-info-content">
                        <div class="app-info-label">Full Name</div>
                        <div class="app-info-value">{{ $user->name }}</div>
                    </div>
                </div>
                
                <!-- Email Card -->
                <div class="app-info-card">
                    <div class="app-info-icon email">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="app-info-content">
                        <div class="app-info-label">Email</div>
                        <div class="app-info-value">{{ Str::limit($user->email, 25) }}</div>
                    </div>
                </div>
                
                <!-- Phone Card -->
                <div class="app-info-card">
                    <div class="app-info-icon phone">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="app-info-content">
                        <div class="app-info-label">Phone</div>
                        <div class="app-info-value">{{ $user->phone ?? 'Not provided' }}</div>
                    </div>
                </div>
                
                <!-- Date of Birth Card -->
                <div class="app-info-card">
                    <div class="app-info-icon dob">
                        <i class="fas fa-birthday-cake"></i>
                    </div>
                    <div class="app-info-content">
                        <div class="app-info-label">Date of Birth</div>
                        <div class="app-info-value">{{ $user->getFormattedDateOfBirth() }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Address Card - Full Width -->
            @if($user->address)
            <div class="app-info-card-full">
                <div class="app-info-icon-full address">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="app-info-content-full">
                    <div class="app-info-label">Address</div>
                    <div class="app-info-value">{{ $user->address }}</div>
                </div>
            </div>
            @endif
            
            <!-- Professional Information for Caregivers -->
            @if($user->role === 'caregiver' && $profile)
            <div class="app-info-grid">
                <div class="app-info-card">
                    <div class="app-info-icon professional">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="app-info-content">
                        <div class="app-info-label">Experience</div>
                        <div class="app-info-value">{{ $profile->experience_years ? $profile->experience_years . ' years' : 'Not provided' }}</div>
                    </div>
                </div>
                
                <div class="app-info-card">
                    <div class="app-info-icon specialization">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="app-info-content">
                        <div class="app-info-label">Specialization</div>
                        <div class="app-info-value">{{ $profile->specialization ?? 'Not provided' }}</div>
                    </div>
                </div>
            </div>
            
            <div class="app-info-card-full">
                <div class="app-info-icon-full availability">
                    <i class="fas fa-toggle-on"></i>
                </div>
                <div class="app-info-content-full">
                    <div class="app-info-label">Availability Status</div>
                    <div class="app-info-value">
                        <span class="app-badge app-badge-{{ $profile->availability_status === 'available' ? 'success' : ($profile->availability_status === 'busy' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($profile->availability_status) }}
                        </span>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Bio Card - Full Width -->
            @if($profile && $profile->bio)
            <div class="app-info-card-full bio">
                <div class="app-info-icon-full bio-icon">
                    <i class="fas fa-quote-left"></i>
                </div>
                <div class="app-info-content-full">
                    <div class="app-info-label">About Me</div>
                    <div class="app-info-value-bio">{{ $profile->bio }}</div>
                </div>
            </div>
            @endif
        </div>

        <!-- Quick Upload Section -->
        <div class="app-detail-section">
            <div class="app-section-header">
                <h3 class="app-section-title">
                    <i class="fas fa-upload me-2"></i>Upload Document
                </h3>
            </div>
            
            <form method="POST" action="{{ route('documents.upload') }}" enctype="multipart/form-data" class="app-upload-form">
                @csrf
                
                <div class="app-form-group">
                    <label for="quick_document_type" class="app-form-label">Document Type</label>
                    <select class="app-form-select" id="quick_document_type" name="document_type" required>
                        <option value="">Select Type</option>
                        @php
                            $allowedTypes = auth()->user()->isPatient() ? [
                                'medical_report' => 'Medical Report',
                                'aadhaar_card' => 'Aadhaar Card',
                                'past_medical_history' => 'Past Medical History',
                                'prescription' => 'Prescription',
                                'lab_report' => 'Lab Report',
                                'insurance_card' => 'Insurance Card',
                                'other' => 'Other',
                            ] : [
                                'certificate' => 'Certificate',
                                'id_proof' => 'ID Proof',
                                'medical_license' => 'Medical License',
                                'insurance' => 'Insurance',
                                'other' => 'Other',
                            ];
                        @endphp
                        @foreach($allowedTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="app-form-group">
                    <label for="quick_document_name" class="app-form-label">Document Name</label>
                    <input type="text" 
                           class="app-form-input" 
                           id="quick_document_name" 
                           name="document_name" 
                           placeholder="{{ auth()->user()->isPatient() ? 'e.g., Blood Test Report' : 'e.g., Nursing Certificate' }}"
                           required>
                </div>
                
                <div class="app-form-group">
                    <label for="quick_document_file" class="app-form-label">Choose File</label>
                    <div class="app-file-upload">
                        <input type="file" 
                               class="app-file-input" 
                               id="quick_document_file" 
                               name="document_file" 
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                               required>
                        <label for="quick_document_file" class="app-file-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Tap to select file</span>
                            <small>Max 10MB - PDF, JPG, PNG, DOC</small>
                        </label>
                    </div>
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
                
                <button type="submit" class="app-btn-upload">
                    <i class="fas fa-upload me-2"></i>Upload Document
                </button>
            </form>
        </div>

        <!-- Documents Section -->
        <div class="app-detail-section">
            <div class="app-section-header">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <h3 class="app-section-title">
                        <i class="fas fa-file-alt me-2"></i>My Documents
                    </h3>
                    <a href="{{ route('documents.index') }}" class="app-link-btn">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            
            <div class="app-documents-grid">
                <div class="app-document-card">
                    <div class="app-document-icon primary">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h6>{{ auth()->user()->isPatient() ? 'Medical Reports' : 'Certificates' }}</h6>
                    <span class="app-document-count">{{ $user->documents()->whereIn('document_type', auth()->user()->isPatient() ? ['medical_report'] : ['certificate'])->count() }}</span>
                </div>
                <div class="app-document-card">
                    <div class="app-document-icon success">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <h6>{{ auth()->user()->isPatient() ? 'Aadhaar Card' : 'ID Proofs' }}</h6>
                    <span class="app-document-count">{{ $user->documents()->whereIn('document_type', auth()->user()->isPatient() ? ['aadhaar_card'] : ['id_proof'])->count() }}</span>
                </div>
                <div class="app-document-card">
                    <div class="app-document-icon info">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h6>{{ auth()->user()->isPatient() ? 'Prescriptions' : 'Medical License' }}</h6>
                    <span class="app-document-count">{{ $user->documents()->whereIn('document_type', auth()->user()->isPatient() ? ['prescription'] : ['medical_license'])->count() }}</span>
                </div>
                <div class="app-document-card">
                    <div class="app-document-icon warning">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h6>{{ auth()->user()->isPatient() ? 'Insurance Card' : 'Insurance' }}</h6>
                    <span class="app-document-count">{{ $user->documents()->whereIn('document_type', auth()->user()->isPatient() ? ['insurance_card'] : ['insurance'])->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    @include('auth::components.bottom-nav')
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

.app-header-right {
    display: flex;
    gap: 12px;
}

.app-header-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    font-size: 1.2rem;
}

/* App Content */
.app-content {
    padding: 16px;
    padding-bottom: 90px !important;
    margin-top: 0;
}

/* Profile Header */
.app-profile-header {
    background: white;
    border-radius: 16px;
    padding: 24px;
    text-align: center;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.app-profile-avatar {
    width: 100px;
    height: 100px;
    margin: 0 auto 16px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid #667eea;
}

.app-profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.app-avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2.5rem;
}

.app-profile-name {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 12px 0;
}

.app-profile-badges {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-bottom: 16px;
}

.app-profile-completion {
    margin-bottom: 16px;
}

.app-progress-bar {
    width: 100%;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 8px;
}

.app-progress-fill {
    height: 100%;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    transition: width 0.3s ease;
}

.app-btn-edit {
    display: inline-flex;
    align-items: center;
    padding: 12px 24px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
}

.app-professional-section,
.app-bio-section {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #e9ecef;
}

.app-subsection-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 16px 0;
}

.app-bio-text {
    font-size: 0.9rem;
    color: #6c757d;
    line-height: 1.6;
    margin: 0;
}

.app-link-btn {
    font-size: 0.85rem;
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
}

/* Modern Profile Information Grid */
.app-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 12px;
}

.app-info-card {
    background: white;
    border-radius: 16px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
}

.app-info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: #e0e0e0;
}

.app-info-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.app-info-icon.name {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-info-icon.email {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.app-info-icon.phone {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.app-info-icon.dob {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.app-info-icon.professional {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.app-info-icon.specialization {
    background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
}

.app-info-icon-full.availability {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
}

.app-info-content {
    flex: 1;
    min-width: 0;
}

.app-info-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.app-info-value {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    word-break: break-word;
    line-height: 1.4;
}

/* Full Width Cards */
.app-info-card-full {
    background: white;
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
    margin-bottom: 12px;
}

.app-info-card-full:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: #e0e0e0;
}

.app-info-card-full.bio {
    background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
    border: 2px solid #e9ecef;
}

.app-info-icon-full {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.app-info-icon-full.address {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.app-info-icon-full.bio-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-info-content-full {
    flex: 1;
    min-width: 0;
}

.app-info-value-bio {
    font-size: 0.9rem;
    color: #2c3e50;
    line-height: 1.6;
    margin-top: 4px;
    font-style: italic;
}

.app-documents-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.app-document-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
}

.app-document-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 1.5rem;
    color: white;
}

.app-document-icon.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-document-icon.success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.app-document-icon.info {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.app-document-icon.warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.app-document-card h6 {
    font-size: 0.85rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0 0 8px 0;
}

.app-document-count {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    background: white;
    color: #667eea;
    font-weight: 700;
    font-size: 0.9rem;
}

/* Reuse existing styles from show page */
.app-detail-section,
.app-section-header,
.app-section-title,
.app-detail-list,
.app-detail-row,
.app-detail-label,
.app-detail-value,
.app-detail-grid,
.app-detail-item,
.app-badge {
    /* Styles already defined in show.blade.php */
}

/* Upload Form Styles */
.app-upload-form {
    margin-top: 16px;
}

.app-form-group {
    margin-bottom: 16px;
}

.app-form-label {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.app-form-select,
.app-form-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 0.95rem;
    background: white;
    color: #2c3e50;
    transition: all 0.2s ease;
    font-family: inherit;
}

.app-form-select:focus,
.app-form-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.app-form-input::placeholder {
    color: #9ca3af;
}

.app-file-upload {
    position: relative;
}

.app-file-input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.app-file-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 32px 16px;
    border: 2px dashed #e9ecef;
    border-radius: 12px;
    background: #fafbff;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}

.app-file-label:hover,
.app-file-label:active {
    border-color: #667eea;
    background: #f0f4ff;
}

.app-file-label i {
    font-size: 2rem;
    color: #667eea;
    margin-bottom: 8px;
}

.app-file-label span {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 4px;
}

.app-file-label small {
    font-size: 0.75rem;
    color: #6c757d;
}

.app-file-input:focus + .app-file-label {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.app-btn-upload {
    width: 100%;
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
    margin-top: 12px;
}

.app-btn-upload:active {
    transform: scale(0.98);
}

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

.app-alert ul {
    margin: 0;
    padding-left: 20px;
}

.app-link-btn {
    display: flex;
    align-items: center;
}

@media (min-width: 768px) {
    .app-documents-grid {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .app-info-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
    
    .app-info-card {
        padding: 20px;
        flex-direction: column;
        text-align: center;
    }
    
    .app-info-icon {
        width: 56px;
        height: 56px;
        font-size: 1.4rem;
        margin-bottom: 8px;
    }
    
    .app-info-content {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .app-info-grid {
        gap: 10px;
    }
    
    .app-info-card {
        padding: 14px;
        gap: 10px;
    }
    
    .app-info-icon {
        width: 44px;
        height: 44px;
        font-size: 1.1rem;
    }
    
    .app-info-value {
        font-size: 0.85rem;
    }
    
    .app-info-card-full {
        padding: 16px;
        gap: 12px;
    }
    
    .app-info-icon-full {
        width: 48px;
        height: 48px;
        font-size: 1.2rem;
    }
}
</style>
@endsection

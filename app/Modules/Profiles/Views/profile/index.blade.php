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
        <div class="profile-page-root">

        @if(session('success'))
            <div class="profile-flash profile-flash-success" role="status">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="profile-flash profile-flash-danger" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

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
            @if($user->isStaff())
                @if($user->hasVerifiedPhone())
                    <a href="{{ route('profile.id-card') }}" class="app-btn-edit mt-2" style="background: linear-gradient(135deg, #312e81, #1d4ed8);">
                        <i class="fas fa-id-card me-2"></i>View ID Card
                    </a>
                @else
                    <a href="{{ route('profile.edit') }}" class="app-btn-edit mt-2" style="background: #94a3b8;">
                        <i class="fas fa-mobile-alt me-2"></i>Verify mobile for ID card
                    </a>
                @endif
            @endif
        </div>

        @if($subscriptionSummary)
            <div class="profile-subscription-banner">
                <div class="profile-subscription-banner-icon">
                    <i class="fas fa-id-card-alt"></i>
                </div>
                <div class="profile-subscription-banner-body">
                    @if($subscriptionSummary['active'])
                        <div class="profile-subscription-title">Active healthcare plan</div>
                        <div class="profile-subscription-meta">
                            {{ $subscriptionSummary['active']->plan->name ?? 'Plan' }}
                            <span class="text-muted">·</span>
                            Valid through {{ $subscriptionSummary['active']->end_date->format('M j, Y') }}
                        </div>
                    @else
                        <div class="profile-subscription-title">No active plan right now</div>
                        <div class="profile-subscription-meta text-muted">
                            @if(($subscriptionSummary['total_records'] ?? 0) > 0)
                                You have past subscription history — renew or pick a plan to stay covered.
                            @else
                                Choose a plan when you are ready to unlock benefits.
                            @endif
                        </div>
                    @endif
                </div>
                <a href="{{ route('subscriptions.index') }}" class="profile-subscription-cta">
                    {{ $subscriptionSummary['active'] ? 'Manage' : 'View plans' }}
                    <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        @endif

        <!-- Nursing Warrior Badge (nurse & caregiver only) -->
        @if($user->isStaff())
        <div class="app-detail-section app-nursing-warrior-badge">
            <div class="app-section-header">
                <div class="app-section-icon-header">
                    <i class="fas fa-award"></i>
                </div>
                <h3 class="app-section-title">Earned Badge</h3>
            </div>
            <div class="app-warrior-badge-card">
                <div class="app-warrior-badge-image-wrap">
                    <img src="{{ asset('images/nursing-warrior-badge.png') }}" alt="Nursing Warrior Badge" class="app-warrior-badge-img">
                </div>
                <p class="app-warrior-badge-label mb-0">You have earned this <strong>Nursing Warrior</strong> badge</p>
                <p class="app-warrior-badge-sublabel">MeD Miracle Health Care – {{ $user->role === 'nurse' ? 'Nurse Warrior' : 'Caregiver Warrior' }}</p>
            </div>
        </div>
        @endif

        <!-- Profile Details - Modern Card Grid -->
        <div class="app-detail-section">
            <div class="profile-section-heading">
                <div class="profile-section-heading-row">
                    <div class="profile-section-title-block">
                        <span class="profile-section-icon-badge" aria-hidden="true">
                            <i class="fas fa-id-card"></i>
                        </span>
                        <div class="profile-section-title-text">
                            <h2 class="profile-section-h2">Profile information</h2>
                            <p class="profile-section-sub">Name, mobile, date of birth and address</p>
                        </div>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="profile-section-cta">
                        <i class="fas fa-edit me-2"></i>
                        <span class="profile-section-cta-label">Update profile &amp; mobile</span>
                    </a>
                </div>
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
                
                <!-- Phone Card -->
                <div class="app-info-card">
                    <div class="app-info-icon phone">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="app-info-content">
                        <div class="app-info-label">Phone</div>
                        <div class="app-info-value app-info-value-multiline">{{ $user->displayPhone() }}</div>
                        @if($pendingDisplay = $user->displayPendingPhone())
                            <span class="profile-verify-chip profile-verify-chip--pending" title="Enter the OTP on Edit Profile to activate this number">
                                <i class="fas fa-clock" aria-hidden="true"></i>
                                Pending verification: {{ $pendingDisplay }}
                            </span>
                            <a href="{{ route('profile.edit') }}" class="profile-meta-edit-link">Complete OTP verification</a>
                        @elseif(! empty($user->phone) && $user->phone_verified_at)
                            @if($user->phone_verified_source === 'admin')
                                <span class="profile-verify-chip profile-verify-chip--admin" title="{{ $user->phoneVerificationUserLabel() }}"><i class="fas fa-user-shield" aria-hidden="true"></i> {{ $user->phoneVerificationUserLabel() }}</span>
                            @else
                                <span class="profile-verify-chip" title="{{ $user->phoneVerificationUserLabel() }}"><i class="fas fa-check-circle" aria-hidden="true"></i> {{ $user->phoneVerificationUserLabel() }}</span>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="profile-meta-edit-link">Edit in profile settings</a>
                        @else
                            @if($user->isStaff())
                                <span class="profile-verify-chip profile-verify-chip--warn"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Mobile not verified — payouts on hold</span>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="profile-meta-edit-link">Edit in profile settings</a>
                        @endif
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

        <!-- Upload documents -->
        <div class="app-detail-section profile-upload-section">
            <div class="profile-upload-card">
                <div class="profile-upload-card-head">
                    <div>
                        <h3 class="profile-upload-heading">
                            <span class="profile-upload-heading-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                            Document uploads
                        </h3>
                        <p class="profile-upload-lead mb-0">Add PDF, JPG, PNG or Word files (max 10&nbsp;MB). Stored securely on your account.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('documents.upload') }}" enctype="multipart/form-data" class="profile-upload-form" id="profileQuickUploadForm">
                    @csrf

                    <div class="profile-upload-grid">
                        <div class="profile-upload-field">
                            <label for="quick_document_type" class="profile-field-label">Type</label>
                            <select class="profile-field-control" id="quick_document_type" name="document_type" required>
                                <option value="" disabled {{ old('document_type') ? '' : 'selected' }}>Choose category…</option>
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
                                    <option value="{{ $value }}" {{ old('document_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="profile-upload-field profile-upload-field-grow">
                            <label for="quick_document_name" class="profile-field-label">Display name</label>
                            <input type="text"
                                   class="profile-field-control"
                                   id="quick_document_name"
                                   name="document_name"
                                   value="{{ old('document_name') }}"
                                   placeholder="{{ auth()->user()->isPatient() ? 'e.g. March 2026 blood work' : 'e.g. Nursing diploma 2024' }}"
                                   autocomplete="off"
                                   required>
                        </div>
                    </div>

                    <div class="profile-dropzone-wrap">
                        <label class="profile-field-label" for="quick_document_file">File</label>
                        <div class="profile-dropzone">
                            <input type="file"
                                   class="profile-file-native"
                                   id="quick_document_file"
                                   name="document_file"
                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,application/pdf,image/*"
                                   required>
                            <label for="quick_document_file" class="profile-dropzone-ui" id="profileDropzoneLabel">
                                <i class="fas fa-file-medical profile-dropzone-icon"></i>
                                <span class="profile-dropzone-title" id="profileDropzoneTitle">Drop a file here or browse</span>
                                <span class="profile-dropzone-hint" id="profileDropzoneHint">10MB max · PDF, JPG, PNG, DOC/DOCX</span>
                            </label>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="profile-flash profile-flash-danger profile-flash-inline" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="profile-upload-actions">
                        <button type="submit" class="profile-btn-primary">
                            <i class="fas fa-upload me-2"></i>Upload
                        </button>
                        <a href="{{ route('documents.index') }}" class="profile-btn-ghost">All documents</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Documents Section -->
        <div class="app-detail-section">
            <div class="profile-section-heading profile-section-heading--documents">
                <div class="profile-section-heading-row">
                    <div class="profile-section-title-block">
                        <span class="profile-section-icon-badge profile-section-icon-badge--teal" aria-hidden="true">
                            <i class="fas fa-folder-open"></i>
                        </span>
                        <div class="profile-section-title-text">
                            <h2 class="profile-section-h2">My documents</h2>
                            <p class="profile-section-sub">Files by category · open <strong>View all</strong> for the full library</p>
                        </div>
                    </div>
                    <a href="{{ route('documents.index') }}" class="profile-section-cta profile-section-cta--outline">
                        View all
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            
            <div class="app-documents-grid">
                @if(auth()->user()->isPatient())
                    <div class="app-document-card">
                        <div class="app-document-icon primary">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <h6>Reports &amp; history</h6>
                        <span class="app-document-count">{{ $documentCategoryCounts['medical_group'] ?? 0 }}</span>
                    </div>
                    <div class="app-document-card">
                        <div class="app-document-icon success">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h6>Aadhaar</h6>
                        <span class="app-document-count">{{ $documentCategoryCounts['aadhaar'] ?? 0 }}</span>
                    </div>
                    <div class="app-document-card">
                        <div class="app-document-icon info">
                            <i class="fas fa-notes-medical"></i>
                        </div>
                        <h6>Prescriptions</h6>
                        <span class="app-document-count">{{ $documentCategoryCounts['prescription'] ?? 0 }}</span>
                    </div>
                    <div class="app-document-card">
                        <div class="app-document-icon warning">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h6>Insurance</h6>
                        <span class="app-document-count">{{ $documentCategoryCounts['insurance'] ?? 0 }}</span>
                    </div>
                @else
                    <div class="app-document-card">
                        <div class="app-document-icon primary">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h6>Certificates</h6>
                        <span class="app-document-count">{{ $documentCategoryCounts['certificate'] ?? 0 }}</span>
                    </div>
                    <div class="app-document-card">
                        <div class="app-document-icon success">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h6>ID proofs</h6>
                        <span class="app-document-count">{{ $documentCategoryCounts['id_proof'] ?? 0 }}</span>
                    </div>
                    <div class="app-document-card">
                        <div class="app-document-icon info">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <h6>Medical license</h6>
                        <span class="app-document-count">{{ $documentCategoryCounts['medical_license'] ?? 0 }}</span>
                    </div>
                    <div class="app-document-card">
                        <div class="app-document-icon warning">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h6>Insurance</h6>
                        <span class="app-document-count">{{ $documentCategoryCounts['insurance'] ?? 0 }}</span>
                    </div>
                @endif
            </div>
        </div>

        </div>{{-- /.profile-page-root --}}
    </div>

    <!-- Bottom Navigation -->
    @include('auth::components.bottom-nav')
</div>

<style>
/* Profile page shell (desktop) */
.profile-page-root {
    max-width: 920px;
    margin-left: auto;
    margin-right: auto;
}

/* Section headings: Profile information, My documents */
.profile-section-heading {
    margin-bottom: 18px;
    padding-bottom: 4px;
}
.profile-section-heading-row {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 14px;
}
@media (min-width: 576px) {
    .profile-section-heading-row {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
}
.profile-section-title-block {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    min-width: 0;
    flex: 1;
}
.profile-section-icon-badge {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
}
.profile-section-icon-badge--teal {
    background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
    box-shadow: 0 4px 14px rgba(13, 148, 136, 0.3);
}
.profile-section-title-text {
    min-width: 0;
}
.profile-section-h2 {
    font-size: 1.2rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
    margin: 0 0 4px 0;
    line-height: 1.25;
}
.profile-section-sub {
    font-size: 0.88rem;
    color: #64748b;
    margin: 0;
    line-height: 1.45;
    max-width: 36rem;
}
.profile-section-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    padding: 11px 18px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff !important;
    font-weight: 700;
    font-size: 0.88rem;
    text-decoration: none !important;
    box-shadow: 0 4px 14px rgba(102, 126, 234, 0.35);
    white-space: nowrap;
    border: none;
}
.profile-section-cta:hover {
    filter: brightness(1.05);
    color: #fff !important;
}
.profile-section-cta-label {
    display: inline;
}
@media (max-width: 575px) {
    .profile-section-cta {
        width: 100%;
        white-space: normal;
        text-align: center;
    }
}
.profile-section-cta--outline {
    background: #fff;
    color: #4f46e5 !important;
    border: 2px solid rgba(79, 70, 229, 0.35);
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
}
.profile-section-cta--outline:hover {
    background: #f5f3ff;
    color: #4338ca !important;
}
.app-info-value-multiline {
    word-break: break-word;
    overflow-wrap: anywhere;
    hyphens: auto;
    line-height: 1.35;
}
.profile-meta-edit-link {
    display: inline-block;
    margin-top: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #4f46e5;
    text-decoration: none;
}
.profile-meta-edit-link:hover {
    text-decoration: underline;
    color: #4338ca;
}

.profile-verify-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 8px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #047857;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
}
.profile-verify-chip i {
    font-size: 0.72rem;
}
.profile-verify-chip--warn {
    color: #92400e;
    background: #fffbeb;
    border-color: #fcd34d;
}
.profile-verify-chip--pending {
    color: #1e40af;
    background: #eff6ff;
    border-color: #93c5fd;
}

.profile-flash {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 14px;
    font-size: 0.9rem;
    font-weight: 500;
}
.profile-flash i { margin-top: 2px; }
.profile-flash-success {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}
.profile-flash-danger {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
.profile-flash-inline { margin-top: 12px; margin-bottom: 0; }

.profile-subscription-banner {
    display: flex;
    align-items: center;
    gap: 14px;
    background: linear-gradient(135deg, #f0f9ff 0%, #eef2ff 100%);
    border: 1px solid rgba(99, 102, 241, 0.25);
    border-radius: 16px;
    padding: 16px 18px;
    margin-bottom: 16px;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
}
.profile-subscription-banner-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.profile-subscription-banner-body { flex: 1; min-width: 0; }
.profile-subscription-title {
    font-weight: 700;
    color: #0f172a;
    font-size: 0.95rem;
    margin-bottom: 4px;
}
.profile-subscription-meta { font-size: 0.85rem; color: #475569; line-height: 1.4; }
.profile-subscription-cta {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    padding: 8px 14px;
    border-radius: 10px;
    background: #fff;
    color: #4f46e5;
    font-weight: 600;
    font-size: 0.85rem;
    text-decoration: none;
    border: 1px solid rgba(79, 70, 229, 0.35);
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.06);
}
.profile-subscription-cta:hover { color: #4338ca; background: #fafafa; }

.profile-upload-section { margin-top: 8px; }
.profile-upload-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, 0.35);
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.07);
    overflow: hidden;
}
.profile-upload-card-head {
    padding: 18px 20px 12px;
    border-bottom: 1px solid #f1f5f9;
    background: linear-gradient(180deg, #fafbff 0%, #fff 100%);
}
.profile-upload-heading {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 6px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.profile-upload-heading-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}
.profile-upload-lead {
    font-size: 0.85rem;
    color: #64748b;
    line-height: 1.45;
    padding-left: 46px;
}
.profile-upload-form { padding: 18px 20px 20px; }
.profile-upload-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
    margin-bottom: 16px;
}
@media (min-width: 640px) {
    .profile-upload-grid {
        grid-template-columns: minmax(160px, 38%) 1fr;
    }
}
.profile-upload-field-grow { min-width: 0; }
.profile-field-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
    margin-bottom: 6px;
}
.profile-field-control {
    width: 100%;
    padding: 11px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    font-size: 0.92rem;
    color: #0f172a;
    background: #fff;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.profile-field-control:focus {
    outline: none;
    border-color: #818cf8;
    box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.2);
}
.profile-dropzone-wrap { margin-bottom: 16px; }
.profile-dropzone { position: relative; }
.profile-file-native {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    inset: 0;
    cursor: pointer;
    z-index: 2;
}
.profile-dropzone-ui {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 22px 16px;
    min-height: 120px;
    border: 2px dashed #cbd5e1;
    border-radius: 14px;
    background: #f8fafc;
    transition: border-color 0.15s, background 0.15s;
    position: relative;
    z-index: 1;
    pointer-events: none;
}
.profile-dropzone:hover .profile-dropzone-ui,
.profile-dropzone:focus-within .profile-dropzone-ui {
    border-color: #818cf8;
    background: #f5f3ff;
}
.profile-dropzone-icon {
    font-size: 1.75rem;
    color: #6366f1;
    margin-bottom: 8px;
}
.profile-dropzone-title {
    font-weight: 600;
    color: #0f172a;
    font-size: 0.9rem;
}
.profile-dropzone-hint {
    font-size: 0.78rem;
    color: #64748b;
    margin-top: 4px;
}
.profile-upload-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    margin-top: 4px;
}
.profile-btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 22px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    font-weight: 600;
    font-size: 0.92rem;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(102, 126, 234, 0.35);
}
.profile-btn-primary:active { transform: scale(0.98); }
.profile-btn-ghost {
    display: inline-flex;
    align-items: center;
    padding: 10px 16px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.88rem;
    color: #475569;
    background: #f1f5f9;
    text-decoration: none;
    border: 1px solid #e2e8f0;
}
.profile-btn-ghost:hover { color: #334155; background: #e2e8f0; }

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

/* Nursing Warrior Badge Section */
.app-nursing-warrior-badge .app-section-icon-header {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.app-warrior-badge-card {
    background: linear-gradient(160deg, rgba(255,255,255,0.98) 0%, rgba(248,249,255,0.98) 100%);
    border-radius: 20px;
    padding: 24px;
    text-align: center;
    border: 1px solid rgba(102, 126, 234, 0.2);
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.08);
}

.app-warrior-badge-image-wrap {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    border-radius: 16px;
    padding: 16px;
    display: inline-block;
    margin-bottom: 16px;
}

.app-warrior-badge-img {
    max-height: 140px;
    width: auto;
    display: block;
}

.app-warrior-badge-label {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
}

.app-warrior-badge-sublabel {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 6px;
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

.app-inline-edit-link {
    display: inline-block;
    margin-top: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #4f46e5;
    text-decoration: none;
}

.app-inline-edit-link:hover {
    text-decoration: underline;
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
    background: #fff;
    border: 1px solid #f1f5f9;
    border-radius: 14px;
    padding: 16px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    transition: transform 0.15s, box-shadow 0.15s;
}
.app-document-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
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

@section('scripts')
<script>
(function () {
    var input = document.getElementById('quick_document_file');
    var titleEl = document.getElementById('profileDropzoneTitle');
    var hintEl = document.getElementById('profileDropzoneHint');
    if (!input || !titleEl || !hintEl) return;
    input.addEventListener('change', function () {
        if (input.files && input.files[0]) {
            var f = input.files[0];
            titleEl.textContent = f.name;
            var mb = (f.size / (1024 * 1024)).toFixed(2);
            hintEl.textContent = mb + ' MB · ready to upload';
        } else {
            titleEl.textContent = 'Drop a file here or browse';
            hintEl.textContent = '10MB max · PDF, JPG, PNG, DOC/DOCX';
        }
    });
})();
</script>
@endsection

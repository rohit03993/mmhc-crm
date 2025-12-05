@extends('auth::layout')

@section('title', 'Service Request Details - MMHC CRM')

@section('head')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
@endsection

@section('content')
<!-- Mobile App View for Service Details -->
<div class="mobile-app-container">
    <!-- App Header (Mobile Only) -->
    <div class="app-header-mobile d-md-none">
        <div class="app-header-content">
            <div class="app-header-left">
                <a href="{{ route('services.my-requests') }}" class="app-back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <div class="app-header-title">Service Details</div>
                    <div class="app-header-subtitle">Request #{{ $serviceRequest->id }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="app-content">
        <!-- Desktop Header -->
        <div class="d-none d-md-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="text-primary">Service Request Details</h2>
                <p class="text-muted">View your service request information</p>
            </div>
            <a href="{{ route('services.my-requests') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Requests
            </a>
        </div>

        <!-- Status Card -->
        <div class="app-status-card status-{{ $serviceRequest->status }}">
            <div class="app-status-header">
                <div class="app-status-icon">
                    <i class="fas fa-{{ $serviceRequest->status === 'pending' ? 'clock' : ($serviceRequest->status === 'assigned' ? 'user-check' : ($serviceRequest->status === 'in_progress' ? 'play-circle' : 'check-circle')) }}"></i>
                </div>
                <div>
                    <h3 class="app-status-title">{{ $serviceRequest->serviceType->name }}</h3>
                    <span class="app-status-badge">{{ ucfirst(str_replace('_', ' ', $serviceRequest->status)) }}</span>
                </div>
            </div>
            <div class="app-status-amount">
                <span>Total Amount</span>
                <span>₹{{ number_format($serviceRequest->total_amount) }}</span>
            </div>
        </div>

        <!-- Service Information -->
        <div class="app-detail-section">
            <div class="app-section-header">
                <h3 class="app-section-title">
                    <i class="fas fa-info-circle me-2"></i>Service Information
                </h3>
            </div>
            
            <div class="app-detail-grid">
                <div class="app-detail-item">
                    <div class="app-detail-label">Duration</div>
                    <div class="app-detail-value">{{ $serviceRequest->duration_days }} days</div>
                </div>
                <div class="app-detail-item">
                    <div class="app-detail-label">Start Date</div>
                    <div class="app-detail-value">{{ $serviceRequest->start_date->format('M d, Y') }}</div>
                </div>
                <div class="app-detail-item">
                    <div class="app-detail-label">End Date</div>
                    <div class="app-detail-value">{{ $serviceRequest->end_date->format('M d, Y') }}</div>
                </div>
                <div class="app-detail-item">
                    <div class="app-detail-label">Staff Type</div>
                    <div class="app-detail-value">{{ ucfirst($serviceRequest->preferred_staff_type) }}</div>
                </div>
            </div>
        </div>

        <!-- Location & Contact -->
        <div class="app-detail-section">
            <div class="app-section-header">
                <h3 class="app-section-title">
                    <i class="fas fa-map-marker-alt me-2"></i>Location & Contact
                </h3>
            </div>
            
            <div class="app-detail-list">
                <div class="app-detail-row">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <div class="app-detail-label">Service Location</div>
                        <div class="app-detail-value">{{ $serviceRequest->location }}</div>
                    </div>
                </div>
                <div class="app-detail-row">
                    <i class="fas fa-user"></i>
                    <div>
                        <div class="app-detail-label">Contact Person</div>
                        <div class="app-detail-value">{{ $serviceRequest->contact_person }}</div>
                    </div>
                </div>
                <div class="app-detail-row">
                    <i class="fas fa-phone"></i>
                    <div>
                        <div class="app-detail-label">Contact Phone</div>
                        <div class="app-detail-value">{{ $serviceRequest->contact_phone }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Staff -->
        @if($serviceRequest->assignedStaff)
        <div class="app-staff-card">
            <div class="app-section-header">
                <h3 class="app-section-title">
                    <i class="fas fa-user-md me-2"></i>Assigned Staff
                </h3>
            </div>
            
            <div class="app-staff-info">
                <div class="app-staff-avatar {{ $serviceRequest->assignedStaff->isNurse() ? 'nurse' : 'caregiver' }}">
                    <i class="fas fa-user-{{ $serviceRequest->assignedStaff->isNurse() ? 'nurse' : 'md' }}"></i>
                </div>
                <div class="app-staff-details">
                    <h4 class="app-staff-name">{{ $serviceRequest->assignedStaff->name }}</h4>
                    <div class="app-staff-badges">
                        <span class="app-badge app-badge-{{ $serviceRequest->assignedStaff->isNurse() ? 'primary' : 'success' }}">
                            {{ ucfirst($serviceRequest->assignedStaff->role) }}
                        </span>
                        <span class="app-badge app-badge-secondary">{{ $serviceRequest->assignedStaff->unique_id }}</span>
                    </div>
                    @if($serviceRequest->assignedStaff->qualification)
                    <div class="app-staff-qual">
                        <i class="fas fa-graduation-cap"></i>
                        <span>{{ $serviceRequest->assignedStaff->qualification }}</span>
                    </div>
                    @endif
                    @if($serviceRequest->assignedStaff->experience)
                    <div class="app-staff-exp">
                        <i class="fas fa-briefcase"></i>
                        <span>{{ $serviceRequest->assignedStaff->experience }} years exp.</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Notes & Requirements -->
        @if($serviceRequest->notes || $serviceRequest->special_requirements)
        <div class="app-detail-section">
            <div class="app-section-header">
                <h3 class="app-section-title">
                    <i class="fas fa-sticky-note me-2"></i>Additional Information
                </h3>
            </div>
            
            @if($serviceRequest->notes)
            <div class="app-note-item">
                <div class="app-note-label">Notes</div>
                <div class="app-note-content">{{ $serviceRequest->notes }}</div>
            </div>
            @endif
            
            @if($serviceRequest->special_requirements)
            <div class="app-note-item">
                <div class="app-note-label">Special Requirements</div>
                <div class="app-note-content">{{ $serviceRequest->special_requirements }}</div>
            </div>
            @endif
        </div>
        @endif

        <!-- Timeline -->
        <div class="app-timeline-section">
            <div class="app-section-header">
                <h3 class="app-section-title">
                    <i class="fas fa-history me-2"></i>Request Timeline
                </h3>
            </div>
            
            <div class="app-timeline">
                <div class="app-timeline-item">
                    <div class="app-timeline-marker primary"></div>
                    <div class="app-timeline-content">
                        <h6>Request Submitted</h6>
                        <small>{{ $serviceRequest->created_at->format('M d, Y g:i A') }}</small>
                    </div>
                </div>
                
                @if($serviceRequest->assigned_at)
                <div class="app-timeline-item">
                    <div class="app-timeline-marker info"></div>
                    <div class="app-timeline-content">
                        <h6>Staff Assigned</h6>
                        <small>{{ $serviceRequest->assigned_at->format('M d, Y g:i A') }}</small>
                    </div>
                </div>
                @endif
                
                @if($serviceRequest->started_at)
                <div class="app-timeline-item">
                    <div class="app-timeline-marker warning"></div>
                    <div class="app-timeline-content">
                        <h6>Service Started</h6>
                        <small>{{ $serviceRequest->started_at->format('M d, Y g:i A') }}</small>
                    </div>
                </div>
                @endif
                
                @if($serviceRequest->completed_at)
                <div class="app-timeline-item">
                    <div class="app-timeline-marker success"></div>
                    <div class="app-timeline-content">
                        <h6>Service Completed</h6>
                        <small>{{ $serviceRequest->completed_at->format('M d, Y g:i A') }}</small>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        @if($serviceRequest->status === 'pending')
        <div class="app-actions">
            <button class="app-btn-warning" onclick="cancelRequest({{ $serviceRequest->id }})">
                <i class="fas fa-times me-2"></i>Cancel Request
            </button>
        </div>
        @endif
    </div>

    <!-- Bottom Navigation -->
    @include('auth::components.bottom-nav')
</div>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Service Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Service Type</label>
                                    <div class="fw-bold">{{ $serviceRequest->serviceType->name }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Status</label>
                                    <div>
                                        <span class="badge 
                                            @if($serviceRequest->status === 'pending') bg-warning
                                            @elseif($serviceRequest->status === 'assigned') bg-info
                                            @elseif($serviceRequest->status === 'in_progress') bg-primary
                                            @elseif($serviceRequest->status === 'completed') bg-success
                                            @else bg-secondary
                                            @endif fs-6">
                                            {{ ucfirst(str_replace('_', ' ', $serviceRequest->status)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Duration</label>
                                    <div class="fw-bold">{{ $serviceRequest->duration_days }} days</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Total Amount</label>
                                    <div class="fw-bold text-success fs-5">₹{{ number_format($serviceRequest->total_amount) }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Start Date</label>
                                    <div class="fw-bold">{{ $serviceRequest->start_date->format('M d, Y') }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">End Date</label>
                                    <div class="fw-bold">{{ $serviceRequest->end_date->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location & Contact -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Location & Contact</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Service Location</label>
                                    <div class="fw-bold">{{ $serviceRequest->location }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Contact Person</label>
                                    <div class="fw-bold">{{ $serviceRequest->contact_person }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Contact Phone</label>
                                    <div class="fw-bold">{{ $serviceRequest->contact_phone }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Preferred Staff Type</label>
                                    <div class="fw-bold">{{ ucfirst($serviceRequest->preferred_staff_type) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes & Requirements -->
                    @if($serviceRequest->notes || $serviceRequest->special_requirements)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Additional Information</h5>
                        </div>
                        <div class="card-body">
                            @if($serviceRequest->notes)
                            <div class="mb-3">
                                <label class="form-label text-muted">Notes</label>
                                <div class="fw-bold">{{ $serviceRequest->notes }}</div>
                            </div>
                            @endif
                            @if($serviceRequest->special_requirements)
                            <div class="mb-3">
                                <label class="form-label text-muted">Special Requirements</label>
                                <div class="fw-bold">{{ $serviceRequest->special_requirements }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Assigned Staff -->
                    @if($serviceRequest->assignedStaff)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Assigned Staff</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-user-{{ $serviceRequest->assignedStaff->isNurse() ? 'nurse' : 'md' }} fa-3x text-{{ $serviceRequest->assignedStaff->isNurse() ? 'info' : 'success' }}"></i>
                            </div>
                            <h6 class="text-center">{{ $serviceRequest->assignedStaff->name }}</h6>
                            <div class="text-center mb-2">
                                <span class="badge bg-{{ $serviceRequest->assignedStaff->isNurse() ? 'info' : 'success' }}">
                                    {{ ucfirst($serviceRequest->assignedStaff->role) }}
                                </span>
                            </div>
                            <div class="text-center text-muted small">
                                ID: {{ $serviceRequest->assignedStaff->unique_id }}
                            </div>
                            @if($serviceRequest->assignedStaff->qualification)
                            <div class="mt-2">
                                <small class="text-muted">Qualification:</small>
                                <div class="fw-bold">{{ $serviceRequest->assignedStaff->qualification }}</div>
                            </div>
                            @endif
                            @if($serviceRequest->assignedStaff->experience)
                            <div class="mt-2">
                                <small class="text-muted">Experience:</small>
                                <div class="fw-bold">{{ $serviceRequest->assignedStaff->experience }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Timeline -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Request Timeline</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6>Request Submitted</h6>
                                        <small class="text-muted">{{ $serviceRequest->created_at->format('M d, Y g:i A') }}</small>
                                    </div>
                                </div>
                                
                                @if($serviceRequest->assigned_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6>Staff Assigned</h6>
                                        <small class="text-muted">{{ $serviceRequest->assigned_at->format('M d, Y g:i A') }}</small>
                                    </div>
                                </div>
                                @endif
                                
                                @if($serviceRequest->started_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6>Service Started</h6>
                                        <small class="text-muted">{{ $serviceRequest->started_at->format('M d, Y g:i A') }}</small>
                                    </div>
                                </div>
                                @endif
                                
                                @if($serviceRequest->completed_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6>Service Completed</h6>
                                        <small class="text-muted">{{ $serviceRequest->completed_at->format('M d, Y g:i A') }}</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Actions</h5>
                        </div>
                        <div class="card-body">
                            @if($serviceRequest->status === 'pending')
                            <button class="btn btn-warning btn-sm w-100 mb-2" onclick="cancelRequest({{ $serviceRequest->id }})">
                                <i class="fas fa-times me-1"></i>Cancel Request
                            </button>
                            @endif
                            
                            <a href="{{ route('services.my-requests') }}" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fas fa-arrow-left me-1"></i>Back to Requests
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

/* Status Card */
.app-status-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.app-status-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
}

.app-status-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.app-status-card.status-pending .app-status-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.app-status-card.status-assigned .app-status-icon {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.app-status-card.status-in_progress .app-status-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-status-card.status-completed .app-status-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.app-status-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 4px 0;
}

.app-status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}

.app-status-card.status-pending .app-status-badge {
    background: #f59e0b;
}

.app-status-card.status-assigned .app-status-badge {
    background: #3b82f6;
}

.app-status-card.status-in_progress .app-status-badge {
    background: #667eea;
}

.app-status-card.status-completed .app-status-badge {
    background: #10b981;
}

.app-status-amount {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid #e9ecef;
}

.app-status-amount span:first-child {
    font-size: 0.9rem;
    color: #6c757d;
}

.app-status-amount span:last-child {
    font-size: 1.5rem;
    font-weight: 700;
    color: #28a745;
}

/* Detail Sections */
.app-detail-section {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.app-section-header {
    margin-bottom: 16px;
}

.app-section-title {
    font-size: 1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
    display: flex;
    align-items: center;
}

.app-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.app-detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.app-detail-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.app-detail-value {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2c3e50;
}

.app-detail-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.app-detail-row {
    display: flex;
    align-items: start;
    gap: 12px;
}

.app-detail-row i {
    width: 24px;
    color: #667eea;
    margin-top: 2px;
}

/* Staff Card */
.app-staff-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.app-staff-info {
    display: flex;
    align-items: start;
    gap: 16px;
}

.app-staff-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.app-staff-avatar.nurse {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.app-staff-avatar.caregiver {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.app-staff-details {
    flex: 1;
}

.app-staff-name {
    font-size: 1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 8px 0;
}

.app-staff-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}

.app-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}

.app-badge-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-badge-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.app-badge-secondary {
    background: #6c757d;
}

.app-staff-qual,
.app-staff-exp {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 8px;
}

.app-staff-qual i,
.app-staff-exp i {
    color: #667eea;
}

/* Notes */
.app-note-item {
    margin-bottom: 16px;
}

.app-note-item:last-child {
    margin-bottom: 0;
}

.app-note-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 8px;
}

.app-note-content {
    font-size: 0.9rem;
    color: #2c3e50;
    line-height: 1.5;
}

/* Timeline */
.app-timeline-section {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.app-timeline {
    position: relative;
    padding-left: 32px;
}

.app-timeline::before {
    content: '';
    position: absolute;
    left: 11px;
    top: 20px;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.app-timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.app-timeline-item:last-child {
    margin-bottom: 0;
}

.app-timeline-marker {
    position: absolute;
    left: -32px;
    top: 4px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #e9ecef;
}

.app-timeline-marker.primary {
    background: #667eea;
}

.app-timeline-marker.info {
    background: #3b82f6;
}

.app-timeline-marker.warning {
    background: #f59e0b;
}

.app-timeline-marker.success {
    background: #10b981;
}

.app-timeline-content h6 {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0 0 4px 0;
}

.app-timeline-content small {
    font-size: 0.8rem;
    color: #6c757d;
}

/* Actions */
.app-actions {
    margin-top: 16px;
}

.app-btn-warning {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    background: #f59e0b;
    color: white;
    border: none;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.app-btn-warning:active {
    transform: scale(0.98);
}

/* Desktop View */
@media (min-width: 768px) {
    .mobile-app-container {
        padding-bottom: 0;
    }
    
    .app-content {
        padding: 24px;
        padding-bottom: 24px;
    }
    
    .app-detail-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}
</style>

<script>
function cancelRequest(requestId) {
    if (confirm('Are you sure you want to cancel this service request?')) {
        // Here you would typically make an AJAX request to cancel the request
        alert('Request cancellation feature will be implemented soon.');
    }
}
</script>
@endsection

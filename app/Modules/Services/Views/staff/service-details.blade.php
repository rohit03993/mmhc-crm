@extends('auth::layout')

@section('title', 'Service Details - MMHC CRM')

@section('head')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --info-gradient: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="service-details-header mb-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <a href="{{ route('staff.dashboard') }}" class="btn btn-link text-decoration-none p-0 me-3">
                                <i class="fas fa-arrow-left fa-lg"></i>
                            </a>
                            <div>
                                <h2 class="service-details-title mb-1">Service Assignment Details</h2>
                                <p class="service-details-subtitle text-muted mb-0">Complete information about your assigned service</p>
                            </div>
                        </div>
                    </div>
                    <div class="status-badge-large status-{{ $serviceRequest->status }}">
                        <i class="fas fa-{{ $serviceRequest->status === 'assigned' ? 'user-check' : ($serviceRequest->status === 'in_progress' ? 'play-circle' : ($serviceRequest->status === 'completed' ? 'check-circle' : 'clock')) }} me-2"></i>
                        {{ ucfirst(str_replace('_', ' ', $serviceRequest->status)) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Service Overview Card -->
            <div class="details-card mb-4">
                <div class="details-card-header status-header-{{ $serviceRequest->status }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="details-card-title mb-1">
                                <i class="fas fa-info-circle me-2"></i>Service Overview
                            </h5>
                            <p class="details-card-subtitle mb-0">{{ $serviceRequest->serviceType->name }}</p>
                        </div>
                        <div class="service-type-icon">
                            <i class="fas fa-{{ $serviceRequest->serviceType->duration_hours == 24 ? 'clock' : ($serviceRequest->serviceType->duration_hours == 12 ? 'clock' : ($serviceRequest->serviceType->duration_hours == 8 ? 'hourglass-half' : 'calendar-check')) }}"></i>
                        </div>
                    </div>
                </div>
                <div class="details-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-calendar-alt me-2"></i>Duration
                                </div>
                                <div class="detail-value">{{ $serviceRequest->duration_days }} days</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-clock me-2"></i>Hours/Day
                                </div>
                                <div class="detail-value">{{ $serviceRequest->serviceType->duration_hours }} hours</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-calendar-check me-2"></i>Start Date
                                </div>
                                <div class="detail-value">{{ $serviceRequest->start_date->format('M d, Y') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-calendar-times me-2"></i>End Date
                                </div>
                                <div class="detail-value">{{ $serviceRequest->end_date->format('M d, Y') }}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Service Location
                                </div>
                                <div class="detail-value">{{ $serviceRequest->location }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Information Card -->
            <div class="details-card mb-4">
                <div class="details-card-header bg-info text-white">
                    <h5 class="details-card-title mb-0">
                        <i class="fas fa-user-injured me-2"></i>Patient Information
                    </h5>
                </div>
                <div class="details-card-body">
                    <div class="patient-info-grid">
                        <div class="patient-avatar-section">
                            <div class="patient-avatar">
                                <i class="fas fa-user-injured"></i>
                            </div>
                            <div class="patient-name">{{ $serviceRequest->patient->name }}</div>
                            <div class="patient-id">ID: {{ $serviceRequest->patient->unique_id }}</div>
                        </div>
                        <div class="patient-details">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="fas fa-user me-2"></i>Contact Person
                                        </div>
                                        <div class="detail-value">{{ $serviceRequest->contact_person }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="fas fa-phone me-2"></i>Contact Phone
                                        </div>
                                        <div class="detail-value">
                                            <a href="tel:{{ $serviceRequest->contact_phone }}" class="text-decoration-none">
                                                {{ $serviceRequest->contact_phone }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Requirements -->
            @if($serviceRequest->notes || $serviceRequest->special_requirements)
            <div class="details-card mb-4">
                <div class="details-card-header bg-warning text-white">
                    <h5 class="details-card-title mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>Service Requirements
                    </h5>
                </div>
                <div class="details-card-body">
                    @if($serviceRequest->notes)
                    <div class="requirement-item mb-3">
                        <div class="requirement-label">
                            <i class="fas fa-sticky-note me-2"></i>General Notes
                        </div>
                        <div class="requirement-content">{{ $serviceRequest->notes }}</div>
                    </div>
                    @endif
                    @if($serviceRequest->special_requirements)
                    <div class="requirement-item">
                        <div class="requirement-label">
                            <i class="fas fa-exclamation-triangle me-2"></i>Special Requirements
                        </div>
                        <div class="requirement-content">{{ $serviceRequest->special_requirements }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Earnings Card -->
            <div class="earnings-card-modern mb-4">
                <div class="earnings-card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-rupee-sign me-2"></i>Your Earnings
                    </h5>
                </div>
                <div class="earnings-card-body">
                    <div class="earnings-total">
                        @if($serviceRequest->status === 'completed' && $serviceRequest->isApprovedByAdmin())
                            <div class="earnings-amount-large earnings-earned">
                                ₹{{ number_format($serviceRequest->total_staff_payout ?? 0) }}
                            </div>
                            <div class="earnings-label-small">Total Earnings Earned</div>
                            <div class="earnings-note-small text-success">
                                <i class="fas fa-check-circle me-1"></i>Approved by Admin
                            </div>
                        @elseif($serviceRequest->status === 'completed' && !$serviceRequest->isApprovedByAdmin())
                            <div class="earnings-amount-large earnings-pending-approval">
                                ₹{{ number_format($serviceRequest->total_staff_payout ?? 0) }}
                            </div>
                            <div class="earnings-label-small">Pending Admin Approval</div>
                            <div class="earnings-note-small text-warning">
                                <i class="fas fa-clock me-1"></i>Sent for Approval
                            </div>
                        @elseif($serviceRequest->status === 'in_progress')
                            <div class="earnings-amount-large earnings-pending">
                                ₹{{ number_format($serviceRequest->total_staff_payout ?? 0) }}
                            </div>
                            <div class="earnings-label-small">Projected Earnings</div>
                            <div class="earnings-note-small">Payment after completion</div>
                        @else
                            <div class="earnings-amount-large earnings-projected">
                                ₹{{ number_format($serviceRequest->total_staff_payout ?? 0) }}
                            </div>
                            <div class="earnings-label-small">Projected Earnings</div>
                            <div class="earnings-note-small">Not yet earned</div>
                        @endif
                    </div>
                    
                    <div class="earnings-breakdown-modern">
                        <div class="earnings-item">
                            <div class="earnings-item-label">Daily Rate</div>
                            <div class="earnings-item-value">
                                @if($serviceRequest->total_staff_payout)
                                    ₹{{ number_format($serviceRequest->total_staff_payout / $serviceRequest->duration_days) }}/day
                                @else
                                    <span class="text-muted">Will be calculated on assignment</span>
                                @endif
                            </div>
                        </div>
                        <div class="earnings-item">
                            <div class="earnings-item-label">Duration</div>
                            <div class="earnings-item-value">{{ $serviceRequest->duration_days }} days</div>
                        </div>
                        <div class="earnings-item">
                            <div class="earnings-item-label">Service Type</div>
                            <div class="earnings-item-value">{{ $serviceRequest->serviceType->name }}</div>
                        </div>
                        <div class="earnings-item">
                            <div class="earnings-item-label">Your Role</div>
                            <div class="earnings-item-value role-badge-{{ auth()->user()->isNurse() ? 'nurse' : 'caregiver' }}">
                                {{ auth()->user()->isNurse() ? 'Licensed Nurse' : 'Caregiver' }}
                            </div>
                        </div>
                        
                        @if($serviceRequest->status === 'completed' && $serviceRequest->isApprovedByAdmin() && $serviceRequest->total_staff_payout)
                        <div class="earnings-payment-status">
                            <div class="payment-status-badge payment-ready">
                                <i class="fas fa-rupee-sign me-2"></i>
                                Payment Approved & Ready
                            </div>
                            <div class="payment-info-text">
                                Your earnings of ₹{{ number_format($serviceRequest->total_staff_payout) }} have been approved and will be processed within 3-5 business days.
                                <br><small>Approved by: {{ $serviceRequest->approvedBy->name ?? 'Admin' }} on {{ $serviceRequest->admin_approved_at->format('M d, Y') }}</small>
                            </div>
                        </div>
                        @elseif($serviceRequest->status === 'completed' && !$serviceRequest->isApprovedByAdmin() && $serviceRequest->total_staff_payout)
                        <div class="earnings-payment-status">
                            <div class="payment-status-badge payment-pending-approval">
                                <i class="fas fa-hourglass-half me-2"></i>
                                Pending Admin Approval
                            </div>
                            <div class="payment-info-text">
                                Your earnings request of ₹{{ number_format($serviceRequest->total_staff_payout) }} has been sent to admin for approval. 
                                Amount will be shown in green once approved.
                                <br><small>Completed on: {{ $serviceRequest->completed_at->format('M d, Y g:i A') }}</small>
                            </div>
                        </div>
                        @elseif($serviceRequest->status === 'in_progress' && $serviceRequest->total_staff_payout)
                        <div class="earnings-payment-status">
                            <div class="payment-status-badge payment-pending">
                                <i class="fas fa-clock me-2"></i>
                                Earnings Not Yet Earned
                            </div>
                            <div class="payment-info-text">
                                These are projected earnings. Payment will be processed only after you complete the service.
                            </div>
                        </div>
                        @elseif($serviceRequest->status === 'assigned' && $serviceRequest->total_staff_payout)
                        <div class="earnings-payment-status">
                            <div class="payment-status-badge payment-not-earned">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                Not Yet Earned
                            </div>
                            <div class="payment-info-text">
                                This is the projected amount. Earnings will be available only after you complete the entire service period.
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="details-card mb-4">
                <div class="details-card-header bg-primary text-white">
                    <h5 class="details-card-title mb-0">
                        <i class="fas fa-history me-2"></i>Service Timeline
                    </h5>
                </div>
                <div class="details-card-body">
                    <div class="timeline-modern">
                        <div class="timeline-item-modern">
                            <div class="timeline-marker-modern timeline-primary"></div>
                            <div class="timeline-content-modern">
                                <div class="timeline-title">Service Assigned</div>
                                <div class="timeline-date">
                                    {{ $serviceRequest->assigned_at ? $serviceRequest->assigned_at->format('M d, Y g:i A') : 'Recently' }}
                                </div>
                            </div>
                        </div>
                        
                        @if($serviceRequest->started_at)
                        <div class="timeline-item-modern">
                            <div class="timeline-marker-modern timeline-warning"></div>
                            <div class="timeline-content-modern">
                                <div class="timeline-title">Service Started</div>
                                <div class="timeline-date">{{ $serviceRequest->started_at->format('M d, Y g:i A') }}</div>
                            </div>
                        </div>
                        @endif
                        
                        @if($serviceRequest->completed_at)
                        <div class="timeline-item-modern">
                            <div class="timeline-marker-modern timeline-success"></div>
                            <div class="timeline-content-modern">
                                <div class="timeline-title">Service Completed</div>
                                <div class="timeline-date">{{ $serviceRequest->completed_at->format('M d, Y g:i A') }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="details-card">
                <div class="details-card-header bg-dark text-white">
                    <h5 class="details-card-title mb-0">
                        <i class="fas fa-cog me-2"></i>Actions
                    </h5>
                </div>
                <div class="details-card-body">
                    <div class="action-buttons">
                        @if($serviceRequest->status === 'assigned')
                        <button class="btn-action btn-action-start w-100 mb-2" onclick="startService({{ $serviceRequest->id }})">
                            <i class="fas fa-play me-2"></i>Start Service
                        </button>
                        @elseif($serviceRequest->status === 'in_progress')
                        <button class="btn-action btn-action-complete w-100 mb-2" onclick="completeService({{ $serviceRequest->id }})">
                            <i class="fas fa-check me-2"></i>Mark as Completed
                        </button>
                        @endif
                        
                        <a href="{{ route('staff.dashboard') }}" class="btn-action btn-action-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comprehensive Mobile-First Styling -->
<style>
/* Header Styles */
.service-details-header {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.service-details-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.service-details-subtitle {
    font-size: 0.9rem;
}

.status-badge-large {
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: 600;
    font-size: 1rem;
    color: white;
}

.status-badge-large.status-assigned {
    background: var(--info-gradient);
}

.status-badge-large.status-in_progress {
    background: var(--primary-gradient);
}

.status-badge-large.status-completed {
    background: var(--success-gradient);
}

/* Detail Cards */
.details-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.details-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

.details-card-header {
    padding: 1.2rem 1.5rem;
    color: white;
}

.status-header-assigned {
    background: var(--info-gradient);
}

.status-header-in_progress {
    background: var(--primary-gradient);
}

.status-header-completed {
    background: var(--success-gradient);
}

.details-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
}

.details-card-subtitle {
    font-size: 0.85rem;
    opacity: 0.9;
}

.service-type-icon {
    font-size: 2rem;
    opacity: 0.8;
}

.details-card-body {
    padding: 1.5rem;
}

/* Detail Items */
.detail-item {
    padding: 0.75rem 0;
}

.detail-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.4rem;
    font-weight: 600;
}

.detail-value {
    font-size: 1rem;
    font-weight: 700;
    color: #2c3e50;
}

/* Patient Info */
.patient-info-grid {
    display: grid;
    gap: 1.5rem;
}

.patient-avatar-section {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 12px;
}

.patient-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: var(--info-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    margin: 0 auto 1rem;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.patient-name {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.3rem;
}

.patient-id {
    font-size: 0.85rem;
    color: #6c757d;
}

/* Requirements */
.requirement-item {
    padding: 1rem;
    background: #fff9e6;
    border-left: 4px solid #ffc107;
    border-radius: 8px;
}

.requirement-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #856404;
    margin-bottom: 0.5rem;
}

.requirement-content {
    font-size: 0.95rem;
    color: #495057;
    line-height: 1.6;
}

/* Earnings Card */
.earnings-card-modern {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.earnings-card-header {
    background: var(--success-gradient);
    padding: 1.2rem 1.5rem;
    color: white;
}

.earnings-card-body {
    padding: 1.5rem;
}

.earnings-total {
    text-align: center;
    padding: 1.5rem 0;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 1.5rem;
}

.earnings-amount-large {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.earnings-earned {
    color: #28a745;
}

.earnings-pending {
    color: #ffc107;
}

.earnings-projected {
    color: #6c757d;
    opacity: 0.7;
}

.earnings-pending-approval {
    color: #6c757d;
    opacity: 0.6;
    text-decoration: line-through;
}

.earnings-note-small {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.3rem;
    font-style: italic;
}

.earnings-label-small {
    font-size: 0.85rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.earnings-breakdown-modern {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.earnings-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.earnings-item-label {
    font-size: 0.85rem;
    color: #6c757d;
}

.earnings-item-value {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2c3e50;
}

.role-badge-nurse {
    color: #17a2b8;
    font-weight: 700;
}

.role-badge-caregiver {
    color: #28a745;
    font-weight: 700;
}

/* Payment Status */
.earnings-payment-status {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid #e9ecef;
}

.payment-status-badge {
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.payment-ready {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.payment-pending {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.payment-not-earned {
    background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
    color: white;
}

.payment-pending-approval {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.payment-info-text {
    font-size: 0.8rem;
    color: #6c757d;
    line-height: 1.5;
    text-align: center;
}

/* Timeline Modern */
.timeline-modern {
    position: relative;
    padding-left: 2rem;
}

.timeline-item-modern {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-marker-modern {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 3px #dee2e6;
}

.timeline-primary {
    background: var(--primary-gradient);
}

.timeline-warning {
    background: var(--warning-gradient);
}

.timeline-success {
    background: var(--success-gradient);
}

.timeline-content-modern {
    padding-left: 0.5rem;
}

.timeline-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.timeline-date {
    font-size: 0.8rem;
    color: #6c757d;
}

.timeline-modern::before {
    content: '';
    position: absolute;
    left: -1.6rem;
    top: 1rem;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #dee2e6, transparent);
}

/* Action Buttons */
.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.btn-action {
    padding: 0.85rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    border: none;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-action-start {
    background: var(--success-gradient);
    color: white;
}

.btn-action-complete {
    background: var(--warning-gradient);
    color: white;
}

.btn-action-secondary {
    background: #6c757d;
    color: white;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    color: white;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .container-fluid {
        padding: 1rem;
    }
    
    .service-details-header {
        padding: 1rem;
    }
    
    .service-details-title {
        font-size: 1.3rem;
    }
    
    .status-badge-large {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    .details-card-body {
        padding: 1.2rem;
    }
    
    .earnings-amount-large {
        font-size: 2rem;
    }
    
    .patient-avatar {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .patient-name {
        font-size: 1rem;
    }
}

@media (max-width: 576px) {
    .service-details-title {
        font-size: 1.1rem;
    }
    
    .earnings-amount-large {
        font-size: 1.75rem;
    }
    
    .detail-value {
        font-size: 0.9rem;
    }
    
    .btn-action {
        padding: 0.75rem 1.2rem;
        font-size: 0.9rem;
    }
}
</style>

<script>
function startService(serviceId) {
    if (confirm('Are you sure you want to start this service?')) {
        // Show loading state
        const btn = event.target.closest('.btn-action-start');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Starting...';
        
        fetch(`/staff/service/${serviceId}/start`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                btn.innerHTML = '<i class="fas fa-check me-2"></i>Started!';
                btn.classList.remove('btn-action-start');
                btn.classList.add('btn-action-secondary');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert(data.message || 'Failed to start service');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while starting the service. Please try again.');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }
}

function completeService(serviceId) {
    if (confirm('Are you sure you want to mark this service as completed?')) {
        // Show loading state
        const btn = event.target.closest('.btn-action-complete');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Completing...';
        
        fetch(`/staff/service/${serviceId}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                btn.innerHTML = '<i class="fas fa-check me-2"></i>Completed!';
                btn.classList.remove('btn-action-complete');
                btn.classList.add('btn-action-secondary');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert(data.message || 'Failed to complete service');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while completing the service. Please try again.');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }
}
</script>
@endsection

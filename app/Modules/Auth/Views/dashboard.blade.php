@extends('auth::layout')

@section('title', 'Dashboard - MMHC CRM')

@section('head')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    
    <style>
        :root {
            --patient-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --info-gradient: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="patient-header-card mb-3">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="patient-avatar-large me-3">
                                <i class="fas fa-user-injured fa-2x"></i>
                            </div>
                            <div>
                                <h2 class="patient-name mb-1">Welcome back, {{ $user->name }}!</h2>
                                <p class="patient-subtitle mb-0">
                                    <span class="badge badge-patient">Patient</span>
                                    <span class="text-muted ms-2">ID: {{ $user->unique_id }}</span>
                                    <span class="profile-badge ms-2">{{ $stats['profile_completion'] }}% Profile Complete</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 text-md-end">
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <a href="{{ route('services.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus-circle me-1"></i>New Request
                            </a>
                            <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-user-edit me-1"></i>Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards - Mobile Optimized -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card-modern stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['total_requests'] }}</div>
                    <div class="stat-label">Total Requests</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card-modern stat-info">
                <div class="stat-icon">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['active_requests'] }}</div>
                    <div class="stat-label">Active Services</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card-modern stat-success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['completed_requests'] }}</div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card-modern stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['pending_requests'] }}</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Staff Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="modern-card">
                <div class="modern-card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2"></i>Available Healthcare Staff
                        </h5>
                        <a href="{{ route('staff.index') }}" class="btn btn-light btn-sm">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="modern-card-body">
                    <!-- Nurses Section -->
                    @if($available_nurses->count() > 0)
                    <div class="mb-4">
                        <h6 class="staff-section-title mb-3">
                            <i class="fas fa-user-nurse me-2 text-primary"></i>Licensed Nurses
                        </h6>
                        <div class="row g-3">
                            @foreach($available_nurses as $nurse)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="staff-card">
                                    <div class="staff-avatar">
                                        <i class="fas fa-user-nurse"></i>
                                    </div>
                                    <div class="staff-info">
                                        <h6 class="staff-name">{{ $nurse->name }}</h6>
                                        <div class="staff-details">
                                            @if($nurse->qualification)
                                            <div class="staff-detail-item">
                                                <i class="fas fa-graduation-cap"></i>
                                                <span>{{ $nurse->qualification }}</span>
                                            </div>
                                            @endif
                                            @if($nurse->experience)
                                            <div class="staff-detail-item">
                                                <i class="fas fa-briefcase"></i>
                                                <span>{{ $nurse->experience }} years exp.</span>
                                            </div>
                                            @endif
                                        </div>
                                        @if($service_types->count() > 0)
                                        <div class="staff-pricing">
                                            <small class="text-muted">Starting from</small>
                                            <div class="staff-price">
                                                ₹{{ number_format($service_types->first()->patient_charge) }}/day
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="staff-action">
                                        <a href="{{ route('services.create') }}?staff_type=nurse&staff_id={{ $nurse->id }}" 
                                           class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-calendar-check me-1"></i>Request Service
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <!-- Caregivers Section -->
                    @if($available_caregivers->count() > 0)
                    <div>
                        <h6 class="staff-section-title mb-3">
                            <i class="fas fa-user-md me-2 text-success"></i>Caregivers
                        </h6>
                        <div class="row g-3">
                            @foreach($available_caregivers as $caregiver)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="staff-card">
                                    <div class="staff-avatar caregiver">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                    <div class="staff-info">
                                        <h6 class="staff-name">{{ $caregiver->name }}</h6>
                                        <div class="staff-details">
                                            @if($caregiver->qualification)
                                            <div class="staff-detail-item">
                                                <i class="fas fa-graduation-cap"></i>
                                                <span>{{ $caregiver->qualification }}</span>
                                            </div>
                                            @endif
                                            @if($caregiver->experience)
                                            <div class="staff-detail-item">
                                                <i class="fas fa-briefcase"></i>
                                                <span>{{ $caregiver->experience }} years exp.</span>
                                            </div>
                                            @endif
                                        </div>
                                        @if($service_types->count() > 0)
                                        <div class="staff-pricing">
                                            <small class="text-muted">Starting from</small>
                                            <div class="staff-price">
                                                ₹{{ number_format($service_types->first()->patient_charge) }}/day
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="staff-action">
                                        <a href="{{ route('services.create') }}?staff_type=caregiver&staff_id={{ $caregiver->id }}" 
                                           class="btn btn-success btn-sm w-100">
                                            <i class="fas fa-calendar-check me-1"></i>Request Service
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if($available_nurses->count() == 0 && $available_caregivers->count() == 0)
                    <div class="empty-state py-4">
                        <div class="empty-state-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h6 class="empty-state-title">No Staff Available</h6>
                        <p class="empty-state-text">Check back later for available healthcare staff.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row g-4">
        <!-- Recent Service Requests -->
        <div class="col-12 col-lg-8">
            <div class="modern-card">
                <div class="modern-card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Recent Service Requests
                        </h5>
                        <a href="{{ route('services.my-requests') }}" class="btn btn-light btn-sm">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="modern-card-body">
                    @if(isset($recent_requests) && $recent_requests->count() > 0)
                        <div class="service-requests-list">
                            @foreach($recent_requests as $request)
                            <div class="service-request-item">
                                <div class="service-request-icon status-{{ $request->status }}">
                                    <i class="fas fa-{{ $request->status === 'pending' ? 'clock' : ($request->status === 'assigned' ? 'user-check' : ($request->status === 'in_progress' ? 'play-circle' : 'check-circle')) }}"></i>
                                </div>
                                <div class="service-request-content">
                                    <div class="service-request-header">
                                        <div class="service-request-title">{{ $request->serviceType->name }}</div>
                                        <span class="badge badge-status-{{ $request->status }}">
                                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                        </span>
                                    </div>
                                    <div class="service-request-details">
                                        <div class="detail-item">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            <span>{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-rupee-sign me-1"></i>
                                            <span>₹{{ number_format($request->total_amount) }}</span>
                                        </div>
                                        @if($request->assignedStaff)
                                        <div class="detail-item">
                                            <i class="fas fa-user-{{ $request->assignedStaff->isNurse() ? 'nurse' : 'md' }} me-1"></i>
                                            <span>{{ $request->assignedStaff->name }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="service-request-actions">
                                    <a href="{{ route('services.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <h5 class="empty-state-title">No Service Requests Yet</h5>
                            <p class="empty-state-text">Get started by requesting your first healthcare service.</p>
                            <a href="{{ route('services.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>Request Service
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions & Profile -->
        <div class="col-12 col-lg-4">
            <!-- Quick Actions -->
            <div class="modern-card mb-4">
                <div class="modern-card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="modern-card-body">
                    <div class="quick-actions-list">
                        <a href="{{ route('services.create') }}" class="quick-action-item">
                            <div class="quick-action-icon-small bg-primary">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="quick-action-text">
                                <div class="quick-action-title">Request Service</div>
                                <div class="quick-action-subtitle">Book healthcare service</div>
                            </div>
                            <i class="fas fa-chevron-right quick-action-arrow"></i>
                        </a>
                        
                        <a href="{{ route('services.my-requests') }}" class="quick-action-item">
                            <div class="quick-action-icon-small bg-info">
                                <i class="fas fa-list"></i>
                            </div>
                            <div class="quick-action-text">
                                <div class="quick-action-title">My Requests</div>
                                <div class="quick-action-subtitle">View all requests</div>
                            </div>
                            <i class="fas fa-chevron-right quick-action-arrow"></i>
                        </a>
                        
                        <a href="{{ route('plans.index') }}" class="quick-action-item">
                            <div class="quick-action-icon-small bg-warning">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div class="quick-action-text">
                                <div class="quick-action-title">Healthcare Plans</div>
                                <div class="quick-action-subtitle">Browse plans</div>
                            </div>
                            <i class="fas fa-chevron-right quick-action-arrow"></i>
                        </a>
                        
                        <a href="{{ route('profile.edit') }}" class="quick-action-item">
                            <div class="quick-action-icon-small bg-secondary">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div class="quick-action-text">
                                <div class="quick-action-title">Update Profile</div>
                                <div class="quick-action-subtitle">{{ $stats['profile_completion'] }}% complete</div>
                            </div>
                            <i class="fas fa-chevron-right quick-action-arrow"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Profile Completion -->
            @if($stats['profile_completion'] < 100)
            <div class="modern-card">
                <div class="modern-card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-check me-2"></i>Complete Your Profile
                    </h5>
                </div>
                <div class="modern-card-body">
                    <div class="profile-progress">
                        <div class="progress-bar-wrapper">
                            <div class="progress-bar-fill" style="width: {{ $stats['profile_completion'] }}%"></div>
                        </div>
                        <div class="progress-text">
                            <span>{{ $stats['profile_completion'] }}% Complete</span>
                            <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-primary">
                                Complete Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Comprehensive Mobile-First Styling -->
<style>
/* Header Styles */
.patient-header-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.patient-avatar-large {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: var(--patient-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
}

.patient-name {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.patient-subtitle {
    font-size: 0.9rem;
}

.badge-patient {
    background: var(--patient-gradient);
    color: white;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
}

.profile-badge {
    background: #f8f9fa;
    color: #6c757d;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.8rem;
}

/* Stat Cards */
.stat-card-modern {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card-modern:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.stat-primary .stat-icon {
    background: var(--primary-gradient);
}

.stat-info .stat-icon {
    background: var(--info-gradient);
}

.stat-success .stat-icon {
    background: var(--success-gradient);
}

.stat-warning .stat-icon {
    background: var(--warning-gradient);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1;
    margin-bottom: 0.3rem;
}

.stat-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Modern Card */
.modern-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.modern-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

.modern-card-header {
    padding: 1.2rem 1.5rem;
}

.modern-card-body {
    padding: 1.5rem;
}

/* Service Requests List */
.service-requests-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.service-request-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.service-request-item:hover {
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transform: translateX(5px);
}

.service-request-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.service-request-icon.status-pending {
    background: var(--warning-gradient);
}

.service-request-icon.status-assigned {
    background: var(--info-gradient);
}

.service-request-icon.status-in_progress {
    background: var(--primary-gradient);
}

.service-request-icon.status-completed {
    background: var(--success-gradient);
}

.service-request-content {
    flex: 1;
}

.service-request-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.service-request-title {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
}

.badge-status-pending {
    background: #ffc107;
    color: #000;
}

.badge-status-assigned {
    background: #17a2b8;
    color: white;
}

.badge-status-in_progress {
    background: #667eea;
    color: white;
}

.badge-status-completed {
    background: #28a745;
    color: white;
}

.service-request-details {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.85rem;
    color: #6c757d;
}

.detail-item {
    display: flex;
    align-items: center;
}

.service-request-actions {
    display: flex;
    gap: 0.5rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-state-icon {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

.empty-state-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.empty-state-text {
    color: #6c757d;
    margin-bottom: 1.5rem;
}

/* Quick Actions List */
.quick-actions-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.quick-action-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 12px;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.3s ease;
}

.quick-action-item:hover {
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transform: translateX(5px);
    color: #2c3e50;
}

.quick-action-icon-small {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    flex-shrink: 0;
}

.quick-action-text {
    flex: 1;
}

.quick-action-title {
    font-size: 0.95rem;
    font-weight: 600;
    margin-bottom: 0.2rem;
}

.quick-action-subtitle {
    font-size: 0.8rem;
    color: #6c757d;
}

.quick-action-arrow {
    color: #95a5a6;
}

/* Profile Progress */
.profile-progress {
    padding: 0.5rem 0;
}

.progress-bar-wrapper {
    height: 10px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 0.75rem;
}

.progress-bar-fill {
    height: 100%;
    background: var(--success-gradient);
    border-radius: 10px;
    transition: width 0.3s ease;
}

.progress-text {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
    color: #6c757d;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .container-fluid {
        padding: 1rem;
    }
    
    .patient-header-card {
        padding: 1rem;
    }
    
    .patient-name {
        font-size: 1.3rem;
    }
    
    .stat-card-modern {
        padding: 1rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .service-request-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .service-request-actions {
        width: 100%;
        justify-content: flex-end;
    }
    
    .service-request-details {
        flex-direction: column;
        gap: 0.5rem;
    }
}

@media (max-width: 576px) {
    .patient-name {
        font-size: 1.1rem;
    }
    
    .stat-value {
        font-size: 1.3rem;
    }
}

/* Staff Cards */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.staff-section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.staff-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 2px solid transparent;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.staff-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #667eea;
}

.staff-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
    margin: 0 auto 1rem;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.staff-avatar.caregiver {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
}

.staff-info {
    flex: 1;
    text-align: center;
    margin-bottom: 1rem;
}

.staff-name {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.75rem;
}

.staff-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.staff-detail-item {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #6c757d;
}

.staff-detail-item i {
    color: #667eea;
    width: 16px;
}

.staff-pricing {
    margin-top: auto;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.staff-price {
    font-size: 1.1rem;
    font-weight: 700;
    color: #28a745;
    margin-top: 0.25rem;
}

.staff-action {
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .staff-card {
        margin-bottom: 1rem;
    }
    
    .staff-avatar {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
}
</style>
@endsection

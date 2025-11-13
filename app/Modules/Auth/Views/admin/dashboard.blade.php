@extends('auth::layout')

@section('title', 'Admin Dashboard - MMHC CRM')

@section('head')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    
    <style>
        :root {
            --admin-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --info-gradient: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --danger-gradient: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="admin-header-card mb-3">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="admin-avatar-large me-3">
                                <i class="fas fa-shield-alt fa-2x"></i>
                            </div>
                            <div>
                                <h2 class="admin-name mb-1">{{ $user->name }}</h2>
                                <p class="admin-subtitle mb-0">
                                    <span class="badge badge-admin">Administrator</span>
                                    <span class="text-muted ms-2">ID: {{ $user->unique_id }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 text-md-end">
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <button class="btn btn-light btn-sm" onclick="window.print()">
                                <i class="fas fa-print me-1"></i>Print
                            </button>
                            <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">
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
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['total_users'] }}</div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card-modern stat-info">
                <div class="stat-icon">
                    <i class="fas fa-user-nurse"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['total_staff'] }}</div>
                    <div class="stat-label">Staff Members</div>
                    <div class="stat-sublabel">{{ $stats['total_nurses'] }} Nurses, {{ $stats['total_caregivers'] }} Caregivers</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card-modern stat-success">
                <div class="stat-icon">
                    <i class="fas fa-user-injured"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['total_patients'] }}</div>
                    <div class="stat-label">Patients</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card-modern stat-danger">
                <div class="stat-icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['pending_approvals'] }}</div>
                    <div class="stat-label">Pending Approvals</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Requests Stats -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="modern-card">
                <div class="modern-card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>Service Requests Overview
                    </h5>
                </div>
                <div class="modern-card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="stat-mini-card">
                                <div class="stat-mini-value">{{ $stats['total_service_requests'] }}</div>
                                <div class="stat-mini-label">Total Requests</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-mini-card stat-warning-mini">
                                <div class="stat-mini-value">{{ $stats['pending_service_requests'] }}</div>
                                <div class="stat-mini-label">Pending</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-mini-card stat-info-mini">
                                <div class="stat-mini-value">{{ $stats['in_progress_services'] }}</div>
                                <div class="stat-mini-label">In Progress</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-mini-card stat-success-mini">
                                <div class="stat-mini-value">{{ $stats['pending_approvals'] }}</div>
                                <div class="stat-mini-label">Need Approval</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="row g-4">
        <!-- Quick Actions -->
        <div class="col-12 col-lg-6">
            <div class="modern-card">
                <div class="modern-card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="modern-card-body">
                    <div class="quick-actions-grid">
                        <a href="{{ route('admin.service-requests') }}" class="quick-action-btn">
                            <div class="quick-action-icon bg-primary">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div class="quick-action-content">
                                <div class="quick-action-title">Service Requests</div>
                                <div class="quick-action-desc">Manage & Assign</div>
                            </div>
                            <div class="quick-action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        
                        <a href="{{ route('admin.profiles') }}" class="quick-action-btn">
                            <div class="quick-action-icon bg-info">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="quick-action-content">
                                <div class="quick-action-title">Manage Users</div>
                                <div class="quick-action-desc">View & Edit Profiles</div>
                            </div>
                            <div class="quick-action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        
                        <a href="{{ route('admin.referrals.index') }}" class="quick-action-btn">
                            <div class="quick-action-icon bg-info">
                                <i class="fas fa-share-alt"></i>
                            </div>
                            <div class="quick-action-content">
                                <div class="quick-action-title">Referral Management</div>
                                <div class="quick-action-desc">Track & Manage Referrals</div>
                            </div>
                            <div class="quick-action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        
                        <a href="{{ route('admin.page-content.index') }}" class="quick-action-btn">
                            <div class="quick-action-icon bg-warning">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="quick-action-content">
                                <div class="quick-action-title">Edit Landing Page</div>
                                <div class="quick-action-desc">Content & Plans</div>
                            </div>
                            <div class="quick-action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        
                        @if($stats['pending_approvals'] > 0)
                        <a href="{{ route('admin.service-requests') }}?filter=completed" class="quick-action-btn quick-action-urgent">
                            <div class="quick-action-icon bg-danger">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <div class="quick-action-content">
                                <div class="quick-action-title">Payment Approvals</div>
                                <div class="quick-action-desc">{{ $stats['pending_approvals'] }} pending</div>
                            </div>
                            <div class="quick-action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-12 col-lg-6">
            <div class="modern-card">
                <div class="modern-card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Recent Activity
                    </h5>
                </div>
                <div class="modern-card-body">
                    <div class="activity-timeline">
                        <div class="activity-item">
                            <div class="activity-icon bg-primary">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">{{ $recent_activity['message'] }}</div>
                                <div class="activity-time">{{ $recent_activity['time'] }}</div>
                            </div>
                        </div>
                        
                        <!-- Placeholder for more activities -->
                        <div class="activity-placeholder">
                            <i class="fas fa-clock text-muted"></i>
                            <p class="text-muted mb-0">Activity log will be populated by system events</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comprehensive Mobile-First Styling -->
<style>
/* Header Styles */
.admin-header-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.admin-avatar-large {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: var(--admin-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.admin-name {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.admin-subtitle {
    font-size: 0.9rem;
}

.badge-admin {
    background: var(--admin-gradient);
    color: white;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
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
    background: var(--admin-gradient);
}

.stat-info .stat-icon {
    background: var(--info-gradient);
}

.stat-success .stat-icon {
    background: var(--success-gradient);
}

.stat-danger .stat-icon {
    background: var(--danger-gradient);
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

.stat-sublabel {
    font-size: 0.75rem;
    color: #95a5a6;
    margin-top: 0.2rem;
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

/* Mini Stat Cards */
.stat-mini-card {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.stat-mini-card:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.stat-warning-mini {
    background: #fff3cd;
}

.stat-info-mini {
    background: #d1ecf1;
}

.stat-success-mini {
    background: #d4edda;
}

.stat-mini-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.3rem;
}

.stat-mini-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 600;
}

/* Quick Actions */
.quick-actions-grid {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 12px;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.quick-action-btn:hover {
    background: white;
    border-color: #667eea;
    transform: translateX(5px);
    color: #2c3e50;
    box-shadow: 0 2px 10px rgba(102, 126, 234, 0.2);
}

.quick-action-urgent {
    background: #ffe6e6;
    border-color: #ffcccc;
}

.quick-action-urgent:hover {
    background: #ffcccc;
    border-color: #eb3349;
}

.quick-action-icon {
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

.quick-action-content {
    flex: 1;
}

.quick-action-title {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.2rem;
}

.quick-action-desc {
    font-size: 0.85rem;
    color: #6c757d;
}

.quick-action-arrow {
    color: #95a5a6;
    font-size: 1rem;
}

/* Activity Timeline */
.activity-timeline {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    display: flex;
    align-items: start;
    gap: 1rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 12px;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.2rem;
}

.activity-time {
    font-size: 0.8rem;
    color: #6c757d;
}

.activity-placeholder {
    text-align: center;
    padding: 2rem;
    color: #95a5a6;
}

.activity-placeholder i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .container-fluid {
        padding: 1rem;
    }
    
    .admin-header-card {
        padding: 1rem;
    }
    
    .admin-name {
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
    
    .modern-card-body {
        padding: 1rem;
    }
}

@media (max-width: 576px) {
    .admin-name {
        font-size: 1.1rem;
    }
    
    .stat-value {
        font-size: 1.3rem;
    }
    
    .stat-mini-value {
        font-size: 1.3rem;
    }
}
</style>
@endsection

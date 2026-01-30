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

    <!-- Financial Overview -->
    @if(isset($stats['financial']))
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="modern-card">
                <div class="modern-card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Financial Overview
                    </h5>
                </div>
                <div class="modern-card-body">
                    <div class="row g-3">
                        <!-- Total Revenue -->
                        <div class="col-6 col-md-3">
                            <div class="stat-mini-card stat-success-mini clickable-card" 
                                 data-bs-toggle="tooltip" 
                                 data-bs-placement="top" 
                                 title="Total money received from all subscriptions and service requests (all time)">
                                <div class="stat-mini-value">₹{{ number_format($stats['financial']['total_revenue'], 0) }}</div>
                                <div class="stat-mini-label">Total Revenue</div>
                                <div class="stat-mini-sublabel">All subscriptions + services</div>
                            </div>
                        </div>
                        
                        <!-- Net Profit -->
                        <div class="col-6 col-md-3">
                            <div class="stat-mini-card {{ $stats['financial']['net_profit'] >= 0 ? 'stat-success-mini' : 'stat-danger-mini' }} clickable-card"
                                 data-bs-toggle="tooltip" 
                                 data-bs-placement="top" 
                                 title="Total Revenue minus Total Staff Payouts (Company profit)">
                                <div class="stat-mini-value">₹{{ number_format($stats['financial']['net_profit'], 0) }}</div>
                                <div class="stat-mini-label">Net Profit</div>
                                <div class="stat-mini-sublabel">Revenue - Staff Payouts</div>
                            </div>
                        </div>
                        
                        <!-- Pending Staff Payments -->
                        <div class="col-6 col-md-3">
                            <a href="{{ route('admin.payments.index') }}" class="text-decoration-none">
                                <div class="stat-mini-card stat-danger-mini clickable-card-hover"
                                     data-bs-toggle="tooltip" 
                                     data-bs-placement="top" 
                                     title="Click to view: Money owed TO STAFF (nurses/caregivers) for services, rewards, and referrals">
                                    <div class="stat-mini-value">₹{{ number_format($stats['financial']['pending_staff_payments'] ?? 0, 0) }}</div>
                                    <div class="stat-mini-label">Pending Staff Payments</div>
                                    <div class="stat-mini-sublabel">
                                        @if(($stats['financial']['staff_with_pending_payments'] ?? 0) > 0)
                                            {{ $stats['financial']['staff_with_pending_payments'] }} staff members
                                        @else
                                            To nurses/caregivers
                                        @endif
                                        <i class="fas fa-arrow-right ms-1"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <!-- Pending Payments -->
                        <div class="col-6 col-md-3">
                            <a href="{{ route('admin.pending-payments') }}" class="text-decoration-none">
                                <div class="stat-mini-card stat-warning-mini clickable-card-hover"
                                     data-bs-toggle="tooltip" 
                                     data-bs-placement="top" 
                                     title="Click to view: Money owed TO COMPANY by patients (subscriptions & services not fully paid)">
                                    <div class="stat-mini-value">₹{{ number_format($stats['financial']['total_pending_payments'], 0) }}</div>
                                    <div class="stat-mini-label">Pending Payments</div>
                                    <div class="stat-mini-sublabel">
                                        @if(($stats['financial']['pending_subscriptions_count'] ?? 0) > 0 || ($stats['financial']['pending_service_requests_count'] ?? 0) > 0)
                                            {{ ($stats['financial']['pending_subscriptions_count'] ?? 0) + ($stats['financial']['pending_service_requests_count'] ?? 0) }} items
                                        @else
                                            From patients
                                        @endif
                                        <i class="fas fa-arrow-right ms-1"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Detailed Breakdown -->
                    <hr class="my-3">
                    <!-- Financial Definitions -->
                    <div class="alert alert-info mb-3">
                        <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i><strong>Financial Metrics Explained:</strong></h6>
                        <div class="row g-2 small">
                            <div class="col-12 col-md-6">
                                <i class="fas fa-rupee-sign me-1"></i><strong>Total Revenue:</strong> All money received from subscriptions (paid) + services (paid/completed). This is money the COMPANY has received.
                            </div>
                            <div class="col-12 col-md-6">
                                <i class="fas fa-chart-line me-1"></i><strong>Net Profit:</strong> Total Revenue minus Total Staff Payouts. This is the COMPANY's profit after paying staff.
                            </div>
                            <div class="col-12 col-md-6">
                                <i class="fas fa-users me-1"></i><strong>Pending Staff Payments:</strong> Money OWED TO STAFF (nurses/caregivers) by the company. This includes:
                                <ul class="mb-0 mt-1 ps-3">
                                    <li>Service request payments (completed & approved)</li>
                                    <li>Patient reward earnings</li>
                                    <li>Staff referral bonuses</li>
                                    <li>Subscription referral commissions</li>
                                </ul>
                                <small class="text-info"><i class="fas fa-info-circle me-1"></i>Click the card to view and process payments</small>
                            </div>
                            <div class="col-12 col-md-6">
                                <i class="fas fa-clock me-1"></i><strong>Pending Payments (From Patients):</strong> Money OWED TO COMPANY by patients/customers. This includes:
                                <ul class="mb-0 mt-1 ps-3">
                                    <li>Subscription payments not yet verified</li>
                                    <li>Service payments not fully paid</li>
                                </ul>
                                <small class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>This is money patients owe, not staff payouts</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Revenue Breakdown</h6>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="financial-breakdown-item">
                                <div class="breakdown-label">
                                    <i class="fas fa-crown me-1"></i>Subscription Revenue
                                </div>
                                <div class="breakdown-value">₹{{ number_format($stats['financial']['total_subscription_revenue'], 2) }}</div>
                                <div class="breakdown-detail">
                                    From {{ $stats['financial']['active_subscriptions_count'] }} active subscriptions
                                </div>
                                <div class="breakdown-hint">
                                    <small class="text-muted">Money received from patient subscription payments</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="financial-breakdown-item">
                                <div class="breakdown-label">
                                    <i class="fas fa-clipboard-list me-1"></i>Service Revenue
                                </div>
                                <div class="breakdown-value">₹{{ number_format($stats['financial']['total_service_revenue'], 2) }}</div>
                                <div class="breakdown-detail">
                                    From completed service requests
                                </div>
                                <div class="breakdown-hint">
                                    <small class="text-muted">Money received from patients for healthcare services</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="financial-breakdown-item" style="border-left-color: #dc3545;">
                                <div class="breakdown-label">
                                    <i class="fas fa-money-bill-wave me-1"></i>Staff Payouts
                                </div>
                                <div class="breakdown-value text-danger">₹{{ number_format($stats['financial']['total_staff_payouts'], 2) }}</div>
                                <div class="breakdown-detail">
                                    Total paid to nurses/caregivers
                                </div>
                                <div class="breakdown-hint">
                                    <small class="text-muted">Money paid TO staff (reduces profit)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Monthly Recurring Revenue -->
                    @if($stats['financial']['monthly_recurring_revenue'] > 0)
                    <hr class="my-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="financial-mrr-card">
                                <div class="mrr-icon">
                                    <i class="fas fa-sync-alt"></i>
                                </div>
                                <div class="mrr-content">
                                    <div class="mrr-label">Monthly Recurring Revenue (MRR)</div>
                                    <div class="mrr-value">₹{{ number_format($stats['financial']['monthly_recurring_revenue'], 2) }}/month</div>
                                    <div class="mrr-detail">Recurring revenue from active subscriptions</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

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
                            <a href="{{ route('admin.service-requests') }}" class="text-decoration-none">
                                <div class="stat-mini-card clickable-card-hover"
                                     data-bs-toggle="tooltip" 
                                     data-bs-placement="top" 
                                     title="Click to view all service requests">
                                    <div class="stat-mini-value">{{ $stats['total_service_requests'] }}</div>
                                    <div class="stat-mini-label">Total Requests</div>
                                    <div class="stat-mini-sublabel">Click to view <i class="fas fa-arrow-right ms-1"></i></div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('admin.service-requests') }}?status=pending" class="text-decoration-none">
                                <div class="stat-mini-card stat-warning-mini clickable-card-hover"
                                     data-bs-toggle="tooltip" 
                                     data-bs-placement="top" 
                                     title="Click to view pending service requests">
                                    <div class="stat-mini-value">{{ $stats['pending_service_requests'] }}</div>
                                    <div class="stat-mini-label">Pending</div>
                                    @if($stats['pending_service_requests'] > 0)
                                    <div class="stat-mini-sublabel">Click to view <i class="fas fa-arrow-right ms-1"></i></div>
                                    @endif
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('admin.service-requests') }}?status=in_progress" class="text-decoration-none">
                                <div class="stat-mini-card stat-info-mini clickable-card-hover"
                                     data-bs-toggle="tooltip" 
                                     data-bs-placement="top" 
                                     title="Click to view in-progress service requests">
                                    <div class="stat-mini-value">{{ $stats['in_progress_services'] }}</div>
                                    <div class="stat-mini-label">In Progress</div>
                                    @if($stats['in_progress_services'] > 0)
                                    <div class="stat-mini-sublabel">Click to view <i class="fas fa-arrow-right ms-1"></i></div>
                                    @endif
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('admin.service-requests') }}?filter=completed" class="text-decoration-none">
                                <div class="stat-mini-card stat-success-mini clickable-card-hover"
                                     data-bs-toggle="tooltip" 
                                     data-bs-placement="top" 
                                     title="Click to view completed service requests needing approval">
                                    <div class="stat-mini-value">{{ $stats['pending_approvals'] }}</div>
                                    <div class="stat-mini-label">Need Approval</div>
                                    @if($stats['pending_approvals'] > 0)
                                    <div class="stat-mini-sublabel">Click to view <i class="fas fa-arrow-right ms-1"></i></div>
                                    @endif
                                </div>
                            </a>
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

                        <a href="{{ route('admin.achievement-media.index') }}" class="quick-action-btn">
                            <div class="quick-action-icon bg-info">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="quick-action-content">
                                <div class="quick-action-title">Achievements & Media</div>
                                <div class="quick-action-desc">Carousel images</div>
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

.stat-mini-sublabel {
    font-size: 0.7rem;
    color: #95a5a6;
    margin-top: 0.2rem;
    font-weight: 500;
}

/* Financial Breakdown */
.financial-breakdown-item {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 12px;
    border-left: 4px solid #667eea;
    transition: all 0.3s ease;
}

.financial-breakdown-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.breakdown-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.breakdown-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.3rem;
}

.breakdown-detail {
    font-size: 0.75rem;
    color: #95a5a6;
    margin-bottom: 0.3rem;
}

.breakdown-hint {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid #e9ecef;
}

/* Clickable Card Styles */
.clickable-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.clickable-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15) !important;
}

.clickable-card-hover {
    cursor: pointer;
    transition: all 0.3s ease;
}

.clickable-card-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15) !important;
}

/* Monthly Recurring Revenue Card */
.financial-mrr-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    color: white;
}

.mrr-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.mrr-content {
    flex: 1;
}

.mrr-label {
    font-size: 0.85rem;
    opacity: 0.9;
    margin-bottom: 0.3rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.mrr-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.3rem;
}

.mrr-detail {
    font-size: 0.85rem;
    opacity: 0.8;
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

/* Tooltip Styles */
[data-bs-toggle="tooltip"] {
    cursor: help;
}

.breakdown-hint {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid #e9ecef;
}

.breakdown-hint small {
    font-size: 0.7rem;
}
</style>

<script>
// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection

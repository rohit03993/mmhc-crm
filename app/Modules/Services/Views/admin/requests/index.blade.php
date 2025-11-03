@extends('auth::layout')

@section('title', 'Service Requests Management - MMHC CRM')

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
            --danger-gradient: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="modern-page-header">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div>
                        <h1 class="page-title">Service Requests Management</h1>
                        <p class="page-subtitle">Manage and assign healthcare service requests</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-light btn-sm" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Print
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards - Modern Design -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="modern-stat-card stat-primary-modern">
                <div class="stat-icon-modern">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-content-modern">
                    <div class="stat-value-modern">{{ $stats['total_requests'] }}</div>
                    <div class="stat-label-modern">Total Requests</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="modern-stat-card stat-warning-modern">
                <div class="stat-icon-modern">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content-modern">
                    <div class="stat-value-modern">{{ $stats['pending_requests'] }}</div>
                    <div class="stat-label-modern">Pending</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="modern-stat-card stat-info-modern">
                <div class="stat-icon-modern">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-content-modern">
                    <div class="stat-value-modern">{{ $stats['assigned_requests'] }}</div>
                    <div class="stat-label-modern">Assigned</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="modern-stat-card stat-success-modern">
                <div class="stat-icon-modern">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content-modern">
                    <div class="stat-value-modern">{{ $stats['completed_requests'] }}</div>
                    <div class="stat-label-modern">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="modern-stat-card stat-danger-modern">
                <div class="stat-icon-modern">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="stat-content-modern">
                    <div class="stat-value-modern">{{ $stats['pending_approvals'] }}</div>
                    <div class="stat-label-modern">Pending Approvals</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="modern-stat-card stat-secondary-modern">
                <div class="stat-icon-modern">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="stat-content-modern">
                    <div class="stat-value-modern">{{ $stats['in_progress_requests'] }}</div>
                    <div class="stat-label-modern">In Progress</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs - Modern Design -->
    <div class="modern-tabs-container mb-4">
        <ul class="modern-nav-tabs" role="tablist">
            <li class="modern-nav-item">
                <button class="modern-nav-link active" data-bs-toggle="tab" data-bs-target="#all" type="button">
                    <span class="tab-icon"><i class="fas fa-list"></i></span>
                    <span class="tab-text">All Requests</span>
                    <span class="tab-badge">{{ $stats['total_requests'] }}</span>
                </button>
            </li>
            <li class="modern-nav-item">
                <button class="modern-nav-link" data-bs-toggle="tab" data-bs-target="#pending" type="button">
                    <span class="tab-icon"><i class="fas fa-clock"></i></span>
                    <span class="tab-text">Pending</span>
                    <span class="tab-badge badge-warning">{{ $stats['pending_requests'] }}</span>
                </button>
            </li>
            <li class="modern-nav-item">
                <button class="modern-nav-link" data-bs-toggle="tab" data-bs-target="#assigned" type="button">
                    <span class="tab-icon"><i class="fas fa-user-check"></i></span>
                    <span class="tab-text">Assigned</span>
                    <span class="tab-badge badge-info">{{ $stats['assigned_requests'] }}</span>
                </button>
            </li>
            <li class="modern-nav-item">
                <button class="modern-nav-link" data-bs-toggle="tab" data-bs-target="#in-progress" type="button">
                    <span class="tab-icon"><i class="fas fa-play-circle"></i></span>
                    <span class="tab-text">In Progress</span>
                    <span class="tab-badge badge-primary">{{ $stats['in_progress_requests'] }}</span>
                </button>
            </li>
            <li class="modern-nav-item">
                <button class="modern-nav-link" data-bs-toggle="tab" data-bs-target="#completed" type="button">
                    <span class="tab-icon"><i class="fas fa-check-circle"></i></span>
                    <span class="tab-text">Completed</span>
                    <span class="tab-badge badge-success">{{ $stats['completed_requests'] }}</span>
                </button>
            </li>
        </ul>
    </div>

    <!-- Service Requests Table - Modern Design -->
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>Service Requests
                </h5>
                <div class="table-actions">
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="modern-card-body p-0">
            @if($serviceRequests->count() > 0)
                <div class="table-container-modern">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Service Type</th>
                                <th>Duration</th>
                                <th>Patient Charge</th>
                                <th>Staff Payout</th>
                                <th>Status</th>
                                <th>Assigned Staff</th>
                                <th>Start Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serviceRequests as $request)
                            <tr class="table-row-modern">
                                <td>
                                    <div class="table-cell-patient">
                                        <div class="patient-avatar-table">
                                            <i class="fas fa-user-injured"></i>
                                        </div>
                                        <div class="patient-info-table">
                                            <div class="patient-name-table">{{ $request->patient->name }}</div>
                                            <div class="patient-id-table">{{ $request->patient->unique_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="service-type-cell">
                                        <div class="service-type-name">{{ $request->serviceType->name }}</div>
                                        <div class="service-type-duration">{{ $request->serviceType->duration_hours }}h service</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="duration-cell">
                                        <div class="duration-days">{{ $request->duration_days }} days</div>
                                        <div class="duration-dates">{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d') }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="amount-cell amount-charge">
                                        <i class="fas fa-rupee-sign me-1"></i>
                                        {{ number_format($request->total_amount) }}
                                    </div>
                                </td>
                                <td>
                                    @if($request->total_staff_payout)
                                        <div class="amount-cell amount-payout">
                                            <i class="fas fa-rupee-sign me-1"></i>
                                            {{ number_format($request->total_staff_payout) }}
                                        </div>
                                        @if($request->assignedStaff)
                                            <div class="staff-type-badge">{{ $request->assignedStaff->isNurse() ? 'Nurse' : 'Caregiver' }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted small">Not calculated</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="status-cell">
                                        <span class="status-badge-modern status-{{ $request->status }}">
                                            <i class="fas fa-{{ $request->status === 'pending' ? 'clock' : ($request->status === 'assigned' ? 'user-check' : ($request->status === 'in_progress' ? 'play-circle' : 'check-circle')) }} me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                        </span>
                                        @if($request->status === 'completed')
                                            @if($request->isApprovedByAdmin())
                                                <div class="approval-badge approved">
                                                    <i class="fas fa-check-circle me-1"></i>Payment Approved
                                                </div>
                                                @if($request->approvedBy)
                                                    <div class="approver-name">{{ $request->approvedBy->name }}</div>
                                                @endif
                                            @else
                                                <div class="approval-badge pending">
                                                    <i class="fas fa-clock me-1"></i>Pending Approval
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($request->assignedStaff)
                                        <div class="staff-cell">
                                            <div class="staff-icon {{ $request->assignedStaff->isNurse() ? 'nurse-icon' : 'caregiver-icon' }}">
                                                <i class="fas fa-user-{{ $request->assignedStaff->isNurse() ? 'nurse' : 'md' }}"></i>
                                            </div>
                                            <div class="staff-info">
                                                <div class="staff-name">{{ $request->assignedStaff->name }}</div>
                                                <div class="staff-role">{{ ucfirst($request->assignedStaff->role) }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="date-cell">{{ $request->start_date->format('M d, Y') }}</div>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        @if($request->status === 'pending' || $request->status === 'assigned')
                                            <a href="{{ route('admin.service-requests.assign', $request) }}" 
                                               class="btn-action btn-assign" title="Assign Staff">
                                                <i class="fas fa-user-plus"></i>
                                            </a>
                                        @endif
                                        
                                        @if($request->status === 'completed' && !$request->isApprovedByAdmin())
                                            <form action="{{ route('admin.service-requests.approve-payment', $request) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Approve payment of ₹{{ number_format($request->total_staff_payout ?? 0) }} to {{ $request->assignedStaff->name ?? 'staff' }}?');">
                                                @csrf
                                                <button type="submit" class="btn-action btn-approve" title="Approve Payment">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <a href="{{ route('services.show', $request) }}" 
                                           class="btn-action btn-view" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="pagination-container-modern">
                    {{ $serviceRequests->links() }}
                </div>
            @else
                <div class="empty-state-modern">
                    <div class="empty-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h4 class="empty-title">No Service Requests</h4>
                    <p class="empty-text">No service requests have been submitted yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Comprehensive Modern Styling -->
<style>
/* Page Header */
.modern-page-header {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-subtitle {
    color: #6c757d;
    margin: 0.5rem 0 0 0;
    font-size: 0.95rem;
}

/* Modern Stat Cards */
.modern-stat-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.modern-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.stat-primary-modern { border-left-color: #667eea; }
.stat-warning-modern { border-left-color: #f093fb; }
.stat-info-modern { border-left-color: #3498db; }
.stat-success-modern { border-left-color: #11998e; }
.stat-danger-modern { border-left-color: #eb3349; }
.stat-secondary-modern { border-left-color: #6c757d; }

.stat-icon-modern {
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

.stat-primary-modern .stat-icon-modern { background: var(--primary-gradient); }
.stat-warning-modern .stat-icon-modern { background: var(--warning-gradient); }
.stat-info-modern .stat-icon-modern { background: var(--info-gradient); }
.stat-success-modern .stat-icon-modern { background: var(--success-gradient); }
.stat-danger-modern .stat-icon-modern { background: var(--danger-gradient); }
.stat-secondary-modern .stat-icon-modern { background: #6c757d; }

.stat-content-modern {
    flex: 1;
}

.stat-value-modern {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1;
    margin-bottom: 0.3rem;
}

.stat-label-modern {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Modern Tabs */
.modern-tabs-container {
    background: white;
    border-radius: 16px;
    padding: 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.modern-nav-tabs {
    display: flex;
    gap: 0.5rem;
    list-style: none;
    padding: 0;
    margin: 0;
    flex-wrap: wrap;
}

.modern-nav-item {
    flex: 1;
    min-width: 120px;
}

.modern-nav-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: #f8f9fa;
    border: none;
    border-radius: 10px;
    color: #6c757d;
    text-decoration: none;
    transition: all 0.3s ease;
    width: 100%;
    justify-content: center;
}

.modern-nav-link:hover,
.modern-nav-link.active {
    background: var(--primary-gradient);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
}

.tab-icon {
    font-size: 1rem;
}

.tab-text {
    font-size: 0.85rem;
    font-weight: 600;
}

.tab-badge {
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.modern-nav-link:not(.active) .tab-badge {
    background: #dee2e6;
    color: #6c757d;
}

/* Modern Card */
.modern-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.modern-card-header {
    padding: 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.modern-card-body {
    padding: 0;
}

/* Modern Table */
.table-container-modern {
    overflow-x: auto;
}

.table-modern {
    width: 100%;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.table-modern thead {
    background: #f8f9fa;
}

.table-modern th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
    white-space: nowrap;
}

.table-row-modern {
    transition: all 0.2s ease;
    border-bottom: 1px solid #f0f0f0;
}

.table-row-modern:hover {
    background: #f8f9fa;
    transform: scale(1.01);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.table-row-modern td {
    padding: 1rem;
    vertical-align: middle;
}

/* Table Cell Styles */
.table-cell-patient {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.patient-avatar-table {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
}

.patient-name-table {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.9rem;
}

.patient-id-table {
    font-size: 0.75rem;
    color: #6c757d;
}

.service-type-cell,
.duration-cell {
    display: flex;
    flex-direction: column;
}

.service-type-name,
.duration-days {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.9rem;
}

.service-type-duration,
.duration-dates {
    font-size: 0.75rem;
    color: #6c757d;
}

.amount-cell {
    font-weight: 700;
    font-size: 1rem;
}

.amount-charge {
    color: #28a745;
}

.amount-payout {
    color: #3498db;
}

.staff-type-badge {
    display: inline-block;
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.2rem 0.5rem;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-top: 0.3rem;
}

.status-cell {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.status-badge-modern {
    display: inline-flex;
    align-items: center;
    padding: 0.4rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-assigned {
    background: #d1ecf1;
    color: #0c5460;
}

.status-in_progress {
    background: #cce5ff;
    color: #004085;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.approval-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
}

.approval-badge.approved {
    background: #d4edda;
    color: #155724;
}

.approval-badge.pending {
    background: #fff3cd;
    color: #856404;
}

.approver-name {
    font-size: 0.7rem;
    color: #6c757d;
    margin-top: 0.2rem;
}

.staff-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.staff-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9rem;
}

.nurse-icon {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.caregiver-icon {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.staff-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.85rem;
}

.staff-role {
    font-size: 0.7rem;
    color: #6c757d;
}

.date-cell {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.85rem;
}

.actions-cell {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-action {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-assign {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.btn-approve {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.btn-view {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    color: white;
}

/* Empty State */
.empty-state-modern {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    font-size: 5rem;
    color: #dee2e6;
    margin-bottom: 1.5rem;
}

.empty-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.empty-text {
    color: #6c757d;
    margin: 0;
}

/* Pagination */
.pagination-container-modern {
    padding: 1.5rem;
    border-top: 1px solid #e9ecef;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .modern-page-header {
        padding: 1.5rem;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .modern-stat-card {
        padding: 1rem;
    }
    
    .stat-value-modern {
        font-size: 1.5rem;
    }
    
    .stat-icon-modern {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .modern-nav-tabs {
        flex-direction: column;
    }
    
    .modern-nav-item {
        min-width: 100%;
    }
    
    .table-modern {
        font-size: 0.8rem;
    }
    
    .table-modern th,
    .table-row-modern td {
        padding: 0.75rem 0.5rem;
    }
}

@media (max-width: 576px) {
    .page-title {
        font-size: 1.25rem;
    }
    
    .stat-value-modern {
        font-size: 1.3rem;
    }
    
    .table-modern {
        font-size: 0.75rem;
    }
}
</style>
@endsection

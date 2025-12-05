@extends('auth::layout')

@section('title', 'Staff Dashboard - MMHC CRM')

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
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }
    </style>
@endsection

@push('scripts')
<script>
    function copyReferralLink() {
        const referralLinkInput = document.getElementById('referralLink');
        referralLinkInput.select();
        referralLinkInput.setSelectionRange(0, 99999); // For mobile devices
        
        try {
            document.execCommand('copy');
            
            // Show success message
            const button = event.target.closest('button');
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.remove('btn-outline-primary');
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-primary');
            }, 2000);
        } catch (err) {
            console.error('Failed to copy: ', err);
            alert('Failed to copy referral link. Please copy manually.');
        }
    }
</script>
@endpush

@section('content')
<!-- Mobile App View for Staff Dashboard -->
<div class="mobile-app-container">
    <!-- App Header (Mobile Only) -->
    <div class="app-header-mobile d-md-none">
        <div class="app-header-content">
            <div class="app-header-left">
                <div class="app-user-avatar staff-{{ auth()->user()->isNurse() ? 'nurse' : 'caregiver' }}">
                    <i class="fas fa-user-{{ auth()->user()->isNurse() ? 'nurse' : 'md' }}"></i>
                </div>
                <div class="app-user-info">
                    <div class="app-user-name">{{ Str::limit(auth()->user()->name, 15) }}</div>
                    <div class="app-user-id">{{ auth()->user()->unique_id }}</div>
                </div>
            </div>
            <div class="app-header-right">
                <a href="{{ route('profile.edit') }}" class="app-header-icon">
                    <i class="fas fa-user-circle"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="app-content">
        <!-- Desktop Header -->
        <div class="d-none d-md-block mb-4">
            <div class="staff-header-card mb-3">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="staff-avatar-large me-3">
                                <i class="fas fa-user-{{ auth()->user()->isNurse() ? 'nurse' : 'md' }} fa-2x"></i>
                            </div>
                            <div>
                                <h2 class="staff-name mb-1">{{ auth()->user()->name }}</h2>
                                <p class="staff-subtitle mb-0">
                                    <span class="badge badge-role">{{ auth()->user()->isNurse() ? 'Licensed Nurse' : 'Caregiver' }}</span>
                                    <span class="text-muted ms-2">ID: {{ auth()->user()->unique_id }}</span>
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

    <!-- Statistics Cards - Mobile Optimized -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['total_assignments'] }}</div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['pending_assignments'] }}</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['active_assignments'] }}</div>
                    <div class="stat-label">Active</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['completed_assignments'] }}</div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings Statistics - Redesigned -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="earnings-section-header mb-3">
                <h4 class="earnings-section-title">
                    <i class="fas fa-wallet me-2"></i>Earnings Overview
                </h4>
                <p class="earnings-section-subtitle">Track your earnings and payments</p>
            </div>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="earnings-card-modern earnings-card-approved">
                        <div class="earnings-card-icon-wrapper">
                            <div class="earnings-card-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="earnings-card-content">
                            <div class="earnings-card-label">Total Earnings</div>
                            <div class="earnings-card-value">₹{{ number_format($earningsStats['total_earnings'], 2) }}</div>
                            <div class="earnings-card-badge">Approved & Paid</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="earnings-card-modern earnings-card-pending">
                        <div class="earnings-card-icon-wrapper">
                            <div class="earnings-card-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="earnings-card-content">
                            <div class="earnings-card-label">Pending Approval</div>
                            <div class="earnings-card-value">₹{{ number_format($earningsStats['pending_earnings'], 2) }}</div>
                            <div class="earnings-card-badge">Awaiting Admin</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="earnings-card-modern earnings-card-upcoming">
                        <div class="earnings-card-icon-wrapper">
                            <div class="earnings-card-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                        <div class="earnings-card-content">
                            <div class="earnings-card-label">Upcoming</div>
                            <div class="earnings-card-value">₹{{ number_format($earningsStats['upcoming_earnings'], 2) }}</div>
                            <div class="earnings-card-badge">In Progress</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="earnings-card-modern earnings-card-month">
                        <div class="earnings-card-icon-wrapper">
                            <div class="earnings-card-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                        <div class="earnings-card-content">
                            <div class="earnings-card-label">This Month</div>
                            <div class="earnings-card-value">₹{{ number_format($earningsStats['earnings_this_month'], 2) }}</div>
                            <div class="earnings-card-badge">
                                @if($earningsStats['earnings_last_month'] > 0)
                                    {{ $earningsStats['earnings_this_month'] > $earningsStats['earnings_last_month'] ? '↑' : '↓' }} 
                                    vs Last Month
                                @else
                                    Current Period
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="modern-card">
                <div class="modern-card-body p-3">
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <a href="{{ route('rewards.create') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus-circle me-2"></i>Add Patient Details
                        </a>
                        <a href="{{ route('rewards.index') }}" class="btn btn-outline-warning btn-lg">
                            <i class="fas fa-gift me-2"></i>View All Rewards
                        </a>
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-user-edit me-2"></i>Update Profile
                        </a>
                        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-file-alt me-2"></i>My Documents
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rewards Summary -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-4">
            <div class="reward-card-modern h-100">
                <div class="reward-card-header-modern">
                    <div class="reward-icon-large">
                        <i class="fas fa-gift"></i>
                    </div>
                    <h5 class="reward-title-modern mb-0">My Reward Wallet</h5>
                </div>
                <div class="reward-card-body-modern">
                    <div class="reward-stats-modern">
                        <div class="reward-stat-item">
                            <div class="reward-stat-value">{{ number_format($rewardSummary['points']) }}</div>
                            <div class="reward-stat-label">Points Earned</div>
                        </div>
                        <div class="reward-stat-divider"></div>
                        <div class="reward-stat-item">
                            <div class="reward-stat-value text-success">₹{{ number_format($rewardSummary['amount'], 2) }}</div>
                            <div class="reward-stat-label">Reward Value</div>
                        </div>
                    </div>
                    <div class="reward-action-section">
                        <a href="{{ route('rewards.create') }}" class="btn btn-warning w-100">
                            <i class="fas fa-plus-circle me-2"></i>Add Patient Details
                        </a>
                        <small class="text-muted d-block mt-2 text-center">
                            <i class="fas fa-info-circle me-1"></i>Earn 1 point (₹10) per submission
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-8">
            <div class="reward-card shadow-sm border-0 h-100">
                <div class="reward-card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="reward-title mb-0">
                            <i class="fas fa-list me-2 text-primary"></i>Recent Reward Entries
                        </h5>
                        <a href="{{ route('rewards.index') }}" class="btn btn-outline-secondary btn-sm">
                            View All
                        </a>
                    </div>
                    @if($recentRewards->count())
                        <div class="reward-list">
                            @foreach($recentRewards as $reward)
                                <div class="reward-item">
                                    <div>
                                        <div class="reward-item-title">{{ $reward->patient_name }}</div>
                                        <div class="text-muted small">
                                            @if($reward->patient_age)
                                                Age: {{ $reward->patient_age }} &middot;
                                            @endif
                                            {{ $reward->hospital_name }} &middot; {{ $reward->patient_phone }}
                                            @if($reward->patient_pincode)
                                                &middot; PIN: {{ $reward->patient_pincode }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success">
                                            +{{ $reward->reward_points }} pts
                                        </span>
                                        <div class="text-muted small">
                                            {{ $reward->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state text-center py-4">
                            <div class="empty-state-icon mb-3">
                                <i class="fas fa-gift"></i>
                            </div>
                            <p class="text-muted mb-0">No reward entries yet. Add patient details to earn rewards.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Section -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="reward-card shadow-sm border-0 h-100">
                <div class="reward-card-body">
                    <h5 class="reward-title">
                        <i class="fas fa-share-alt me-2 text-info"></i>Referral Program
                    </h5>
                    <p class="text-muted small mb-3">Share your referral link with nurses and caregivers to earn rewards!</p>
                    
                    <!-- Referral Link -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Your Referral Link:</label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="referralLink" 
                                   value="{{ $referralLink }}" 
                                   readonly>
                            <button class="btn btn-outline-primary" 
                                    type="button" 
                                    onclick="copyReferralLink()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-info-circle me-1"></i>Earn 1 point (₹10) for each successful referral
                        </small>
                    </div>

                    <!-- Referral Stats -->
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <div class="fw-bold text-primary">{{ $referralStats['completed_referrals'] }}</div>
                                <div class="small text-muted">Completed</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <div class="fw-bold text-success">₹{{ number_format($referralStats['total_reward_amount'], 2) }}</div>
                                <div class="small text-muted">Earned</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-lg-6">
            <div class="reward-card shadow-sm border-0 h-100">
                <div class="reward-card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="reward-title mb-0">
                            <i class="fas fa-users me-2 text-success"></i>Recent Referrals
                        </h5>
                    </div>
                    @if($recentReferrals->count())
                        <div class="reward-list">
                            @foreach($recentReferrals as $referral)
                                <div class="reward-item">
                                    <div>
                                        <div class="reward-item-title">
                                            @if($referral->referred)
                                                {{ $referral->referred->name }}
                                            @else
                                                Pending Registration
                                            @endif
                                        </div>
                                        <div class="text-muted small">
                                            Code: {{ $referral->referral_code }} &middot; 
                                            Status: 
                                            <span class="badge bg-{{ $referral->status === 'completed' ? 'success' : ($referral->status === 'pending' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($referral->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        @if($referral->status === 'completed')
                                            <span class="badge bg-success">
                                                +{{ $referral->reward_points }} pts
                                            </span>
                                        @endif
                                        <div class="text-muted small">
                                            {{ $referral->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state text-center py-4">
                            <div class="empty-state-icon mb-3">
                                <i class="fas fa-share-alt"></i>
                            </div>
                            <p class="text-muted mb-0">No referrals yet. Share your link to earn rewards!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Services Section -->
    <div class="row">
        <div class="col-12">
            <div class="services-section-header mb-3">
                <h3 class="section-title">
                    <i class="fas fa-tasks me-2"></i>My Assigned Services
                </h3>
                <p class="section-subtitle text-muted">Manage your healthcare service assignments</p>
            </div>
        </div>
    </div>

    @if($assignedServices->count() > 0)
        <div class="row g-3 g-md-4">
            @foreach($assignedServices as $service)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="service-card-modern">
                    <!-- Service Header -->
                    <div class="service-card-header service-status-{{ $service->status }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="service-type-badge">
                                    <i class="fas fa-{{ $service->status === 'pending_approval' ? 'bell' : ($service->status === 'assigned' ? 'user-check' : ($service->status === 'in_progress' ? 'play-circle' : ($service->status === 'completed' ? 'check-circle' : 'clock'))) }} me-2"></i>
                                    {{ $service->serviceType->name }}
                                </div>
                                <div class="service-status-badge status-{{ $service->status }}">
                                    @if($service->status === 'pending_approval')
                                        <i class="fas fa-exclamation-circle me-1"></i>Action Required
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $service->status)) }}
                                    @endif
                                </div>
                            </div>
                            <div class="service-date">
                                <small>{{ $service->start_date->format('M d') }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Service Body -->
                    <div class="service-card-body">
                        <!-- Patient Info -->
                        <div class="service-info-item">
                            <div class="info-label">
                                <i class="fas fa-user-injured me-2"></i>Patient
                            </div>
                            <div class="info-value">{{ $service->patient->name }}</div>
                            <div class="info-subtext">{{ Str::limit($service->location, 30) }}</div>
                        </div>

                        <!-- Duration -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="service-info-item-compact">
                                    <div class="info-label-small">
                                        <i class="fas fa-calendar-alt me-1"></i>Duration
                                    </div>
                                    <div class="info-value-small">{{ $service->duration_days }} days</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="service-info-item-compact">
                                    <div class="info-label-small">
                                        <i class="fas fa-clock me-1"></i>Hours/Day
                                    </div>
                                    <div class="info-value-small">{{ $service->serviceType->duration_hours }}h</div>
                                </div>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="service-date-range">
                            <i class="fas fa-calendar-check me-2"></i>
                            <span>{{ $service->start_date->format('M d') }} - {{ $service->end_date->format('M d, Y') }}</span>
                        </div>

                        <!-- Earnings Highlight -->
                        <div class="service-earnings">
                            @if($service->status === 'completed' && $service->isApprovedByAdmin())
                                <div class="earnings-main">
                                    <span class="earnings-label">Earned Amount</span>
                                    <span class="earnings-amount earnings-earned">
                                        ₹{{ number_format($service->total_staff_payout ?? 0) }}
                                    </span>
                                </div>
                            @elseif($service->status === 'completed' && !$service->isApprovedByAdmin())
                                <div class="earnings-main">
                                    <span class="earnings-label">Pending Approval</span>
                                    <span class="earnings-amount earnings-pending-approval">
                                        ₹{{ number_format($service->total_staff_payout ?? 0) }}
                                    </span>
                                </div>
                            @elseif($service->status === 'pending_approval')
                                <div class="earnings-main">
                                    <span class="earnings-label">Potential Earnings</span>
                                    <span class="earnings-amount earnings-pending-approval">
                                        ₹{{ number_format($service->total_staff_payout ?? ($service->serviceType->patient_charge * $service->duration_days)) }}
                                    </span>
                                </div>
                                <div class="alert alert-warning mb-2 p-2" style="font-size: 0.85rem; border-radius: 8px;">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>New Booking Request!</strong> Please accept or reject this booking.
                                </div>
                            @else
                                <div class="earnings-main">
                                    <span class="earnings-label">Projected Earnings</span>
                                    <span class="earnings-amount earnings-projected">
                                        ₹{{ number_format($service->total_staff_payout ?? ($service->serviceType->patient_charge * $service->duration_days)) }}
                                    </span>
                                </div>
                            @endif
                            <div class="earnings-breakdown">
                                <div class="breakdown-item">
                                    <span>Daily Rate:</span>
                                    <strong>
                                        @if($service->total_staff_payout)
                                            ₹{{ number_format($service->total_staff_payout / $service->duration_days) }}/day
                                        @else
                                            ₹{{ number_format($service->serviceType->patient_charge / $service->duration_days) }}/day
                                        @endif
                                    </strong>
                                </div>
                                <div class="breakdown-item">
                                    <span>Status:</span>
                                    <strong class="text-{{ $service->status === 'pending_approval' ? 'warning' : ($service->status === 'completed' && $service->isApprovedByAdmin() ? 'success' : ($service->status === 'completed' && !$service->isApprovedByAdmin() ? 'warning' : ($service->status === 'in_progress' ? 'warning' : 'muted'))) }}">
                                        @if($service->status === 'pending_approval')
                                            <i class="fas fa-bell me-1"></i>Action Required
                                        @elseif($service->status === 'completed' && $service->isApprovedByAdmin())
                                            Approved
                                        @elseif($service->status === 'completed' && !$service->isApprovedByAdmin())
                                            Pending Approval
                                        @elseif($service->status === 'in_progress')
                                            In Progress
                                        @elseif($service->status === 'assigned')
                                            Ready to Start
                                        @else
                                            Not Started
                                        @endif
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="service-actions">
                            @if($service->status === 'pending_approval')
                            <!-- Accept/Reject Booking (One-Way Booking) - Prominent Display -->
                            <div class="pending-approval-actions">
                                <div class="pending-approval-header">
                                    <i class="fas fa-bell text-warning me-2"></i>
                                    <strong>New Booking Request</strong>
                                </div>
                                <div class="d-flex flex-column gap-2 mt-3">
                                    <form method="POST" action="{{ route('staff.booking.accept', $service) }}" class="w-100">
                                        @csrf
                                        <button type="submit" class="btn btn-action-success w-100" onclick="return confirm('Accept this booking? You will be assigned to provide service from {{ $service->start_date->format('M d, Y') }} to {{ $service->end_date->format('M d, Y') }}.')">
                                            <i class="fas fa-check-circle me-2"></i>Accept Booking
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-action-danger w-100" onclick="showRejectModal({{ $service->id }})">
                                        <i class="fas fa-times-circle me-2"></i>Reject Booking
                                    </button>
                                </div>
                                
                                <!-- Reject Modal - Enhanced -->
                                <div id="rejectModal{{ $service->id }}" style="display: none;" class="reject-modal-container mt-3">
                                    <div class="reject-modal-header">
                                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                        <strong>Reject Booking Request</strong>
                                    </div>
                                    <form method="POST" action="{{ route('staff.booking.reject', $service) }}" class="mt-3">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Please provide a reason for rejecting this booking:</label>
                                            <textarea name="rejection_reason" 
                                                      class="form-control" 
                                                      rows="4" 
                                                      required 
                                                      placeholder="Example: Already committed to another service, Personal reasons, Date conflicts, etc."></textarea>
                                            <small class="text-muted">This helps us improve our service and find alternative staff for the patient.</small>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-danger flex-fill">
                                                <i class="fas fa-times me-1"></i>Confirm Rejection
                                            </button>
                                            <button type="button" class="btn btn-secondary" onclick="hideRejectModal({{ $service->id }})">
                                                <i class="fas fa-arrow-left me-1"></i>Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @else
                            <a href="{{ route('staff.service-details', $service) }}" 
                               class="btn btn-action-primary">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                            @if($service->status === 'assigned')
                            <button class="btn btn-action-success" onclick="startService({{ $service->id }})">
                                <i class="fas fa-play me-2"></i>Start Service
                            </button>
                            @elseif($service->status === 'in_progress')
                            <button class="btn btn-action-warning" onclick="completeService({{ $service->id }})">
                                <i class="fas fa-check me-2"></i>Mark Complete
                            </button>
                            @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    {{ $assignedServices->links() }}
                </div>
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="empty-state-card">
            <div class="empty-state-icon">
                <i class="fas fa-user-{{ auth()->user()->isNurse() ? 'nurse' : 'md' }}"></i>
            </div>
            <h3 class="empty-state-title">No Assigned Services</h3>
            <p class="empty-state-text">You don't have any service assignments yet. Check back later for new assignments.</p>
            <div class="empty-state-tip">
                <i class="fas fa-lightbulb me-2"></i>
                <strong>Tip:</strong> Keep your profile updated and available to receive more assignments!
            </div>
        </div>
    @endif
    </div>

    <!-- Bottom Navigation Bar (Mobile Only) - Using Shared Component -->
    @include('auth::components.bottom-nav')
</div>

<!-- Comprehensive Mobile-First Styling -->
<style>
/* Mobile App Container */
.mobile-app-container {
    position: relative;
    min-height: 100vh;
    background: #f5f7fa;
    padding-bottom: 140px !important;
    margin-top: 0;
}

/* Ensure all text is visible - Force dark colors on white backgrounds */
.mobile-app-container * {
    color: inherit;
}

.mobile-app-container .text-muted {
    color: #6c757d !important;
    opacity: 1 !important;
}

.mobile-app-container .text-white {
    color: #ffffff !important;
}

.mobile-app-container .text-dark {
    color: #212529 !important;
}

@media (max-width: 767px) {
    .mobile-app-container {
        padding-bottom: 160px !important;
    }
}

/* App Content */
.app-content {
    position: relative;
    padding: 16px;
    padding-bottom: 20px;
}

@media (max-width: 767px) {
    .app-content {
        padding-bottom: 40px;
    }
}

/* App Header Mobile */
.app-header-mobile {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: white;
    border-bottom: 1px solid #e9ecef;
    padding: 12px 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
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

.app-user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.app-user-avatar.staff-nurse {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.app-user-avatar.staff-caregiver {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.app-user-info {
    display: flex;
    flex-direction: column;
}

.app-user-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: #212529 !important;
    line-height: 1.2;
}

.app-user-id {
    font-size: 0.75rem;
    color: #495057 !important;
    line-height: 1.2;
    font-weight: 500;
}

.app-header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.app-header-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #667eea;
    text-decoration: none;
    transition: all 0.2s ease;
}

.app-header-icon:hover,
.app-header-icon:active {
    background: #e9ecef;
    transform: scale(1.05);
}

/* Staff Header Card */
.staff-header-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.staff-avatar-large {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--primary-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.staff-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.staff-subtitle {
    font-size: 0.9rem;
}

.badge-role {
    background: var(--primary-gradient);
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

/* Statistics Cards */
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.12);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: white;
    flex-shrink: 0;
}

.stat-primary .stat-icon {
    background: var(--primary-gradient);
}

.stat-warning .stat-icon {
    background: var(--warning-gradient);
}

.stat-info .stat-icon {
    background: var(--info-gradient);
}

.stat-success .stat-icon {
    background: var(--success-gradient);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #212529 !important;
    line-height: 1;
    margin-bottom: 0.3rem;
}

.stat-label {
    font-size: 0.85rem;
    color: #495057 !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

/* Rewards Card */
.reward-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.12);
    position: relative;
    overflow: hidden;
}

.reward-card::after {
    content: '';
    position: absolute;
    top: -40%;
    right: -60%;
    width: 120%;
    height: 120%;
    background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
}

.reward-card-body {
    position: relative;
    z-index: 2;
}

.reward-title {
    font-weight: 700;
    color: #2c3e50;
}

.reward-stats {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 12px;
    padding: 1rem;
    margin-top: 1rem;
}

.reward-points {
    font-size: 2.2rem;
    font-weight: 700;
    color: #6c5ce7;
    line-height: 1;
}

.reward-amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: #27ae60;
    line-height: 1;
}

.reward-label {
    font-size: 0.85rem;
}

.reward-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.reward-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fb;
    padding: 0.85rem 1rem;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.reward-item:hover {
    background: #eef2ff;
    transform: translateX(4px);
}

/* Service Cards */
.services-section-header {
    padding: 1rem 0;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #212529 !important;
    margin: 0;
}

.section-title i {
    color: #667eea !important;
}

.section-subtitle {
    font-size: 0.9rem;
    margin: 0.3rem 0 0 0;
    color: #495057 !important;
}

.service-card-modern {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.service-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.service-card-header {
    padding: 1rem 1.25rem;
    color: white;
    position: relative;
    overflow: hidden;
}

.service-card-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
}

.service-status-assigned {
    background: var(--info-gradient);
}

.service-status-in_progress {
    background: var(--primary-gradient);
}

.service-status-completed {
    background: var(--success-gradient);
}

.service-status-pending {
    background: var(--warning-gradient);
}

.service-status-pending_approval {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    animation: pulse-warning 2s infinite;
}

@keyframes pulse-warning {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(245, 158, 11, 0);
    }
}

/* Pending Approval Actions - Enhanced UI */
.pending-approval-actions {
    background: linear-gradient(135deg, #fff7ed 0%, #fffbeb 100%);
    border: 2px solid #fbbf24;
    border-radius: 12px;
    padding: 16px;
    margin-top: 12px;
}

.pending-approval-header {
    display: flex;
    align-items: center;
    color: #92400e;
    font-size: 0.95rem;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #fde68a;
}

.reject-modal-container {
    background: #fef2f2;
    border: 2px solid #fecaca;
    border-radius: 12px;
    padding: 16px;
}

.reject-modal-header {
    display: flex;
    align-items: center;
    color: #991b1b;
    font-size: 0.95rem;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #fecaca;
}

.btn-action-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-action-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    color: white;
}

.service-type-badge {
    font-size: 0.95rem;
    font-weight: 600;
    margin-bottom: 0.4rem;
    color: #ffffff !important;
}

.service-type-badge i {
    color: #ffffff !important;
}

.service-status-badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    background: rgba(255,255,255,0.25);
    backdrop-filter: blur(10px);
    color: #ffffff !important;
}

.service-date {
    font-size: 0.85rem;
    opacity: 1 !important;
    color: #ffffff !important;
    font-weight: 500;
}

.service-card-body {
    padding: 1rem 1.25rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.service-info-item {
    margin-bottom: 0.9rem;
}

.info-label {
    font-size: 0.75rem;
    color: #495057 !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.3rem;
    font-weight: 600;
}

.info-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #212529 !important;
    margin-bottom: 0.2rem;
}

.info-subtext {
    font-size: 0.85rem;
    color: #495057 !important;
    font-weight: 500;
}

.service-info-item-compact {
    background: #f8f9fa;
    padding: 0.7rem;
    border-radius: 8px;
}

.info-label-small {
    font-size: 0.7rem;
    color: #495057 !important;
    margin-bottom: 0.2rem;
    font-weight: 600;
}

.info-value-small {
    font-size: 0.95rem;
    font-weight: 700;
    color: #212529 !important;
}

.service-date-range {
    background: #f8f9fa;
    padding: 0.8rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #212529 !important;
    text-align: center;
    font-weight: 500;
}

.service-date-range i {
    color: #667eea !important;
}

.service-earnings {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.2rem;
    border-radius: 12px;
    margin-bottom: 1.2rem;
}

.earnings-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.8rem;
    padding-bottom: 0.8rem;
    border-bottom: 2px solid #dee2e6;
}

.earnings-label {
    font-size: 0.85rem;
    color: #6c757d;
    text-transform: uppercase;
}

.earnings-amount {
    font-size: 1.5rem;
    font-weight: 700;
}

.earnings-earned {
    color: #28a745;
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

.earnings-breakdown {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.breakdown-item {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #212529 !important;
    font-weight: 500;
}

.breakdown-item span {
    color: #495057 !important;
}

.breakdown-item strong {
    color: #212529 !important;
    font-weight: 700;
}

.service-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: auto;
}

.btn-action-primary,
.btn-action-success,
.btn-action-warning {
    flex: 1;
    padding: 0.7rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    border: none;
    transition: all 0.3s ease;
}

.btn-action-primary {
    background: var(--primary-gradient);
    color: white;
}

.btn-action-success {
    background: var(--success-gradient);
    color: white;
}

.btn-action-warning {
    background: var(--warning-gradient);
    color: white;
}

.btn-action-primary:hover,
.btn-action-success:hover,
.btn-action-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Enhanced Reward Card */
.reward-card-modern {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
}

.reward-card-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}

.reward-card-header-modern {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    padding: 1.5rem;
    text-align: center;
    color: white;
}

.reward-icon-large {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    margin: 0 auto 1rem;
}

.reward-title-modern {
    font-size: 1.1rem;
    font-weight: 700;
    color: white;
}

.reward-card-body-modern {
    padding: 1.5rem;
}

.reward-stats-modern {
    display: flex;
    align-items: center;
    justify-content: space-around;
    padding: 1.5rem 0;
    margin-bottom: 1rem;
}

.reward-stat-item {
    text-align: center;
    flex: 1;
}

.reward-stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.reward-stat-label {
    font-size: 0.85rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.reward-stat-divider {
    width: 1px;
    height: 50px;
    background: #dee2e6;
}

.reward-action-section {
    margin-top: 1rem;
}

/* Empty State */
.empty-state-card {
    background: white;
    border-radius: 16px;
    padding: 3rem 2rem;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.empty-state-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    background: var(--primary-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: white;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.empty-state-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #212529 !important;
    margin-bottom: 0.8rem;
}

.empty-state-text {
    color: #495057 !important;
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
    font-weight: 500;
}

.empty-state-tip {
    background: #e7f3ff;
    padding: 1rem;
    border-radius: 8px;
    color: #212529 !important;
    max-width: 500px;
    margin: 0 auto;
    font-weight: 500;
}

.empty-state-tip i {
    color: #667eea !important;
}

.empty-state-tip strong {
    color: #212529 !important;
    font-weight: 700;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .container-fluid {
        padding: 1rem;
    }
    
    .staff-header-card {
        padding: 1rem;
    }
    
    .staff-name {
        font-size: 1.2rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.1rem;
    }
    
    .service-card-body {
        padding: 1.2rem;
    }
    
    .earnings-amount {
        font-size: 1.2rem;
    }
    
    .service-actions {
        flex-direction: column;
    }
    
    .btn-action-primary,
    .btn-action-success,
    .btn-action-warning {
        width: 100%;
    }
    
    .empty-state-card {
        padding: 2rem 1rem;
    }
    
    .empty-state-icon {
        width: 80px;
        height: 80px;
        font-size: 2.5rem;
    }
}

/* Earnings Section Header */
.earnings-section-header {
    padding: 0 0.5rem;
    margin-bottom: 1rem;
}

.earnings-section-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #212529 !important;
    margin: 0 0 0.25rem 0;
    display: flex;
    align-items: center;
}

.earnings-section-title i {
    color: #667eea !important;
    font-size: 1.3rem;
}

.earnings-section-subtitle {
    font-size: 0.9rem;
    color: #6c757d !important;
    margin: 0;
    font-weight: 500;
}

/* Modern Earnings Cards - Redesigned */
.earnings-card-modern {
    background: white;
    border-radius: 16px;
    padding: 1.5rem 1.25rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 2px solid transparent;
    height: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}

.earnings-card-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, transparent 0%, currentColor 50%, transparent 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.earnings-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: currentColor;
}

.earnings-card-modern:hover::before {
    opacity: 1;
}

.earnings-card-icon-wrapper {
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: flex-start;
}

.earnings-card-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.earnings-card-approved {
    color: #28a745;
}

.earnings-card-approved .earnings-card-icon {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.earnings-card-pending {
    color: #ffc107;
}

.earnings-card-pending .earnings-card-icon {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
}

.earnings-card-upcoming {
    color: #17a2b8;
}

.earnings-card-upcoming .earnings-card-icon {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}

.earnings-card-month {
    color: #667eea;
}

.earnings-card-month .earnings-card-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.earnings-card-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.earnings-card-label {
    font-size: 0.8rem;
    color: #6c757d !important;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 0.75rem;
    line-height: 1.2;
}

.earnings-card-value {
    font-size: 1.6rem;
    font-weight: 700;
    color: #212529 !important;
    margin-bottom: 0.5rem;
    line-height: 1.2;
    word-break: break-word;
}

.earnings-card-badge {
    font-size: 0.75rem;
    color: #6c757d !important;
    font-weight: 500;
    padding: 0.35rem 0.75rem;
    background: #f8f9fa;
    border-radius: 20px;
    display: inline-block;
    width: fit-content;
    margin-top: auto;
}

@media (max-width: 576px) {
    .earnings-card-modern {
        padding: 1.25rem 1rem;
    }
    
    .earnings-card-icon {
        width: 48px;
        height: 48px;
        font-size: 1.5rem;
    }
    
    .earnings-card-value {
        font-size: 1.4rem;
    }
    
    .earnings-card-label {
        font-size: 0.75rem;
        margin-bottom: 0.5rem;
    }
    
    .earnings-section-title {
        font-size: 1.2rem;
    }
}

/* Modern Card Header - Ensure text visibility */
.modern-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.modern-card-header.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.modern-card-header.bg-gradient-primary.text-white h5 {
    color: #ffffff !important;
    font-weight: 700;
}

.modern-card-header.bg-gradient-primary.text-white i {
    color: #ffffff !important;
}

.earnings-content {
    flex: 1;
}

.earnings-label {
    font-size: 0.85rem;
    color: #495057 !important;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.earnings-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #212529 !important;
    margin-bottom: 0.25rem;
}

.earnings-stat-card small {
    color: #6c757d !important;
    font-weight: 500;
}

@media (max-width: 576px) {
    .staff-avatar-large {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    
    .staff-name {
        font-size: 1.1rem;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-value {
        font-size: 1.3rem;
    }
    
    .earnings-stat-card {
        padding: 1rem;
    }
    
    .earnings-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
    }
    
    .earnings-value {
        font-size: 1.25rem;
    }
    
    .service-card-header {
        padding: 1rem;
    }
    
    .section-title {
        font-size: 1.2rem;
    }
}
</style>

<script>
function showRejectModal(serviceId) {
    const modal = document.getElementById('rejectModal' + serviceId);
    if (modal) {
        modal.style.display = 'block';
        // Scroll to modal
        modal.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function hideRejectModal(serviceId) {
    const modal = document.getElementById('rejectModal' + serviceId);
    if (modal) {
        modal.style.display = 'none';
        // Clear form
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
        }
    }
}

function startService(serviceId) {
    if (confirm('Are you sure you want to start this service?')) {
        // AJAX request to start service will be implemented
        fetch(`/staff/service/${serviceId}/start`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to start service');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Service start feature will be implemented soon.');
        });
    }
}

function completeService(serviceId) {
    if (confirm('Are you sure you want to mark this service as completed?')) {
        // AJAX request to complete service will be implemented
        fetch(`/staff/service/${serviceId}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to complete service');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Service completion feature will be implemented soon.');
        });
    }
}

// Mobile menu toggle is handled by the shared bottom-nav component
</script>
@endsection

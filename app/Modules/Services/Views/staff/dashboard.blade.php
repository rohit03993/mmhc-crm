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
<div class="container-fluid px-3 px-md-4 py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
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

    <!-- Rewards Summary -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-4">
            <div class="reward-card shadow-sm border-0 h-100">
                <div class="reward-card-body">
                    <h5 class="reward-title">
                        <i class="fas fa-gift me-2 text-warning"></i>My Reward Wallet
                    </h5>
                    <div class="reward-stats d-flex justify-content-between align-items-center">
                        <div>
                            <div class="reward-points">{{ number_format($rewardSummary['points']) }}</div>
                            <div class="reward-label text-muted">Points Earned</div>
                        </div>
                        <div class="text-end">
                            <div class="reward-amount">₹{{ number_format($rewardSummary['amount'], 2) }}</div>
                            <div class="reward-label text-muted">Reward Value</div>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('rewards.create') }}" class="mt-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus-circle me-2"></i>Add Patient Details
                        </button>
                    </form>
                    <small class="text-muted d-block mt-2">Earn 1 point (₹10) for each successful submission.</small>
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
                                    <i class="fas fa-{{ $service->status === 'assigned' ? 'user-check' : ($service->status === 'in_progress' ? 'play-circle' : ($service->status === 'completed' ? 'check-circle' : 'clock')) }} me-2"></i>
                                    {{ $service->serviceType->name }}
                                </div>
                                <div class="service-status-badge status-{{ $service->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $service->status)) }}
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
                                    <strong class="text-{{ $service->status === 'completed' && $service->isApprovedByAdmin() ? 'success' : ($service->status === 'completed' && !$service->isApprovedByAdmin() ? 'warning' : ($service->status === 'in_progress' ? 'warning' : 'muted')) }}">
                                        @if($service->status === 'completed' && $service->isApprovedByAdmin())
                                            Approved
                                        @elseif($service->status === 'completed' && !$service->isApprovedByAdmin())
                                            Pending Approval
                                        @elseif($service->status === 'in_progress')
                                            In Progress
                                        @else
                                            Not Started
                                        @endif
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="service-actions">
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

<!-- Comprehensive Mobile-First Styling -->
<style>
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
    color: #2c3e50;
    line-height: 1;
    margin-bottom: 0.3rem;
}

.stat-label {
    font-size: 0.85rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
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
    color: #2c3e50;
    margin: 0;
}

.section-subtitle {
    font-size: 0.9rem;
    margin: 0.3rem 0 0 0;
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
    padding: 1.2rem;
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

.service-type-badge {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.service-status-badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    background: rgba(255,255,255,0.25);
    backdrop-filter: blur(10px);
}

.service-date {
    font-size: 0.85rem;
    opacity: 0.9;
}

.service-card-body {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.service-info-item {
    margin-bottom: 1.2rem;
}

.info-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.3rem;
}

.info-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.2rem;
}

.info-subtext {
    font-size: 0.85rem;
    color: #6c757d;
}

.service-info-item-compact {
    background: #f8f9fa;
    padding: 0.7rem;
    border-radius: 8px;
}

.info-label-small {
    font-size: 0.7rem;
    color: #6c757d;
    margin-bottom: 0.2rem;
}

.info-value-small {
    font-size: 0.95rem;
    font-weight: 700;
    color: #2c3e50;
}

.service-date-range {
    background: #f8f9fa;
    padding: 0.8rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #495057;
    text-align: center;
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
    color: #495057;
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
    color: #2c3e50;
    margin-bottom: 0.8rem;
}

.empty-state-text {
    color: #6c757d;
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.empty-state-tip {
    background: #e7f3ff;
    padding: 1rem;
    border-radius: 8px;
    color: #495057;
    max-width: 500px;
    margin: 0 auto;
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
    
    .service-card-header {
        padding: 1rem;
    }
    
    .section-title {
        font-size: 1.2rem;
    }
}
</style>

<script>
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
</script>
@endsection

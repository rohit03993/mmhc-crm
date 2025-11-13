@extends('auth::layout')

@section('title', 'Referral Management - Admin Dashboard')

@section('head')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 1.5rem;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .stat-card.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .stat-card.info {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }
        .stat-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .referral-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .referral-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .badge-referral {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .table-referrals {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        .table-referrals thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .performance-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .performance-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        .avatar-circle {
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .referral-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .top-referrer-badge {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            display: inline-block;
            font-weight: bold;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-share-alt me-2 text-primary"></i>
                        Referral Management
                    </h2>
                    <p class="text-muted mb-0">Track and manage all referral activities</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $overallStats['total_staff_with_referrals'] }}</div>
                <div class="stat-label">
                    <i class="fas fa-users me-2"></i>Staff with Referrals
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card success">
                <div class="stat-value">{{ $overallStats['completed_referrals'] }}</div>
                <div class="stat-label">
                    <i class="fas fa-check-circle me-2"></i>Completed Referrals
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card info">
                <div class="stat-value">{{ $overallStats['total_reward_points'] }}</div>
                <div class="stat-label">
                    <i class="fas fa-gift me-2"></i>Total Reward Points
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card warning">
                <div class="stat-value">₹{{ number_format($overallStats['total_reward_amount'], 2) }}</div>
                <div class="stat-label">
                    <i class="fas fa-rupee-sign me-2"></i>Total Reward Amount
                </div>
            </div>
        </div>
    </div>

    <!-- Top Referrer Highlight -->
    @if($overallStats['top_referrer'])
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-2">
                                    <i class="fas fa-trophy me-2"></i>Top Referrer
                                </h5>
                                <h4 class="mb-1">{{ $overallStats['top_referrer']->name }}</h4>
                                <p class="mb-0 opacity-75">
                                    {{ ucfirst($overallStats['top_referrer']->role) }} &middot; 
                                    {{ $overallStats['top_referrer']->completed_referrals }} completed referrals &middot; 
                                    ₹{{ number_format($overallStats['top_referrer']->total_reward_amount, 2) }} earned
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <a href="{{ route('admin.referrals.staff', $overallStats['top_referrer']->id) }}" class="btn btn-light btn-lg">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" action="{{ route('admin.referrals.index') }}" class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">Filter by Staff Member</label>
                <select name="staff_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Staff Members</option>
                    @foreach($staffWithReferrals as $staff)
                        <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                            {{ $staff->name }} ({{ $staff->role }}) - {{ $staff->completed_referrals }} referrals
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                @if(request('staff_id'))
                    <a href="{{ route('admin.referrals.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Clear Filter
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Staff Performance Cards -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Staff Referral Performance
                    </h5>
                    <span class="badge bg-light text-primary">{{ $staffWithReferrals->count() }} Staff Members</span>
                </div>
                <div class="card-body">
                    @if($staffWithReferrals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;" class="text-center">Rank</th>
                                        <th style="width: 20%;">Staff Member</th>
                                        <th style="width: 10%;">Role</th>
                                        <th style="width: 15%;">Referral Code</th>
                                        <th style="width: 10%;" class="text-center">Completed</th>
                                        <th style="width: 10%;" class="text-center">Pending</th>
                                        <th style="width: 10%;" class="text-center">Reward Points</th>
                                        <th style="width: 15%;" class="text-center">Reward Amount</th>
                                        <th style="width: 10%;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($staffWithReferrals as $index => $staff)
                                        <tr class="{{ $index === 0 ? 'table-warning' : '' }}">
                                            <td class="text-center">
                                                @if($index === 0)
                                                    <span class="badge bg-warning text-dark" style="font-size: 1rem;">
                                                        <i class="fas fa-trophy me-1"></i>#1
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">#{{ $index + 1 }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2" style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.1rem;">
                                                        {{ strtoupper(substr($staff->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $staff->name }}</div>
                                                        <small class="text-muted">ID: {{ $staff->unique_id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $staff->role === 'nurse' ? 'info' : 'success' }} px-3 py-2">
                                                    <i class="fas fa-{{ $staff->role === 'nurse' ? 'user-nurse' : 'user-md' }} me-1"></i>
                                                    {{ ucfirst($staff->role) }}
                                                </span>
                                            </td>
                                            <td>
                                                <code class="bg-light p-2 rounded d-inline-block" style="font-size: 0.85rem;">{{ $staff->referral_code }}</code>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success px-3 py-2" style="font-size: 1rem;">
                                                    <i class="fas fa-check-circle me-1"></i>{{ $staff->completed_referrals }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-warning px-3 py-2" style="font-size: 1rem;">
                                                    <i class="fas fa-clock me-1"></i>{{ $staff->pending_referrals }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold text-primary" style="font-size: 1.1rem;">
                                                    <i class="fas fa-gift me-1"></i>{{ $staff->total_reward_points }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold text-success" style="font-size: 1.1rem;">
                                                    <i class="fas fa-rupee-sign me-1"></i>{{ number_format($staff->total_reward_amount, 2) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.referrals.staff', $staff->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-share-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No referral activities yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- All Referrals Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        All Referrals
                        @if($selectedStaff)
                            <small class="ms-2">- {{ $selectedStaff->name }}</small>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if($allReferrals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-referrals table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Referrer (Who Shared)</th>
                                        <th style="width: 20%;">Referred User (Who Registered)</th>
                                        <th style="width: 12%;">Referral Code</th>
                                        <th style="width: 10%;" class="text-center">Status</th>
                                        <th style="width: 12%;" class="text-center">Reward Points</th>
                                        <th style="width: 13%;" class="text-center">Reward Amount</th>
                                        <th style="width: 13%;" class="text-center">Completed At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allReferrals as $referral)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.9rem;">
                                                        {{ strtoupper(substr($referral->referrer->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $referral->referrer->name }}</div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-{{ $referral->referrer->role === 'nurse' ? 'user-nurse' : 'user-md' }} me-1"></i>
                                                            {{ ucfirst($referral->referrer->role) }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($referral->referred)
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle me-2" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.9rem;">
                                                            {{ strtoupper(substr($referral->referred->name, 0, 2)) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold">{{ $referral->referred->name }}</div>
                                                            <small class="text-muted">
                                                                <i class="fas fa-{{ $referral->referred->role === 'nurse' ? 'user-nurse' : 'user-md' }} me-1"></i>
                                                                {{ ucfirst($referral->referred->role) }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <code class="bg-light p-2 rounded d-inline-block" style="font-size: 0.85rem;">{{ $referral->referral_code }}</code>
                                            </td>
                                            <td class="text-center">
                                                @if($referral->status === 'completed')
                                                    <span class="badge bg-success px-3 py-2">
                                                        <i class="fas fa-check-circle me-1"></i>Completed
                                                    </span>
                                                @elseif($referral->status === 'pending')
                                                    <span class="badge bg-warning px-3 py-2">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary px-3 py-2">
                                                        <i class="fas fa-times-circle me-1"></i>Cancelled
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold text-primary" style="font-size: 1.1rem;">
                                                    <i class="fas fa-gift me-1"></i>{{ $referral->reward_points }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold text-success" style="font-size: 1.1rem;">
                                                    <i class="fas fa-rupee-sign me-1"></i>{{ number_format($referral->reward_amount, 2) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($referral->completed_at)
                                                    <div class="fw-bold">{{ $referral->completed_at->format('M d, Y') }}</div>
                                                    <small class="text-muted">{{ $referral->completed_at->format('h:i A') }}</small>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $allReferrals->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No referrals found.</p>
                            @if($selectedStaff)
                                <a href="{{ route('admin.referrals.index') }}" class="btn btn-primary mt-2">
                                    View All Referrals
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


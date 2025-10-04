@extends('auth::layout')

@section('title', 'Dashboard - MMHC CRM')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Welcome Card -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="card-title mb-1">
                            Welcome back, {{ $user->name }}!
                        </h3>
                        <p class="card-text text-muted">
                            {{ $user->unique_id }} • {{ ucfirst($user->role) }}
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="badge bg-primary fs-6">
                            Profile: {{ $stats['profile_completion'] }}% Complete
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $stats['total_requests'] }}</h4>
                        <p class="card-text">Total Requests</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clipboard-list fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $stats['active_requests'] }}</h4>
                        <p class="card-text">Active Requests</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-play-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $stats['completed_requests'] }}</h4>
                        <p class="card-text">Completed</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $stats['profile_completion'] }}%</h4>
                        <p class="card-text">Profile Complete</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    Recent Activity
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">{{ $recent_activity['message'] }}</h6>
                            <p class="timeline-text text-muted">{{ $recent_activity['time'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                @if($user->isCaregiver())
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>
                            New Care Request
                        </button>
                        <button class="btn btn-outline-secondary">
                            <i class="fas fa-calendar me-2"></i>
                            View Schedule
                        </button>
                    </div>
                @elseif($user->isPatient())
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-user-plus me-2"></i>
                            Request Caregiver
                        </button>
                        <button class="btn btn-outline-secondary">
                            <i class="fas fa-file-medical me-2"></i>
                            View Medical Records
                        </button>
                    </div>
                @endif
                
                <hr>
                <div class="d-grid">
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-info">
                        <i class="fas fa-cog me-2"></i>
                        Update Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #007bff;
}

.timeline-content {
    padding-left: 20px;
}

.timeline-title {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
}

.timeline-text {
    font-size: 12px;
}
</style>
@endsection

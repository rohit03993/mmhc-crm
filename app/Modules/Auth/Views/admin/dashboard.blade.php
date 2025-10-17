@extends('auth::layout')

@section('title', 'Admin Dashboard - MMHC CRM')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="row">
    <!-- Welcome Card -->
    <div class="col-12 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="card-title mb-1">
                            Welcome, {{ $user->name }}!
                        </h3>
                        <p class="card-text">
                            Admin Dashboard • {{ $user->unique_id }}
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <i class="fas fa-shield-alt fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $stats['total_users'] }}</h4>
                        <p class="card-text">Total Users</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $stats['total_caregivers'] }}</h4>
                        <p class="card-text">Caregivers</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-nurse fa-2x"></i>
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
                        <h4 class="card-title">{{ $stats['total_patients'] }}</h4>
                        <p class="card-text">Patients</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-injured fa-2x"></i>
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
                        <h4 class="card-title">{{ $stats['pending_approvals'] }}</h4>
                        <p class="card-text">Pending Approvals</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="{{ route('admin.users') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-users me-2"></i>
                            Manage Users
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('admin.page-content.index') }}" class="btn btn-outline-success w-100">
                            <i class="fas fa-edit me-2"></i>
                            Edit Landing Page & Plans
                        </a>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-info w-100">
                            <i class="fas fa-cog me-2"></i>
                            System Settings
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-warning w-100">
                            <i class="fas fa-bell me-2"></i>
                            Notifications
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-md-6 mb-4">
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

    <!-- User Management Preview -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>
                    User Overview
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="border-end">
                            <h4 class="text-primary">{{ $stats['total_caregivers'] }}</h4>
                            <p class="text-muted">Active Caregivers</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border-end">
                            <h4 class="text-success">{{ $stats['total_patients'] }}</h4>
                            <p class="text-muted">Registered Patients</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h4 class="text-info">{{ $stats['total_users'] }}</h4>
                        <p class="text-muted">Total Users</p>
                    </div>
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

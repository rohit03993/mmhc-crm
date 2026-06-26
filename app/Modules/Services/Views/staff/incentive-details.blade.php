@extends('auth::layout')

@section('title', 'Incentive Details - Staff Dashboard')
@section('page-title', 'Incentive Details')

@section('head')
@include('services::partials.mobile-assets')
@include('services::partials.staff-referrals-assets')
@endsection

@section('content')
<div class="mobile-app-container hc-mobile-shell" data-mmhc-ptr>
    <div class="app-mobile-header d-md-none">
        <div class="d-flex align-items-center">
            <a href="{{ $isAdminViewer ? route('admin.users') : route('staff.dashboard') }}" class="btn btn-link text-white p-0 me-3" aria-label="Back">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h5 class="text-white mb-0">Incentive details</h5>
                <small class="text-white opacity-75">{{ $targetStaff->unique_id }}</small>
            </div>
        </div>
    </div>

    <div class="container-fluid px-3 py-4">
        <div class="hc-m-hero d-md-none mb-3">
            <p class="hc-m-hero__label">Earnings</p>
            <h2 class="hc-m-hero__title">{{ $targetStaff->name }}</h2>
            <p class="hc-m-hero__lede">{{ strtoupper($targetStaff->role) }} · breakdown by source</p>
        </div>

        <div class="d-none d-md-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <h4 class="mb-1">Incentive Details: {{ $targetStaff->name }}</h4>
                <div class="text-muted small">{{ strtoupper($targetStaff->role) }} | ID: {{ $targetStaff->unique_id }}</div>
            </div>
            <a href="{{ $isAdminViewer ? route('admin.users') : route('staff.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>

        @include('services::staff.partials.incentive-details-inner', [
            'tabIdPrefix' => 'staffinv',
            'incentiveEmbed' => false,
        ])
    </div>
</div>
@endsection

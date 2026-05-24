@extends('auth::layout')

@section('title', 'Incentive Details - Staff Dashboard')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="mb-1">
                Incentive Details: {{ $targetStaff->name }}
            </h4>
            <div class="text-muted small">
                {{ strtoupper($targetStaff->role) }} | ID: {{ $targetStaff->unique_id }}
            </div>
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

@if(! $isAdminViewer)
@endif
@endsection

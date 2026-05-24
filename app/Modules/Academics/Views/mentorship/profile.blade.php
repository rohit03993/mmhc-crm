@extends('auth::layout')

@section('title', $person->name.' — Mentorship profile')
@section('page-title', 'Mentorship profile')

@section('content')
@php
    $avatarUrl = ($profile && $profile->avatar_path)
        ? \Illuminate\Support\Facades\Storage::url($profile->avatar_path)
        : null;
    $roleLabel = ucfirst(str_replace('_', ' ', $person->role));
@endphp

<div class="container-fluid py-3 py-md-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('academics.mentorship.index') }}">Mentorship</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $person->name }}</li>
        </ol>
    </nav>

    <div class="alert alert-light border small mb-3">
        <i class="fas fa-shield-alt text-primary me-1"></i>
        Limited profile for mentorship only — contact details and admin records are not shown.
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-3">
                @if($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="" class="rounded-circle flex-shrink-0" width="88" height="88" style="object-fit: cover;">
                @else
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 88px; height: 88px;">
                        <i class="fas fa-user fa-2x text-primary"></i>
                    </div>
                @endif
                <div class="text-center text-sm-start flex-grow-1">
                    <h1 class="h4 fw-bold mb-1">{{ $person->name }}</h1>
                    <p class="mb-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary">{{ $roleLabel }}</span>
                        @if($person->unique_id)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1 font-monospace">{{ $person->unique_id }}</span>
                        @endif
                        @if($mentorshipStatus)
                            <span class="badge bg-{{ $mentorshipStatus === 'active' ? 'success' : ($mentorshipStatus === 'pending' ? 'warning text-dark' : 'secondary') }} ms-1">
                                Mentorship: {{ ucfirst($mentorshipStatus) }}
                            </span>
                        @endif
                    </p>
                    @if($institution)
                        <p class="text-muted small mb-0"><i class="fas fa-university me-1"></i>{{ $institution->name }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($person->role === 'faculty')
        <div class="row g-3 mb-4">
            @if($person->qualification)
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h6 fw-bold mb-2">Qualification</h2>
                        <p class="small mb-0 text-muted">{{ $person->qualification }}</p>
                    </div>
                </div>
            </div>
            @endif
            @if($activeMenteeCount !== null)
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h6 fw-bold mb-2">Mentorship</h2>
                        <p class="small mb-0"><strong>{{ $activeMenteeCount }}</strong> active mentee{{ $activeMenteeCount === 1 ? '' : 's' }} on MMHC</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    @endif

    @if(in_array($person->role, ['student', 'nurse', 'caregiver'], true))
        <div class="row g-3 mb-4">
            @if($person->qualification)
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h6 fw-bold mb-2">Qualification</h2>
                        <p class="small mb-0 text-muted">{{ $person->qualification }}</p>
                    </div>
                </div>
            </div>
            @endif
            @if($activeMentorCount !== null)
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h6 fw-bold mb-2">Mentorship</h2>
                        <p class="small mb-0"><strong>{{ $activeMentorCount }}</strong> active mentor{{ $activeMentorCount === 1 ? '' : 's' }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    @endif

    @if($profile && ($profile->bio || $profile->specialization || $profile->experience_years))
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h6 fw-bold mb-3">About</h2>
            @if($profile->specialization)
                <p class="small mb-2"><strong>Specialization:</strong> {{ $profile->specialization }}</p>
            @endif
            @if($profile->experience_years)
                <p class="small mb-2"><strong>Experience:</strong> {{ $profile->experience_years }} year{{ (int) $profile->experience_years === 1 ? '' : 's' }}</p>
            @endif
            @if($profile->bio)
                <p class="small text-muted mb-0">{{ $profile->bio }}</p>
            @endif
        </div>
    </div>
    @endif

    @if($batches->isNotEmpty())
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="px-3 px-md-4 pt-3 pb-2 border-bottom">
                <h2 class="h6 mb-0 fw-bold">Batches</h2>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($batches as $batch)
                    <li class="list-group-item px-3 px-md-4 small">
                        <span class="fw-medium">{{ $batch->name }}</span>
                        @if($batch->institution)
                            <span class="text-muted">— {{ $batch->institution->name }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    @if($person->role === 'faculty' && $subjectsTeaching->isNotEmpty())
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="px-3 px-md-4 pt-3 pb-2 border-bottom">
                <h2 class="h6 mb-0 fw-bold">Teaching (summary)</h2>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($subjectsTeaching as $sub)
                    <li class="list-group-item px-3 px-md-4 small">
                        <span class="fw-medium">{{ $sub->name }}</span>
                        @if($sub->batch)
                            <span class="text-muted">({{ $sub->batch->name }})</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('academics.mentorship.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
            <i class="fas fa-arrow-left me-1"></i> Back to mentorship
        </a>
        @if(in_array(auth()->user()->role, ['student', 'nurse', 'caregiver'], true) && $person->role === 'faculty' && !in_array($mentorshipStatus, ['pending', 'active'], true))
            <a href="{{ route('academics.mentorship.browse') }}" class="btn btn-primary btn-sm rounded-pill">Request mentorship</a>
        @endif
    </div>
</div>
@endsection

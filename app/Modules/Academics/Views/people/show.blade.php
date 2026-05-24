@extends('auth::layout')

@section('title', $person->name.' — Academics')
@section('page-title', 'Academic profile')

@section('content')
<div class="container-fluid py-3 py-md-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('academics.dashboard') }}">Academics</a></li>
            @if($person->academic_institution_id && in_array(auth()->user()->role, ['super_admin', 'admin', 'institution_admin', 'faculty'], true))
                <li class="breadcrumb-item"><a href="{{ route('academics.institutions.show', $person->academic_institution_id) }}">College</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $person->name }}</li>
        </ol>
    </nav>

    <div class="card academics-overview-card mb-4">
        <div class="card-body p-4">
            <h1 class="h4 fw-bold text-dark mb-1">{{ $person->name }}</h1>
            <p class="text-muted small mb-2">
                <span class="badge bg-primary bg-opacity-10 text-primary">{{ $person->role }}</span>
                @if($person->unique_id)
                    <span class="ms-2 font-monospace">{{ $person->unique_id }}</span>
                @endif
            </p>
            <p class="mb-1 small"><strong>Email:</strong> {{ $person->email }}</p>
            @if($person->phone)
                <p class="mb-0 small"><strong>Phone:</strong> {{ $person->phone }}</p>
            @endif
        </div>
    </div>

    @if($batches->isNotEmpty())
        <div class="card academics-overview-card mb-4">
            <div class="card-body p-0">
                <div class="px-3 px-md-4 pt-3 pb-2 border-bottom">
                    <h2 class="h6 mb-0 fw-bold">Batches</h2>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach($batches as $batch)
                        <li class="list-group-item px-3 px-md-4">
                            <span class="fw-medium">{{ $batch->name }}</span>
                            <span class="text-muted small">— {{ $batch->institution->name ?? '' }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if($person->role === 'faculty' && $subjectsTeaching->isNotEmpty())
        <div class="card academics-overview-card mb-4">
            <div class="card-body p-0">
                <div class="px-3 px-md-4 pt-3 pb-2 border-bottom">
                    <h2 class="h6 mb-0 fw-bold">Subjects</h2>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach($subjectsTeaching as $sub)
                        <li class="list-group-item px-3 px-md-4">
                            <span class="fw-medium">{{ $sub->name }}</span>
                            <span class="text-muted small">
                                ({{ $sub->batch->name ?? '' }})
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if($person->role === 'faculty' && isset($meiBreakdown))
        <div class="card academics-overview-card mb-4">
            <div class="card-body p-4">
                <h2 class="h6 fw-bold mb-3">Faculty score (FPI)</h2>
                <p class="display-6 mb-2">{{ $meiBreakdown['percent'] }}<span class="fs-6 text-muted">%</span></p>
                <ul class="small text-muted mb-0">
                    <li>Teaching (topics): {{ $meiBreakdown['teaching_percent'] }}%</li>
                    <li>Mentorship: {{ $meiBreakdown['mentorship_percent'] }}%</li>
                    <li>{{ $meiBreakdown['active_mentees'] }} students chose this faculty as mentor (+10 pts each)</li>
                    <li>{{ $meiBreakdown['reviews_given'] }} assignment ratings given (+8 pts each)</li>
                    @if($meiBreakdown['reviews_given'] > 0)
                        <li>Average rating given: {{ $meiBreakdown['avg_rating'] }}/5</li>
                    @endif
                </ul>
                <a href="{{ route('academics.mentorship.index') }}" class="btn btn-sm btn-outline-info mt-3">Open mentorship</a>
            </div>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('academics.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Dashboard</a>
        @if(in_array(auth()->user()->role, ['super_admin', 'admin'], true))
            <a href="{{ route('admin.profiles.view', $person) }}" class="btn btn-outline-primary btn-sm rounded-pill">Full CRM profile</a>
        @endif
    </div>
</div>
@endsection

@extends('auth::layout')

@section('title', 'My mentors - Academics')
@section('page-title', 'Mentorship')

@section('content')
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    <div class="acad-m-hero d-md-none">
        <p class="acad-m-hero__label">Mentorship</p>
        <h2 class="acad-m-hero__title">My mentors</h2>
        <p class="acad-m-hero__lede">Active mentors: <strong>{{ $mentorCount }}</strong> — share assignments for feedback.</p>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 academics-page-toolbar">
        <div class="d-none d-md-block">
            <h2 class="h5 mb-1">My mentors</h2>
            <p class="text-muted small mb-0">Active mentors: <strong>{{ $mentorCount }}</strong></p>
        </div>
        <a href="{{ route('academics.mentorship.browse') }}" class="btn btn-primary acad-cta-pill">
            <i class="fas fa-search me-1" aria-hidden="true"></i> Find mentors
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success app-alert">{{ session('success') }}</div>
    @endif

    @if($myMentors->isEmpty())
        @include('academics::partials.mobile-empty-state', [
            'icon' => 'fa-hands-helping',
            'title' => 'No mentors yet',
            'text' => 'Browse faculty on the platform — mentorship works across colleges.',
            'actionUrl' => route('academics.mentorship.browse'),
            'actionLabel' => 'Browse faculty',
        ])
        <p class="text-muted p-4 mb-0 d-none d-md-block">You have not requested any mentors yet.</p>
    @else
        <div class="acad-mentor-list d-md-none">
            @foreach($myMentors as $m)
                <article class="acad-mentor-card acad-mentor-card--compact">
                    <div class="acad-mentor-card__head">
                        <div class="acad-mentor-card__avatar" aria-hidden="true">
                            {{ $m->mentor ? strtoupper(substr($m->mentor->name, 0, 1)) : '?' }}
                        </div>
                        <div class="acad-mentor-card__meta">
                            @if($m->mentor)
                                <h3 class="acad-mentor-card__name mb-0">
                                    <a href="{{ route('academics.mentorship.profile', $m->mentor) }}">{{ $m->mentor->name }}</a>
                                </h3>
                                <p class="acad-mentor-card__inst mb-0">{{ $m->mentor->academicInstitution->name ?? 'Independent' }}</p>
                            @else
                                <h3 class="acad-mentor-card__name mb-0">—</h3>
                            @endif
                        </div>
                        <span class="acad-status-pill acad-status-pill--{{ $m->status === 'active' ? 'ok' : 'pending' }}">
                            {{ ucfirst($m->status) }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="card shadow-sm d-none d-md-block">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 mmhc-no-mobile-cards">
                        <thead class="table-light"><tr><th>Faculty</th><th>Institute</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($myMentors as $m)
                            <tr>
                                <td>
                                    @if($m->mentor)
                                        <a href="{{ route('academics.mentorship.profile', $m->mentor) }}" class="fw-medium text-decoration-none">{{ $m->mentor->name }}</a>
                                    @else — @endif
                                </td>
                                <td>{{ $m->mentor->academicInstitution->name ?? 'Independent' }}</td>
                                <td><span class="badge bg-{{ $m->status === 'active' ? 'success' : 'warning text-dark' }}">{{ ucfirst($m->status) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@extends('auth::layout')

@section('title', 'Browse faculty mentors')
@section('page-title', 'Find a mentor')

@section('content')
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    <div class="acad-m-hero d-md-none">
        <p class="acad-m-hero__label">Mentorship</p>
        <h2 class="acad-m-hero__title">Find a mentor</h2>
        <p class="acad-m-hero__lede">Browse faculty across all colleges. Send a request with an optional message.</p>
    </div>

    <a href="{{ route('academics.mentorship.index') }}" class="acad-text-link d-md-none mb-3">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> My mentors
    </a>
    <a href="{{ route('academics.mentorship.index') }}" class="btn btn-sm btn-outline-secondary mb-3 d-none d-md-inline-flex">&larr; My mentors</a>

    <h2 class="h5 mb-3 d-none d-md-block">Browse faculty</h2>

    <form method="GET" class="acad-search-bar mb-4">
        <div class="acad-search-bar__field">
            <i class="fas fa-search acad-search-bar__icon" aria-hidden="true"></i>
            <input type="search" name="q" class="form-control acad-search-bar__input"
                   placeholder="Name, qualification, or ID" value="{{ request('q') }}" autocomplete="off">
        </div>
        <button class="btn btn-primary acad-search-bar__btn" type="submit">Search</button>
    </form>

    <div class="acad-mentor-list">
        @forelse($faculty as $f)
            @php
                $initials = strtoupper(substr($f->name, 0, 1));
            @endphp
            <article class="acad-mentor-card">
                <div class="acad-mentor-card__head">
                    <div class="acad-mentor-card__avatar" aria-hidden="true">{{ $initials }}</div>
                    <div class="acad-mentor-card__meta">
                        <h3 class="acad-mentor-card__name">
                            <a href="{{ route('academics.mentorship.profile', $f) }}">{{ $f->name }}</a>
                        </h3>
                        <p class="acad-mentor-card__inst mb-0">
                            <i class="fas fa-university" aria-hidden="true"></i>
                            {{ $f->academicInstitution->name ?? 'Platform faculty' }}
                        </p>
                        @if($f->qualification)
                            <p class="acad-mentor-card__qual mb-0">{{ $f->qualification }}</p>
                        @endif
                    </div>
                </div>
                <div class="acad-mentor-card__actions">
                    <a href="{{ route('academics.mentorship.profile', $f) }}" class="acad-btn-ghost">View profile</a>
                    <button type="button" class="acad-btn-primary acad-mentor-card__toggle" data-bs-toggle="collapse"
                            data-bs-target="#mentor-req-{{ $f->id }}" aria-expanded="false">
                        Request
                    </button>
                </div>
                <div class="collapse" id="mentor-req-{{ $f->id }}">
                    <form action="{{ route('academics.mentorship.request') }}" method="POST" class="acad-mentor-card__form">
                        @csrf
                        <input type="hidden" name="mentor_id" value="{{ $f->id }}">
                        <label class="form-label small fw-semibold text-muted mb-1">Message (optional)</label>
                        <textarea name="message" class="form-control mb-2" rows="2" placeholder="Why would you like this mentor?"></textarea>
                        <button type="submit" class="btn btn-primary w-100 acad-btn-submit">
                            <i class="fas fa-paper-plane me-1" aria-hidden="true"></i> Send request
                        </button>
                    </form>
                </div>
            </article>
        @empty
            @include('academics::partials.mobile-empty-state', [
                'icon' => 'fa-user-friends',
                'title' => 'No faculty found',
                'text' => 'Try a different search term or check back later.',
                'actionUrl' => route('academics.mentorship.browse'),
                'actionLabel' => 'Clear search',
            ])
            <p class="text-muted d-none d-md-block">No faculty found.</p>
        @endforelse
    </div>
    <div class="mt-3">{{ $faculty->links() }}</div>
</div>
@endsection

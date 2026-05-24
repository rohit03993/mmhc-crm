@extends('auth::layout')

@section('title', 'Browse faculty mentors')
@section('page-title', 'Find a mentor')

@section('content')
<div class="container-fluid py-3">
    <a href="{{ route('academics.mentorship.index') }}" class="btn btn-sm btn-outline-secondary mb-3">&larr; My mentors</a>
    <h2 class="h5 mb-3">Browse faculty</h2>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-6">
            <input type="search" name="q" class="form-control" placeholder="Search by name, qualification, ID" value="{{ request('q') }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>

    <div class="row g-3">
        @forelse($faculty as $f)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h3 class="h6 mb-1">
                        <a href="{{ route('academics.mentorship.profile', $f) }}" class="text-decoration-none">{{ $f->name }}</a>
                    </h3>
                    <p class="small text-muted mb-2">{{ $f->academicInstitution->name ?? 'Platform faculty' }}</p>
                    @if($f->qualification)<p class="small mb-2">{{ $f->qualification }}</p>@endif
                    <a href="{{ route('academics.mentorship.profile', $f) }}" class="btn btn-sm btn-outline-primary w-100 mb-2">View profile</a>
                    <form action="{{ route('academics.mentorship.request') }}" method="POST">
                        @csrf
                        <input type="hidden" name="mentor_id" value="{{ $f->id }}">
                        <textarea name="message" class="form-control form-control-sm mb-2" rows="2" placeholder="Optional message"></textarea>
                        <button type="submit" class="btn btn-sm btn-primary w-100">Request mentorship</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12"><p class="text-muted">No faculty found.</p></div>
        @endforelse
    </div>
    <div class="mt-3">{{ $faculty->links() }}</div>
</div>
@endsection

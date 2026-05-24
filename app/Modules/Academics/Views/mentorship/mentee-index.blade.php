@extends('auth::layout')

@section('title', 'My mentors - Academics')
@section('page-title', 'Mentorship')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="h5 mb-1">My mentors</h2>
            <p class="text-muted small mb-0">Active mentors: <strong>{{ $mentorCount }}</strong></p>
        </div>
        <a href="{{ route('academics.mentorship.browse') }}" class="btn btn-primary">Find faculty mentors</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($myMentors->isEmpty())
                <p class="text-muted p-4 mb-0">You have not requested any mentors yet. Browse all faculty on the platform — no institute boundary.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Faculty</th><th>Institute</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($myMentors as $m)
                            <tr>
                                <td>{{ $m->mentor->name ?? '—' }}</td>
                                <td>{{ $m->mentor->academicInstitution->name ?? 'Independent' }}</td>
                                <td><span class="badge bg-{{ $m->status === 'active' ? 'success' : 'warning text-dark' }}">{{ ucfirst($m->status) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('auth::layout')

@section('title', 'Exams — Academics')
@section('page-title', 'Exams')

@section('content')
<div class="container-fluid py-3 py-md-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h1 class="h4 mb-0 fw-bold text-dark">Quizzes &amp; exams</h1>
            <p class="small text-muted mb-0">
                @if($viewerRole === 'student')
                    Assessments available to you (by subject, batch, or college-wide).
                @elseif(in_array($viewerRole, ['super_admin', 'admin'], true))
                    All institutions — create, publish, and view results.
                @else
                    Exams for your college scope. CRM admins see every exam.
                @endif
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(!empty($viewerCanCreate))
                <a href="{{ route('academics.exams.create') }}" class="btn btn-primary btn-sm rounded-pill"><i class="fas fa-plus me-1"></i>New exam</a>
            @endif
            <a href="{{ route('academics.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Dashboard</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    @if($exams->isEmpty())
        <div class="alert alert-light border text-muted mb-0">
            No exams to show yet. @if(!empty($viewerCanCreate))Create one to get started.@endif
        </div>
    @else
        <div class="table-responsive card border shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Title</th>
                        <th>Audience</th>
                        <th>College</th>
                        <th>Schedule</th>
                        <th class="pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exams as $exam)
                        <tr>
                            <td class="ps-3 fw-medium text-dark">
                                <a href="{{ route('academics.exams.show', $exam) }}" class="text-dark text-decoration-none">{{ $exam->title }}</a>
                                @if($exam->is_published)
                                    <span class="badge text-bg-success ms-1 small">Live</span>
                                @else
                                    <span class="badge text-bg-secondary ms-1 small">Draft</span>
                                @endif
                            </td>
                            <td class="small text-muted">
                                <span class="text-uppercase">{{ str_replace('_', ' ', $exam->audience_type) }}</span>
                                @if($exam->subject)
                                    <br><span class="text-dark">{{ $exam->subject->name }}</span>
                                @elseif($exam->batch)
                                    <br><span class="text-dark">Batch: {{ $exam->batch->name }}</span>
                                @endif
                            </td>
                            <td class="small">{{ $exam->institution->name ?? '—' }}</td>
                            <td class="small text-muted">
                                @if($exam->opens_at || $exam->closes_at)
                                    {{ $exam->opens_at?->format('M j, H:i') ?? '—' }}
                                    →
                                    {{ $exam->closes_at?->format('M j, H:i') ?? '—' }}
                                @else
                                    Open schedule
                                @endif
                            </td>
                            <td class="pe-3 text-nowrap">
                                <a href="{{ route('academics.exams.show', $exam) }}" class="btn btn-sm btn-outline-primary rounded-pill">Open</a>
                                @if(in_array($viewerRole, ['super_admin', 'admin'], true))
                                    <a href="{{ route('academics.exams.edit', $exam) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Edit</a>
                                    <a href="{{ route('academics.exams.attempts', $exam) }}" class="btn btn-sm btn-outline-success rounded-pill">Results</a>
                                @elseif(in_array($viewerRole, ['institution_admin', 'faculty'], true))
                                    @if(app(\App\Modules\Academics\Services\ExamAccessService::class)->canManage(auth()->user(), $exam))
                                        <a href="{{ route('academics.exams.edit', $exam) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Edit</a>
                                        <a href="{{ route('academics.exams.attempts', $exam) }}" class="btn btn-sm btn-outline-success rounded-pill">Results</a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

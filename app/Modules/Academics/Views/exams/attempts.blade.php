@extends('auth::layout')

@section('title', 'Results: '.$exam->title)
@section('page-title', 'Exam results')

@section('content')
<div class="container-fluid py-3 py-md-4">
    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
        <a href="{{ route('academics.exams.show', $exam) }}" class="btn btn-outline-secondary btn-sm rounded-pill">Exam detail</a>
        <a href="{{ route('academics.exams.edit', $exam) }}" class="btn btn-outline-primary btn-sm rounded-pill">Edit</a>
        <a href="{{ route('academics.exams.attempts.export', $exam) }}" class="btn btn-success btn-sm rounded-pill ms-md-auto">
            <i class="fas fa-file-csv me-1"></i>Export CSV
        </a>
    </div>

    <h1 class="h5 fw-bold mb-1">{{ $exam->title }}</h1>
    <p class="small text-muted mb-3">Submitted attempts · max score {{ number_format($maxPoints, 2) }}</p>

    @if($attempts->isEmpty())
        <div class="alert alert-light border">No submitted attempts yet.</div>
    @else
        <div class="table-responsive card border shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Student</th>
                        <th>Email</th>
                        <th>Score</th>
                        <th class="pe-3">Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attempts as $att)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('academics.exams.result', [$exam, $att]) }}" class="fw-medium text-decoration-none">{{ $att->studentLabel() }}</a>
                            </td>
                            <td class="small text-muted">{{ $att->user?->email ?? '—' }}</td>
                            <td>
                                <strong>{{ number_format((float) $att->score, 2) }}</strong>
                                <span class="text-muted small">/ {{ number_format($maxPoints, 2) }}</span>
                            </td>
                            <td class="pe-3 small text-muted">{{ $att->submitted_at?->format('M j, Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-1 mt-2">{{ $attempts->links('pagination.modern') }}</div>
    @endif
</div>
@endsection

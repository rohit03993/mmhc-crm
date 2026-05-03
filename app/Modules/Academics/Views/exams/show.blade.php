@extends('auth::layout')

@section('title', $exam->title.' — Exam')
@section('page-title', $exam->title)

@section('content')
<div class="container-fluid py-3 py-md-4">
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('academics.exams.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fas fa-arrow-left me-1"></i>All exams</a>
        @if($canManage)
            <a href="{{ route('academics.exams.edit', $exam) }}" class="btn btn-primary btn-sm rounded-pill"><i class="fas fa-edit me-1"></i>Edit &amp; questions</a>
            <a href="{{ route('academics.exams.attempts', $exam) }}" class="btn btn-outline-primary btn-sm rounded-pill"><i class="fas fa-chart-bar me-1"></i>Results</a>
        @endif
    </div>

    <div class="card border shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                <div>
                    @if($exam->is_published)
                        <span class="badge text-bg-success">Published</span>
                    @else
                        <span class="badge text-bg-secondary">Draft</span>
                    @endif
                    <span class="badge text-bg-light text-dark border ms-1">{{ str_replace('_', ' ', $exam->audience_type) }}</span>
                </div>
                <span class="text-muted small">Max score: <strong class="text-dark">{{ number_format($maxPoints, 2) }}</strong></span>
            </div>
            @if($exam->instructions)
                <div class="text-muted small mb-0">{!! nl2br(e($exam->instructions)) !!}</div>
            @endif
            <hr>
            <div class="row g-2 small text-muted">
                <div class="col-md-6">Institution: <span class="text-dark">{{ $exam->institution->name ?? '—' }}</span></div>
                @if($exam->subject)
                    <div class="col-md-6">Subject: <span class="text-dark">{{ $exam->subject->name }}</span></div>
                @endif
                @if($exam->batch)
                    <div class="col-md-6">Batch: <span class="text-dark">{{ $exam->batch->name }}</span></div>
                @endif
                <div class="col-md-6">Attempts allowed: <span class="text-dark">{{ $exam->max_attempts }}</span></div>
                <div class="col-md-6">Questions: <span class="text-dark">{{ $exam->questions->count() }}</span></div>
            </div>
        </div>
    </div>

    @if($exam->assignment)
        <div class="alert alert-light border small mb-3 mb-md-4">
            <strong>Linked assignment:</strong>
            @if($canManage)
                <a href="{{ route('academics.assignments.show', $exam->assignment) }}" class="ms-1">{{ $exam->assignment->title }}</a>
            @else
                <span class="ms-1">{{ $exam->assignment->title }}</span>
                <span class="text-muted">(open from My assignments for the shortcut)</span>
            @endif
        </div>
    @endif

    @if($canTake)
        <div class="card border shadow-sm rounded-3 border-primary border-opacity-25">
            <div class="card-body p-4">
                <h2 class="h6 fw-bold mb-2">Take this quiz</h2>
                @if(session('warning'))
                    <div class="alert alert-warning py-2 small">{{ session('warning') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-warning py-2 small">{{ session('error') }}</div>
                @endif
                @if($inProgress)
                    <p class="small text-muted mb-2">You have an attempt in progress.</p>
                    <a href="{{ route('academics.exams.take', [$exam, $inProgress]) }}" class="btn btn-primary rounded-pill">Continue attempt</a>
                @elseif($attemptCount >= $exam->max_attempts)
                    <p class="small text-muted mb-0">Maximum attempts reached.</p>
                    @if($lastSubmitted)
                        <a href="{{ route('academics.exams.result', [$exam, $lastSubmitted]) }}" class="btn btn-outline-primary btn-sm rounded-pill mt-2">View last result</a>
                    @endif
                @else
                    <form method="post" action="{{ route('academics.exams.start', $exam) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary rounded-pill" @if($exam->questions->isEmpty()) disabled @endif>Start attempt</button>
                    </form>
                    @if($lastSubmitted)
                        <a href="{{ route('academics.exams.result', [$exam, $lastSubmitted]) }}" class="btn btn-outline-secondary btn-sm rounded-pill ms-2">View last result</a>
                    @endif
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

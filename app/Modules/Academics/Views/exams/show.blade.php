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
                @if($exam->creator)
                    <div class="col-md-6">Created by: <span class="text-dark">{{ $exam->creator->name }}</span>
                        @if($exam->creator->role === 'faculty')
                            <span class="badge bg-light text-dark border ms-1" style="font-size:0.65rem;">Faculty</span>
                        @elseif($exam->creator->role === 'institution_admin')
                            <span class="badge bg-light text-dark border ms-1" style="font-size:0.65rem;">Institution admin</span>
                        @elseif(in_array($exam->creator->role, ['super_admin', 'admin'], true))
                            <span class="badge bg-light text-dark border ms-1" style="font-size:0.65rem;">Platform</span>
                        @endif
                    </div>
                @endif
                @if($canManage && $manageAttempts)
                    <div class="col-md-6">Attempts so far: <span class="text-dark">{{ $manageAttempts['submitted_count'] }} submitted</span>
                        @if($manageAttempts['in_progress_count'] > 0)
                            <span class="text-muted">· {{ $manageAttempts['in_progress_count'] }} in progress</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($canManage && $manageAttempts && $manageAttempts['recent']->isNotEmpty())
        <div class="card border shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h2 class="h6 mb-0 fw-bold text-dark">Recent attempts</h2>
                <a href="{{ route('academics.exams.attempts', $exam) }}" class="btn btn-sm btn-outline-primary rounded-pill">Full results</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Student</th>
                                <th>Status</th>
                                <th class="text-end">Score</th>
                                <th>Started</th>
                                <th>Submitted</th>
                                <th class="pe-3 text-end">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($manageAttempts['recent'] as $att)
                                <tr>
                                    <td class="ps-3">
                                        <span class="fw-medium text-dark">{{ $att->studentLabel() }}</span>
                                        @if($att->user && \Illuminate\Support\Str::of((string) ($att->user->name ?? ''))->trim()->isNotEmpty() && \Illuminate\Support\Str::of((string) ($att->user->email ?? ''))->trim()->isNotEmpty())
                                            <br><span class="text-muted" style="font-size:0.8rem;">{{ $att->user->email }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($att->status === \App\Modules\Academics\Models\AcademicExamAttempt::STATUS_SUBMITTED)
                                            <span class="badge text-bg-success">Submitted</span>
                                        @else
                                            <span class="badge text-bg-warning text-dark">In progress</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($att->status === \App\Modules\Academics\Models\AcademicExamAttempt::STATUS_SUBMITTED)
                                            {{ number_format((float) $att->score, 2) }} / {{ number_format($maxPoints, 2) }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $att->started_at?->format('M j, H:i') ?? '—' }}</td>
                                    <td class="text-muted">{{ $att->submitted_at?->format('M j, H:i') ?? '—' }}</td>
                                    <td class="pe-3 text-end text-nowrap">
                                        @if($att->status === \App\Modules\Academics\Models\AcademicExamAttempt::STATUS_SUBMITTED)
                                            <a href="{{ route('academics.exams.result', [$exam, $att]) }}" class="btn btn-sm btn-outline-primary rounded-pill">View</a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($manageAttempts['recent']->count() >= 75)
                    <p class="small text-muted mb-0 p-3 border-top">Showing the 75 most recent rows. Open <strong>Full results</strong> for the complete list.</p>
                @endif
            </div>
        </div>
    @elseif($canManage && $manageAttempts)
        <div class="alert alert-light border small mb-4 mb-md-4">
            <strong>No attempts yet.</strong> When students start this quiz, they will appear here and under <a href="{{ route('academics.exams.attempts', $exam) }}">Results</a>.
        </div>
    @endif

    @if($canManage && $exam->questions->isNotEmpty())
        <div class="card border shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h2 class="h6 mb-0 fw-bold text-dark">Question preview</h2>
                    <p class="small text-muted mb-0">How the quiz is built (correct answers marked for staff only).</p>
                </div>
                <a href="{{ route('academics.exams.edit', $exam) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Edit questions</a>
            </div>
            <div class="card-body p-4">
                @foreach($exam->questions as $qi => $q)
                    <div class="mb-4 pb-4 border-bottom @if($loop->last) border-0 mb-0 pb-0 @endif">
                        <div class="d-flex flex-wrap gap-2 align-items-start mb-2">
                            <span class="badge bg-secondary">{{ $loop->iteration }}</span>
                            @if($q->question_type === \App\Modules\Academics\Models\AcademicExamQuestion::TYPE_MCQ_MULTI)
                                <span class="badge text-bg-light text-dark border">Multi-select</span>
                            @else
                                <span class="badge text-bg-light text-dark border">Single choice</span>
                            @endif
                            <span class="badge text-bg-light text-dark border">{{ number_format((float) $q->points, 2) }} pts</span>
                        </div>
                        <p class="mb-2 text-dark">{!! nl2br(e($q->body)) !!}</p>
                        <ul class="list-unstyled small mb-0">
                            @foreach($q->options as $opt)
                                <li class="d-flex align-items-start gap-2 py-1">
                                    <span class="fw-semibold text-muted" style="min-width:1.25rem;">{{ $opt->label }}.</span>
                                    <span class="flex-grow-1">{{ $opt->body }}</span>
                                    @if($opt->is_correct)
                                        <span class="badge text-bg-success">Correct</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @if($q->explanation)
                            <p class="small text-muted mt-2 mb-0"><strong>Explanation:</strong> {!! nl2br(e($q->explanation)) !!}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

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

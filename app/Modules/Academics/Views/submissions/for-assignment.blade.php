@extends('auth::layout')

@section('title', 'Submissions - ' . $assignment->title)
@section('page-title', 'Submissions')

@section('content')
@php
    $total = $students->count();
    $submitted = $submissions->count();
    $pct = $total ? round($submitted / $total * 100) : 0;
    $pending = $total - $submitted;
    $lateCount = $submissions->filter(fn ($s) => $s->isLate())->count();
@endphp
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    <div class="acad-m-hero d-md-none">
        <p class="acad-m-hero__label">Submissions</p>
        <h2 class="acad-m-hero__title">{{ $assignment->title }}</h2>
        <p class="acad-m-hero__lede mb-0">
            {{ $assignment->topic->name ?? '—' }} · {{ $assignment->topic->subject->name ?? '—' }}
        </p>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 academics-page-toolbar d-none d-md-flex">
        <div>
            <h2 class="h5 mb-1">{{ $assignment->title }}</h2>
            <p class="text-muted small mb-0">
                Topic: {{ $assignment->topic->name ?? '—' }} · Subject: {{ $assignment->topic->subject->name ?? '—' }} · Batch: {{ $assignment->topic->subject->batch->name ?? '—' }}
            </p>
        </div>
        <a href="{{ route('academics.assignments.show', $assignment) }}" class="btn btn-outline-secondary">Back to assignment</a>
    </div>

    <div class="acad-submission-stats mb-3">
        <div class="acad-submission-stat">
            <span class="acad-submission-stat__value">{{ $submitted }}/{{ $total }}</span>
            <span class="acad-submission-stat__label">Submitted</span>
        </div>
        <div class="acad-submission-stat">
            <span class="acad-submission-stat__value">{{ $pct }}%</span>
            <span class="acad-submission-stat__label">Completion</span>
        </div>
        <div class="acad-submission-stat">
            <span class="acad-submission-stat__value">{{ $pending }}</span>
            <span class="acad-submission-stat__label">Pending</span>
        </div>
        @if($lateCount > 0)
        <div class="acad-submission-stat acad-submission-stat--warn">
            <span class="acad-submission-stat__value">{{ $lateCount }}</span>
            <span class="acad-submission-stat__label">Late</span>
        </div>
        @endif
    </div>

    <div class="acad-faculty-submission-list d-md-none">
        @foreach($students as $student)
            @php $sub = $submissions->get($student->id); @endphp
            <article class="acad-faculty-submission-card">
                <div class="acad-faculty-submission-card__top">
                    <div>
                        <h3 class="acad-faculty-submission-card__name">{{ $student->name }}</h3>
                        <p class="acad-faculty-submission-card__email mb-0">{{ $student->email }}</p>
                    </div>
                    @if($sub)
                        <span class="acad-status-pill acad-status-pill--{{ $sub->isLate() ? 'warn' : 'ok' }}">
                            {{ $sub->isLate() ? 'Late' : 'Submitted' }}
                        </span>
                    @else
                        <span class="acad-status-pill acad-status-pill--pending">Pending</span>
                    @endif
                </div>
                @if($sub)
                    <p class="acad-faculty-submission-card__meta mb-2">
                        <i class="far fa-clock" aria-hidden="true"></i>
                        {{ $sub->submitted_at->format('M j, Y g:i A') }}
                    </p>
                    @if($sub->checklist_points_possible !== null && (float) $sub->checklist_points_possible > 0)
                        <p class="acad-faculty-submission-card__score small mb-2">
                            Checklist {{ $sub->checklist_points_earned }}/{{ $sub->checklist_points_possible }}
                        </p>
                    @endif
                    <div class="acad-faculty-submission-card__actions">
                        @if($sub->file_path)
                            <a href="{{ route('academics.submissions.download', $sub) }}" class="acad-btn-primary">
                                <i class="fas fa-download" aria-hidden="true"></i> Download
                            </a>
                        @else
                            <span class="small text-muted">Notes / checklist only</span>
                        @endif
                    </div>
                @else
                    <p class="small text-muted mb-0">No submission yet.</p>
                @endif
            </article>
        @endforeach
    </div>

    <div class="card d-none d-md-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 mmhc-no-mobile-cards">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Submitted at</th>
                            <th>Checklist score</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        @php $sub = $submissions->get($student->id); @endphp
                        <tr>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->email }}</td>
                            <td>
                                @if($sub)
                                    <span class="badge bg-success">Submitted</span>
                                    @if($sub->isLate())
                                        <span class="badge bg-warning text-dark">Late</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Pending</span>
                                @endif
                            </td>
                            <td>{{ $sub ? $sub->submitted_at->format('M d, Y H:i') : '—' }}</td>
                            <td class="small">
                                @if($sub && $sub->checklist_points_possible !== null && (float) $sub->checklist_points_possible > 0)
                                    {{ $sub->checklist_points_earned }} / {{ $sub->checklist_points_possible }}
                                    <span class="text-muted">({{ round((float) $sub->checklist_points_earned / (float) $sub->checklist_points_possible * 100) }}%)</span>
                                @elseif($assignment->studentMustCompleteChecklist())
                                    —
                                @else
                                    <span class="text-muted">n/a</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($sub && $sub->file_path)
                                    <a href="{{ route('academics.submissions.download', $sub) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                @elseif($sub)
                                    <span class="small text-muted">No file</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <a href="{{ route('academics.assignments.show', $assignment) }}" class="acad-text-link mt-3 d-inline-flex d-md-none">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> Back to assignment
    </a>
</div>
@endsection

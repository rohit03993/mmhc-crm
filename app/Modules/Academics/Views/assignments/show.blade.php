@extends('auth::layout')

@section('title', $assignment->title . ' - Academics')
@section('page-title', 'Assignment')

@section('content')
<div class="container-fluid py-3">
    <div class="card">
        <div class="card-body">
            <h2 class="h5 mb-3">{{ $assignment->title }}</h2>
            @php
                use App\Modules\Academics\Support\AcademicsTaxonomy;
            @endphp
            <p class="mb-2">
                <span class="badge bg-primary">{{ AcademicsTaxonomy::assignmentTypeLabel($assignment->assignment_type) }}</span>
                @if($assignment->is_formative)<span class="badge bg-info text-dark">Formative</span>@endif
                @if($assignment->is_summative)<span class="badge bg-secondary">Summative</span>@endif
                @if($assignment->eval_includes_mcq)<span class="badge bg-light text-dark border">MCQ</span>@endif
                @if($assignment->eval_includes_practical)<span class="badge bg-light text-dark border">Practical</span>@endif
                @if($assignment->eval_includes_viva)<span class="badge bg-light text-dark border">Viva</span>@endif
                @if($assignment->eval_includes_checklist)<span class="badge bg-light text-dark border">Checklist</span>@endif
            </p>
            <p class="small text-muted mb-2">Assessment tags: {{ AcademicsTaxonomy::assessmentTypeLabels($assignment->assessment_type_keys) }}</p>
            <p class="text-muted small mb-2">
                Topic: {{ $assignment->topic->name ?? '—' }} · Subject: {{ $assignment->topic->subject->name ?? '—' }} · Batch: {{ $assignment->topic->subject->batch->name ?? '—' }}
            </p>
            @if($assignment->due_date)
                <p class="mb-2">
                    <strong>Due:</strong> {{ $assignment->due_date->format('M d, Y') }}
                    @if($assignment->isPastDue())
                        <span class="badge bg-danger ms-1">Overdue</span>
                    @endif
                </p>
            @endif
            @if($assignment->description)
                <div class="mb-3">
                    <strong>Description</strong>
                    <div class="mt-1">{{ nl2br(e($assignment->description)) }}</div>
                </div>
            @endif
            @if($assignment->hasChecklistForStudents())
                <div class="mb-3">
                    <strong class="small text-uppercase text-muted">Student checklist</strong>
                    <ol class="mb-0 mt-2 small">
                        @foreach($assignment->normalizedChecklistItems() as $row)
                            <li>{{ $row['label'] }} <span class="text-muted">({{ rtrim(rtrim(number_format((float) $row['points'], 2, '.', ''), '0'), '.') }} pt)</span></li>
                        @endforeach
                    </ol>
                </div>
            @endif
            @if(!empty($assignment->attachments))
                <div class="mb-3">
                    <strong>Attachments</strong>
                    <ul class="list-unstyled mt-1">
                        @foreach($assignment->attachments as $i => $file)
                            <li>
                                <a href="{{ route('academics.assignments.download', [$assignment, $i]) }}" target="_blank">
                                    <i class="fas fa-paperclip me-1"></i>{{ $file['name'] ?? 'File' }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if($linkedExams->isNotEmpty())
                <div class="mb-3">
                    <strong class="small text-uppercase text-muted">Linked quizzes</strong>
                    <ul class="list-unstyled mb-0 mt-1">
                        @foreach($linkedExams as $lex)
                            <li>
                                <a href="{{ route('academics.exams.show', $lex) }}" class="fw-medium">{{ $lex->title }}</a>
                                @if($lex->is_published)
                                    <span class="badge text-bg-success ms-1">Published</span>
                                @else
                                    <span class="badge text-bg-secondary ms-1">Draft</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(in_array(auth()->user()->role, ['institution_admin', 'faculty'], true))
            <a href="{{ route('academics.assignments.submissions', $assignment) }}" class="btn btn-outline-info btn-sm">View submissions</a>
            @endif
            <a href="{{ route('academics.assignments.edit', $assignment) }}" class="btn btn-outline-primary btn-sm">Edit</a>
            <a href="{{ route('academics.assignments.index') }}" class="btn btn-outline-secondary btn-sm">Back to list</a>
        </div>
    </div>
</div>
@endsection

@extends('auth::layout')

@section('title', $assignment->title . ' - Academics')
@section('page-title', 'Assignment')

@section('content')
<div class="container-fluid py-3">
    <div class="card">
        <div class="card-body">
            <h2 class="h5 mb-3">{{ $assignment->title }}</h2>
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
            @if(in_array(auth()->user()->role, ['institution_admin', 'faculty']))
            <a href="{{ route('academics.assignments.submissions', $assignment) }}" class="btn btn-outline-info btn-sm">View submissions</a>
            @endif
            <a href="{{ route('academics.assignments.edit', $assignment) }}" class="btn btn-outline-primary btn-sm">Edit</a>
            <a href="{{ route('academics.assignments.index') }}" class="btn btn-outline-secondary btn-sm">Back to list</a>
        </div>
    </div>
</div>
@endsection

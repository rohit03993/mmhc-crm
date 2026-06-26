@extends('auth::layout')

@section('title', $assignment->title)
@section('page-title', 'Assignment')

@section('content')
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    @if(session('success'))
        <div class="acad-notice acad-notice--ok mb-3"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    <div class="acad-homework-hero">
        <h1 class="acad-homework-hero__title">{{ $assignment->title }}</h1>
        <p class="acad-homework-hero__meta">{{ $openClassroom->title }}</p>
        @if($assignment->due_date)
            <p class="acad-homework-hero__due">Due {{ $assignment->due_date->format('l, M j, Y') }}</p>
        @endif
        @if($submission)
            <span class="acad-status-pill acad-status-pill--ok">Submitted {{ $submission->submitted_at->format('M j, Y') }}</span>
        @endif
    </div>

    @if($assignment->description)
        <section class="acad-homework-section">
            <h2 class="acad-homework-section__title">Instructions</h2>
            <div class="acad-homework-section__body">{!! nl2br(e($assignment->description)) !!}</div>
        </section>
    @endif

    @if(!empty($assignment->attachments))
        <section class="acad-homework-section">
            <h2 class="acad-homework-section__title">Materials</h2>
            <div class="acad-homework-files">
                @foreach($assignment->attachments as $i => $file)
                    <a href="{{ route('academics.open-classrooms.assignments.download', [$openClassroom, $assignment, $i]) }}" class="acad-homework-file">
                        <i class="fas fa-paperclip"></i> {{ $file['name'] ?? 'File' }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div class="acad-homework-actions">
        @if($isMember && ! $isOwner)
            <a href="{{ route('academics.open-classrooms.assignments.submit', [$openClassroom, $assignment]) }}" class="acad-btn-primary">
                {{ $submission ? 'Re-submit' : 'Submit work' }}
            </a>
            @if($submission && $submission->file_path)
                <a href="{{ route('academics.open-classrooms.submissions.download', [$openClassroom, $submission]) }}" class="acad-btn-ghost">My file</a>
            @endif
        @endif
        @if($isOwner)
            <a href="{{ route('academics.open-classrooms.assignments.submissions', [$openClassroom, $assignment]) }}" class="acad-btn-primary">View submissions</a>
        @endif
        <a href="{{ route('academics.open-classrooms.show', $openClassroom) }}" class="acad-btn-ghost">Back to classroom</a>
    </div>
</div>
@endsection

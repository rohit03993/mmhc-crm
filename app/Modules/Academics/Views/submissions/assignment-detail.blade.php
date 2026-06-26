@extends('auth::layout')

@section('title', $assignment->title . ' - Task')
@section('page-title', 'Task details')

@section('content')
@php
    use App\Modules\Academics\Support\AcademicsTaxonomy;
@endphp
<div class="container-fluid py-3 acad-mobile-page acad-homework-detail" data-mmhc-ptr>
  @if(session('success'))
    <div class="acad-notice acad-notice--ok mb-3">
      <i class="fas fa-check-circle" aria-hidden="true"></i>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  <div class="acad-homework-hero">
    <p class="acad-m-hero__label">Homework</p>
    <div class="acad-homework-hero__top">
      <h1 class="acad-homework-hero__title">{{ $assignment->title }}</h1>
      <span class="acad-status-pill acad-status-pill--{{ $status['pill'] }}">{{ $status['label'] }}</span>
    </div>
    <p class="acad-homework-hero__meta">
      {{ $assignment->topic->name ?? '—' }} · {{ $assignment->topic->subject->name ?? '—' }}
    </p>
    @if($assignment->due_date)
      <p class="acad-homework-hero__due">
        <i class="far fa-calendar" aria-hidden="true"></i>
        Due {{ $assignment->due_date->format('l, M j, Y') }}
        @if($assignment->isPastDue() && ! $submission)
          <span class="acad-status-pill acad-status-pill--warn ms-1">Past due</span>
        @endif
      </p>
    @endif
    @if($status['detail'])
      <p class="acad-homework-hero__detail">{{ $status['detail'] }}</p>
    @endif
  </div>

  <div class="acad-homework-spi">
    <div>
      <span class="acad-homework-spi__label">Your SPI</span>
      <strong class="acad-homework-spi__value">{{ $spiBreakdown['percent'] }}%</strong>
    </div>
    <p class="acad-homework-spi__hint mb-0">
      {{ $spiBreakdown['verified'] }} of {{ $spiBreakdown['total'] }} tasks fully credited
    </p>
  </div>

  <section class="acad-homework-section">
    <h2 class="acad-homework-section__title">What to do</h2>
    <div class="acad-homework-section__body">
      <p class="mb-2">
        <span class="badge bg-primary">{{ AcademicsTaxonomy::assignmentTypeLabel($assignment->assignment_type) }}</span>
        @if($assignment->is_formative)<span class="badge bg-info text-dark">Formative</span>@endif
        @if($assignment->is_summative)<span class="badge bg-secondary">Summative</span>@endif
      </p>
      @if($assignment->description)
        <div class="acad-homework-desc">{!! nl2br(e($assignment->description)) !!}</div>
      @else
        <p class="text-muted small mb-0">Complete the steps below and submit your work before the due date.</p>
      @endif
    </div>
  </section>

  @if($assignment->hasChecklistForStudents())
    <section class="acad-homework-section">
      <h2 class="acad-homework-section__title">Checklist</h2>
      <div class="acad-homework-section__body">
        <ol class="acad-homework-checklist mb-0">
          @foreach($assignment->normalizedChecklistItems() as $row)
            @php
              $idx = $loop->index;
              $answered = $submission && (bool) ($submission->checklist_answers[(string) $idx] ?? $submission->checklist_answers[$idx] ?? false);
            @endphp
            <li class="{{ $answered ? 'is-done' : '' }}">
              <span>{{ $row['label'] }}</span>
              <small class="text-muted">({{ rtrim(rtrim(number_format((float) $row['points'], 2, '.', ''), '0'), '.') }} pt)</small>
            </li>
          @endforeach
        </ol>
        @if($submission && $submission->checklist_points_possible !== null && (float) $submission->checklist_points_possible > 0)
          <p class="small fw-semibold mt-2 mb-0">
            Score: {{ $submission->checklist_points_earned }}/{{ $submission->checklist_points_possible }}
          </p>
        @endif
      </div>
    </section>
  @endif

  @if(!empty($assignment->attachments))
    <section class="acad-homework-section">
      <h2 class="acad-homework-section__title">Faculty materials</h2>
      <div class="acad-homework-section__body acad-homework-files">
        @foreach($assignment->attachments as $i => $file)
          <a href="{{ route('academics.assignments.material-download', [$assignment, $i]) }}" class="acad-homework-file" target="_blank" rel="noopener">
            <i class="fas fa-paperclip" aria-hidden="true"></i>
            <span>{{ $file['name'] ?? 'Attachment' }}</span>
          </a>
        @endforeach
      </div>
    </section>
  @endif

  @if($assignment->topic->resources->isNotEmpty())
    <section class="acad-homework-section">
      <h2 class="acad-homework-section__title">Learn first</h2>
      <div class="acad-homework-section__body">
        <div class="acad-resource-list">
          @foreach($assignment->topic->resources->take(3) as $r)
            <article class="acad-resource-card acad-resource-card--compact">
              <h3 class="acad-resource-card__title">{{ $r->title }}</h3>
              <div class="acad-resource-card__actions">
                @if($r->resource_type === \App\Modules\Academics\Models\TopicResource::TYPE_VIDEO_LINK && $r->video_url)
                  <a href="{{ $r->video_url }}" target="_blank" rel="noopener" class="acad-btn-ghost">Watch</a>
                @elseif($r->file_path)
                  <a href="{{ route('academics.topics.resources.download', [$assignment->topic, $r]) }}" class="acad-btn-ghost">Download</a>
                @endif
              </div>
            </article>
          @endforeach
        </div>
        @if($assignment->topic->resources->count() > 3)
          <a href="{{ route('academics.topics.student-library', $assignment->topic) }}" class="acad-text-link mt-2 d-inline-flex">
            View all {{ $assignment->topic->resources->count() }} resources
          </a>
        @else
          <a href="{{ route('academics.topics.student-library', $assignment->topic) }}" class="acad-text-link mt-2 d-inline-flex">
            Open topic library
          </a>
        @endif
      </div>
    </section>
  @endif

  @if($assignment->exams->isNotEmpty())
    <section class="acad-homework-section">
      <h2 class="acad-homework-section__title">Linked quiz</h2>
      <div class="acad-homework-section__body">
        @foreach($assignment->exams as $lex)
          @if($examAccess->canTake(auth()->user(), $lex))
            <div class="acad-homework-quiz-row">
              <div>
                <strong>{{ $lex->title }}</strong>
                @if($lex->is_published)
                  <span class="acad-status-pill acad-status-pill--ok ms-1">Open</span>
                @else
                  <span class="acad-status-pill acad-status-pill--pending ms-1">Draft</span>
                @endif
              </div>
              <a href="{{ route('academics.exams.show', $lex) }}" class="acad-btn-primary acad-btn-primary--sm">Take quiz</a>
            </div>
          @endif
        @endforeach
      </div>
    </section>
  @endif

  @if($submission && $submission->notes)
    <section class="acad-homework-section">
      <h2 class="acad-homework-section__title">Your notes</h2>
      <div class="acad-homework-section__body">
        <p class="mb-0 small">{{ $submission->notes }}</p>
      </div>
    </section>
  @endif

  <div class="acad-homework-actions">
    @if($submission && $submission->file_path)
      <a href="{{ route('academics.submissions.download', $submission) }}" class="acad-btn-ghost">
        <i class="fas fa-download" aria-hidden="true"></i> My file
      </a>
    @endif
    <a href="{{ route('academics.submit.form', $assignment) }}" class="acad-btn-primary">
      @if($submission)
        <i class="fas fa-redo" aria-hidden="true"></i> Re-submit
      @else
        <i class="fas fa-upload" aria-hidden="true"></i> Submit work
      @endif
    </a>
    <a href="{{ route('academics.my-assignments') }}" class="acad-btn-ghost acad-btn-ghost--muted d-md-none">Back to tasks</a>
    <a href="{{ route('academics.my-assignments') }}" class="btn btn-outline-secondary btn-sm d-none d-md-inline-block">Back to list</a>
  </div>
</div>
@endsection

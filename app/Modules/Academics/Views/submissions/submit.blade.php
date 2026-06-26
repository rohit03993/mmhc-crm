@extends('auth::layout')

@section('title', 'Submit - ' . $assignment->title)
@section('page-title', 'Submit Assignment')

@section('content')
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    <div class="acad-m-hero d-md-none">
        <p class="acad-m-hero__label">Assignment</p>
        <h2 class="acad-m-hero__title">{{ $assignment->title }}</h2>
        <p class="acad-m-hero__lede mb-0">
            {{ $assignment->topic->name ?? '—' }} · {{ $assignment->topic->subject->name ?? '—' }}
            @if($assignment->due_date)
                <br>Due {{ $assignment->due_date->format('M d, Y') }}
                @if($assignment->isPastDue())
                    <span class="acad-status-pill acad-status-pill--warn ms-1">Overdue</span>
                @endif
            @endif
        </p>
    </div>

    <div class="card mb-3 d-none d-md-block">
        <div class="card-body">
            <h2 class="h5">{{ $assignment->title }}</h2>
            <p class="text-muted small mb-0">
                Topic: {{ $assignment->topic->name ?? '—' }} · Subject: {{ $assignment->topic->subject->name ?? '—' }}
                @if($assignment->due_date)
                    · Due: {{ $assignment->due_date->format('M d, Y') }}
                    @if($assignment->isPastDue())
                        <span class="badge bg-danger ms-1">Overdue</span>
                    @endif
                @endif
            </p>
        </div>
    </div>

    @if($existing)
    <div class="acad-notice">
        <i class="fas fa-info-circle" aria-hidden="true"></i>
        <span>You submitted on {{ $existing->submitted_at->format('M d, Y H:i') }}. Uploading again replaces your previous file.</span>
    </div>
    @endif

    @foreach($assignment->exams as $lex)
        @if($examAccess->canTake(auth()->user(), $lex))
            <div class="acad-form-card d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span class="small mb-0">Linked quiz: <strong>{{ $lex->title }}</strong></span>
                <a href="{{ route('academics.exams.show', $lex) }}" class="acad-btn-primary" style="flex:0 1 auto; padding:0 1rem;">Open quiz</a>
            </div>
        @endif
    @endforeach

    @if($assignment->topic->resources->isNotEmpty())
        <div class="acad-form-card d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="small mb-0"><strong>{{ $assignment->topic->resources->count() }}</strong> topic resource(s) available</span>
            <a href="{{ route('academics.topics.student-library', $assignment->topic) }}" class="acad-btn-ghost" style="flex:0 1 auto; padding:0 1rem;">Open library</a>
        </div>
    @endif

    @if($assignment->studentMustCompleteChecklist())
        @php $items = $assignment->normalizedChecklistItems(); @endphp
    @endif

    <div class="acad-form-card">
        <form action="{{ route('academics.submit.store', $assignment) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <h3 class="acad-form-card__title d-md-none">Your submission</h3>
            @if($assignment->studentMustCompleteChecklist())
            <div class="mb-3">
                <h3 class="acad-form-card__title d-none d-md-block h6">Checklist</h3>
                <p class="small text-muted mb-3">Tick each criterion you have completed.</p>
                @foreach($items as $i => $item)
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="checklist[{{ $i }}]" value="1" id="cl{{ $i }}"
                            @checked((bool) old('checklist.'.$i, ($existing->checklist_answers[(string) $i] ?? $existing->checklist_answers[$i] ?? false)))>
                        <label class="form-check-label" for="cl{{ $i }}">{{ $item['label'] }} <span class="text-muted small">({{ rtrim(rtrim(number_format((float) $item['points'], 2, '.', ''), '0'), '.') }} pt)</span></label>
                    </div>
                @endforeach
            </div>
            @endif
            <div class="mb-3">
                <label for="file" class="form-label fw-semibold">Your submission file @if($assignment->assignment_type === \App\Modules\Academics\Models\Assignment::TYPE_FILE_UPLOAD)<span class="text-danger">*</span>@else<span class="text-muted">(optional)</span>@endif</label>
                <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" @if($assignment->assignment_type === \App\Modules\Academics\Models\Assignment::TYPE_FILE_UPLOAD) required @endif accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png">
                <small class="text-muted">Max 10MB. PDF, Word, text, or image.</small>
                @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @if(isset($activeMentors) && $activeMentors->isNotEmpty())
            <div class="mb-3">
                <label class="form-label fw-semibold">Share with mentor (optional)</label>
                <p class="small text-muted mb-2">Select mentors who should see and rate this submission.</p>
                @foreach($activeMentors as $mentor)
                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" name="mentor_ids[]" value="{{ $mentor->id }}" id="mentor{{ $mentor->id }}"
                            @checked(in_array($mentor->id, old('mentor_ids', $sharedMentorIds ?? []), true))>
                        <label class="form-check-label" for="mentor{{ $mentor->id }}">
                            {{ $mentor->name }}
                            @if($mentor->academicInstitution)
                                <span class="text-muted small">· {{ $mentor->academicInstitution->name }}</span>
                            @endif
                        </label>
                    </div>
                @endforeach
            </div>
            @endif
            <div class="mb-3">
                <label for="notes" class="form-label fw-semibold">Notes (optional)</label>
                <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="1000">{{ old('notes', $existing->notes ?? '') }}</textarea>
            </div>
            <div class="acad-form-actions d-md-none">
                <button type="submit" class="btn btn-primary">Submit assignment</button>
                <a href="{{ route('academics.my-assignments.show', $assignment) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
            <div class="d-none d-md-block">
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('academics.my-assignments.show', $assignment) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

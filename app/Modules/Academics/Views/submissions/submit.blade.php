@extends('auth::layout')

@section('title', 'Submit - ' . $assignment->title)
@section('page-title', 'Submit Assignment')

@section('content')
<div class="container-fluid py-3">
    <div class="card mb-3">
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
    <div class="alert alert-info">
        You have already submitted this assignment ({{ $existing->submitted_at->format('M d, Y H:i') }}).
        Uploading again will replace your previous submission.
    </div>
    @endif

    @foreach($assignment->exams as $lex)
        @if($examAccess->canTake(auth()->user(), $lex))
            <div class="alert alert-light border d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <span class="small mb-0">This assignment has a linked quiz: <strong>{{ $lex->title }}</strong></span>
                <a href="{{ route('academics.exams.show', $lex) }}" class="btn btn-sm btn-success">Open quiz</a>
            </div>
        @endif
    @endforeach

    @if($assignment->topic->resources->isNotEmpty())
        <div class="alert alert-secondary border-0 d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <span class="small mb-0">This topic has <strong>{{ $assignment->topic->resources->count() }}</strong> linked resource(s) (videos, PDFs).</span>
            <a href="{{ route('academics.topics.student-library', $assignment->topic) }}" class="btn btn-sm btn-outline-dark">Open topic library</a>
        </div>
    @endif

    @if($assignment->studentMustCompleteChecklist())
        @php $items = $assignment->normalizedChecklistItems(); @endphp
        <div class="card mb-3">
            <div class="card-body">
                <h3 class="h6">Checklist</h3>
                <p class="small text-muted mb-3">Tick each criterion you have completed. Points are summed for your score.</p>
                @foreach($items as $i => $item)
                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" name="checklist[{{ $i }}]" value="1" id="cl{{ $i }}"
                            @checked((bool) old('checklist.'.$i, ($existing->checklist_answers[(string) $i] ?? $existing->checklist_answers[$i] ?? false)))>
                        <label class="form-check-label" for="cl{{ $i }}">{{ $item['label'] }} <span class="text-muted small">({{ rtrim(rtrim(number_format((float) $item['points'], 2, '.', ''), '0'), '.') }} pt)</span></label>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('academics.submit.store', $assignment) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="file" class="form-label">Your submission file @if($assignment->assignment_type === \App\Modules\Academics\Models\Assignment::TYPE_FILE_UPLOAD)<span class="text-danger">*</span>@else<span class="text-muted">(optional)</span>@endif</label>
                    <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" @if($assignment->assignment_type === \App\Modules\Academics\Models\Assignment::TYPE_FILE_UPLOAD) required @endif accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png">
                    <small class="text-muted">Max 10MB. PDF, Word, text, or image.@if($assignment->assignment_type !== \App\Modules\Academics\Models\Assignment::TYPE_FILE_UPLOAD) For quiz-style work you can submit notes only or rely on the linked quiz.@endif</small>
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes (optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2" maxlength="1000">{{ old('notes', $existing->notes ?? '') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('academics.my-assignments') }}" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection

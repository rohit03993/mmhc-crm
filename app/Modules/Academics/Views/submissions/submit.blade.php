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

    <div class="card">
        <div class="card-body">
            <form action="{{ route('academics.submit.store', $assignment) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="file" class="form-label">Your submission file <span class="text-danger">*</span></label>
                    <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" required accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png">
                    <small class="text-muted">Max 10MB. PDF, Word, text, or image.</small>
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

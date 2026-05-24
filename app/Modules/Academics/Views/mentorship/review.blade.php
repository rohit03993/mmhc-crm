@extends('auth::layout')

@section('title', 'Rate submission')
@section('page-title', 'Rate submission')

@section('content')
<div class="container-fluid py-3">
    <a href="{{ route('academics.mentorship.index') }}" class="btn btn-sm btn-outline-secondary mb-3">&larr; Mentorship</a>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h2 class="h6 mb-1">{{ $share->submission->assignment->title ?? 'Assignment' }}</h2>
            <p class="small text-muted mb-0">Student: {{ $share->submission->user->name ?? '—' }}</p>
            @if($share->submission->file_path)
                <a href="{{ route('academics.submissions.download', $share->submission) }}" class="btn btn-sm btn-outline-primary mt-2">Download submission</a>
            @endif
            @if($share->submission->notes)
                <p class="small mt-2 mb-0"><strong>Notes:</strong> {{ $share->submission->notes }}</p>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('academics.mentorship.reviews.store', $share) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Rating (1–5)</label>
                    <select name="rating" class="form-select" required>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" @selected(old('rating', $existing->rating ?? '') == $i)>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Feedback</label>
                    <textarea name="feedback" class="form-control" rows="3" maxlength="2000">{{ old('feedback', $existing->feedback ?? '') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save rating</button>
            </form>
        </div>
    </div>
</div>
@endsection

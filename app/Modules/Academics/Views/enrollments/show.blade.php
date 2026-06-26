@extends('auth::layout')

@section('title', 'Review enrollment - Academics')
@section('page-title', 'Review enrollment')

@section('content')
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    <a href="{{ route('academics.enrollments.index') }}" class="acad-text-link d-md-none mb-3">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> Enrollments
    </a>
    <a href="{{ route('academics.enrollments.index') }}" class="btn btn-sm btn-outline-secondary mb-3 d-none d-md-inline-flex">&larr; Back</a>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h2 class="h5">{{ $application->user->name }}</h2>
            <p class="text-muted small mb-0">
                {{ $application->user->phone ?? $application->user->email }}
                · {{ $application->institution->name ?? 'Institute' }}
                · Requested {{ $application->created_at->format('M d, Y') }}
            </p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('academics.enrollments.approve', $application) }}" method="POST" class="mb-4">
                @csrf
                <h3 class="h6">Approve and assign batch(es)</h3>
                <p class="small text-muted">Select batch(es). Assign subject faculty via Subjects and Batches.</p>
                @foreach($batches as $batch)
                    <div class="form-check mb-1">
                        <input type="checkbox" class="form-check-input" name="batch_ids[]" value="{{ $batch->id }}" id="batch{{ $batch->id }}"
                            @checked(in_array($batch->id, old('batch_ids', $application->requested_batch_ids ?? []), true))>
                        <label class="form-check-label" for="batch{{ $batch->id }}">{{ $batch->name }}</label>
                    </div>
                @endforeach
                @error('batch_ids')<div class="text-danger small">{{ $message }}</div>@enderror
                <div class="mt-3 mb-3">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="reviewer_notes" class="form-control" rows="2" maxlength="1000">{{ old('reviewer_notes') }}</textarea>
                </div>
                <button type="submit" class="btn btn-success">Approve enrollment</button>
            </form>

            <hr>

            <form action="{{ route('academics.enrollments.reject', $application) }}" method="POST" onsubmit="return confirm('Reject this enrollment request?');">
                @csrf
                <h3 class="h6 text-danger">Reject request</h3>
                <div class="mb-3">
                    <label class="form-label">Reason (optional)</label>
                    <textarea name="reviewer_notes" class="form-control" rows="2" maxlength="1000"></textarea>
                </div>
                <button type="submit" class="btn btn-outline-danger">Reject</button>
            </form>
        </div>
    </div>
</div>
@endsection

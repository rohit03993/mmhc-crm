@extends('auth::layout')

@section('title', 'Submit — '.$assignment->title)
@section('page-title', 'Submit')

@section('content')
<div class="container-fluid py-3 acad-mobile-page">
    <div class="acad-m-hero d-md-none">
        <h2 class="acad-m-hero__title">{{ $assignment->title }}</h2>
        <p class="acad-m-hero__lede">{{ $openClassroom->title }}</p>
    </div>

    @if($existing)
        <div class="acad-notice mb-3">
            <i class="fas fa-info-circle"></i>
            <span>Submitted {{ $existing->submitted_at->format('M d, Y H:i') }}. Uploading again replaces your file.</span>
        </div>
    @endif

    <div class="acad-form-card">
        <form method="POST" action="{{ route('academics.open-classrooms.assignments.submit.store', [$openClassroom, $assignment]) }}" enctype="multipart/form-data">
            @csrf
            @if($assignment->hasChecklist())
                @php $items = $assignment->normalizedChecklistItems(); @endphp
                <div class="mb-3">
                    <p class="small fw-semibold">Checklist</p>
                    @foreach($items as $i => $item)
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" name="checklist[{{ $i }}]" value="1" id="ocl{{ $i }}"
                                @checked((bool) old('checklist.'.$i, ($existing->checklist_answers[(string) $i] ?? false)))>
                            <label class="form-check-label" for="ocl{{ $i }}">{{ $item['label'] }}</label>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="mb-3">
                <label class="form-label fw-semibold">Your file</label>
                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Notes</label>
                <textarea name="notes" class="form-control" rows="3" maxlength="1000">{{ old('notes', $existing->notes ?? '') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{ route('academics.open-classrooms.assignments.show', [$openClassroom, $assignment]) }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection

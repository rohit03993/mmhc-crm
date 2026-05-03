@extends('auth::layout')

@section('title', 'Edit Assignment - Academics')
@section('page-title', 'Edit Assignment')

@section('content')
<div class="container-fluid py-3">
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Assignment details</h5>
            <form action="{{ route('academics.assignments.update', $assignment) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label for="topic_id" class="form-label">Topic <span class="text-danger">*</span></label>
                        <select name="topic_id" id="topic_id" class="form-select @error('topic_id') is-invalid @enderror" required>
                            @foreach($topics as $t)
                                <option value="{{ $t->id }}" {{ old('topic_id', $assignment->topic_id) == $t->id ? 'selected' : '' }}>{{ $t->name }} — {{ $t->subject->name ?? '' }}</option>
                            @endforeach
                        </select>
                        @error('topic_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $assignment->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $assignment->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @include('academics::partials.assignment-taxonomy-fields', ['assignment' => $assignment])
                    @include('academics::partials.assignment-checklist-fields', ['assignment' => $assignment])
                    <div class="col-md-6">
                        <label for="due_date" class="form-label">Due date</label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', $assignment->due_date?->format('Y-m-d')) }}">
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Add more attachments (max 10MB each)</label>
                        <input type="file" class="form-control" name="attachments[]" multiple accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    @if(!empty($assignment->attachments))
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Current attachments</h5>
            <ul class="list-group list-group-flush">
                @foreach($assignment->attachments as $i => $file)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="{{ route('academics.assignments.download', [$assignment, $i]) }}" target="_blank"><i class="fas fa-paperclip me-1"></i>{{ $file['name'] ?? 'File' }}</a>
                    <form action="{{ route('academics.assignments.remove-attachment', $assignment) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this attachment?');">
                        @csrf
                        <input type="hidden" name="index" value="{{ $i }}">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="mt-3">
        <a href="{{ route('academics.assignments.show', $assignment) }}" class="btn btn-outline-secondary">View</a>
        <a href="{{ route('academics.assignments.index') }}" class="btn btn-outline-secondary">Back to list</a>
        <form action="{{ route('academics.assignments.destroy', $assignment) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this assignment?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Delete assignment</button>
        </form>
    </div>
</div>
@endsection

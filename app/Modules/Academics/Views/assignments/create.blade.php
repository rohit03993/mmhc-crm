@extends('auth::layout')

@section('title', 'Add Assignment - Academics')
@section('page-title', 'Add Assignment')

@section('content')
<div class="container-fluid py-3">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('academics.assignments.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label for="topic_id" class="form-label">Topic <span class="text-danger">*</span></label>
                        <select name="topic_id" id="topic_id" class="form-select @error('topic_id') is-invalid @enderror" required>
                            <option value="">Select topic</option>
                            @foreach($topics as $t)
                                <option value="{{ $t->id }}" {{ old('topic_id') == $t->id ? 'selected' : '' }}>{{ $t->name }} — {{ $t->subject->name ?? '' }} ({{ $t->subject->batch->name ?? '' }})</option>
                            @endforeach
                        </select>
                        @error('topic_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @include('academics::partials.assignment-taxonomy-fields', ['assignment' => null])
                    @include('academics::partials.assignment-checklist-fields', ['assignment' => null])
                    <div class="col-md-6">
                        <label for="due_date" class="form-label">Due date</label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date') }}">
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="attachments" class="form-label">Attachments (optional, max 10MB each)</label>
                        <input type="file" class="form-control @error('attachments.*') is-invalid @enderror" id="attachments" name="attachments[]" multiple accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png">
                        @error('attachments.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create Assignment</button>
                    <a href="{{ route('academics.assignments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('auth::layout')

@section('title', 'Edit Subject - Academics')
@section('page-title', 'Edit Subject')

@section('content')
<div class="container-fluid py-3">
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Subject details</h5>
            <form action="{{ route('academics.subjects.update', $subject) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label for="batch_id" class="form-label">Batch <span class="text-danger">*</span></label>
                        <select name="batch_id" id="batch_id" class="form-select @error('batch_id') is-invalid @enderror" required>
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}" {{ old('batch_id', $subject->batch_id) == $b->id ? 'selected' : '' }}>{{ $b->name }} — {{ $b->institution->name ?? '' }}</option>
                            @endforeach
                        </select>
                        @error('batch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="name" class="form-label">Subject name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $subject->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="code" class="form-label">Code</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $subject->code) }}">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $subject->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Update details</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Assign faculty</h5>
            <p class="text-muted small">Only faculty assigned to this batch can be assigned to the subject.</p>
            <form action="{{ route('academics.subjects.faculty.update', $subject) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Faculty for this subject</label>
                        <select name="faculty_ids[]" class="form-select" multiple size="6">
                            @foreach($facultyAvailable as $u)
                                <option value="{{ $u->id }}" {{ $subject->faculty->contains('id', $u->id) ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Ctrl/Cmd+click to select multiple. If empty, assign faculty to the batch first.</small>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Update faculty</button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('academics.subjects.index') }}" class="btn btn-outline-secondary">Back to Subjects</a>
        <form action="{{ route('academics.subjects.destroy', $subject) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this subject?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Delete subject</button>
        </form>
    </div>
</div>
@endsection

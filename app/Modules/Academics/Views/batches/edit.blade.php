@extends('auth::layout')

@section('title', 'Edit Batch - Academics')
@section('page-title', 'Edit Batch')

@section('content')
<div class="container-fluid py-3">
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Batch details</h5>
            <form action="{{ route('academics.batches.update', $batch) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    @if(auth()->user()->role === 'super_admin')
                    <div class="col-12">
                        <label for="institution_id" class="form-label">Institution <span class="text-danger">*</span></label>
                        <select name="institution_id" id="institution_id" class="form-select @error('institution_id') is-invalid @enderror" required>
                            @foreach($institutions as $inst)
                                <option value="{{ $inst->id }}" {{ old('institution_id', $batch->institution_id) == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                            @endforeach
                        </select>
                        @error('institution_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @endif
                    <div class="col-md-6">
                        <label for="name" class="form-label">Batch name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $batch->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="academic_year" class="form-label">Academic year</label>
                        <input type="text" class="form-control @error('academic_year') is-invalid @enderror" id="academic_year" name="academic_year" value="{{ old('academic_year', $batch->academic_year) }}">
                        @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start date</label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', $batch->start_date?->format('Y-m-d')) }}">
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">End date</label>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', $batch->end_date?->format('Y-m-d')) }}">
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $batch->is_active) ? 'checked' : '' }}>
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
            <h5 class="card-title mb-3">Assign students & faculty</h5>
            <form action="{{ route('academics.batches.assignments.update', $batch) }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Students in this batch</label>
                        <select name="student_ids[]" class="form-select" multiple size="8">
                            @foreach($studentsAvailable as $u)
                                <option value="{{ $u->id }}" {{ $batch->students->contains('id', $u->id) ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Ctrl/Cmd+click to select multiple</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Faculty in this batch</label>
                        <select name="faculty_ids[]" class="form-select" multiple size="8">
                            @foreach($facultyAvailable as $u)
                                <option value="{{ $u->id }}" {{ $batch->faculty->contains('id', $u->id) ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Ctrl/Cmd+click to select multiple</small>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Update assignments</button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('academics.batches.index') }}" class="btn btn-outline-secondary">Back to Batches</a>
        <form action="{{ route('academics.batches.destroy', $batch) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this batch?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Delete batch</button>
        </form>
    </div>
</div>
@endsection

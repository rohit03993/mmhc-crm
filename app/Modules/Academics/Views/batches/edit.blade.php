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
                <input type="hidden" name="institution_id" value="{{ $batch->institution_id }}">
                <div class="row g-3">
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
                        <div class="list-group list-group-flush border rounded" style="max-height: 280px; overflow-y: auto;">
                            @forelse($studentsAvailable as $u)
                                <label class="list-group-item list-group-item-action d-flex align-items-center gap-2 mb-0 cursor-pointer">
                                    <input type="checkbox" name="student_ids[]" value="{{ $u->id }}" class="form-check-input flex-shrink-0" {{ $batch->students->contains('id', $u->id) ? 'checked' : '' }}>
                                    <span class="flex-grow-1">{{ $u->name }}</span>
                                    <small class="text-muted">{{ $u->email }}</small>
                                </label>
                            @empty
                                <div class="list-group-item text-muted">No students available.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Faculty in this batch</label>
                        <div class="list-group list-group-flush border rounded" style="max-height: 280px; overflow-y: auto;">
                            @forelse($facultyAvailable as $u)
                                <label class="list-group-item list-group-item-action d-flex align-items-center gap-2 mb-0 cursor-pointer">
                                    <input type="checkbox" name="faculty_ids[]" value="{{ $u->id }}" class="form-check-input flex-shrink-0" {{ $batch->faculty->contains('id', $u->id) ? 'checked' : '' }}>
                                    <span class="flex-grow-1">{{ $u->name }}</span>
                                    <small class="text-muted">{{ $u->email }}</small>
                                </label>
                            @empty
                                <div class="list-group-item text-muted">No faculty in this institution. <a href="{{ route('academics.faculty.create') }}">Add faculty</a>.</div>
                            @endforelse
                        </div>
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

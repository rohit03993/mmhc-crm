@extends('auth::layout')

@section('title', 'Add Batch - Academics')
@section('page-title', 'Add Batch')

@section('content')
<div class="container-fluid py-3">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('academics.batches.store') }}" method="POST">
                @csrf
                <input type="hidden" name="institution_id" value="{{ auth()->user()->academic_institution_id }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Batch name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="academic_year" class="form-label">Academic year</label>
                        <input type="text" class="form-control @error('academic_year') is-invalid @enderror" id="academic_year" name="academic_year" value="{{ old('academic_year') }}" placeholder="e.g. 2024-25">
                        @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start date</label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') }}">
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">End date</label>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}">
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create Batch</button>
                    <a href="{{ route('academics.batches.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

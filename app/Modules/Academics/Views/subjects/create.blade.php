@extends('auth::layout')

@section('title', 'Add Subject - Academics')
@section('page-title', 'Add Subject')

@section('content')
<div class="container-fluid py-3">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('academics.subjects.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label for="batch_id" class="form-label">Batch <span class="text-danger">*</span></label>
                        <select name="batch_id" id="batch_id" class="form-select @error('batch_id') is-invalid @enderror" required>
                            <option value="">Select batch</option>
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}" {{ old('batch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }} — {{ $b->institution->name ?? '' }}</option>
                            @endforeach
                        </select>
                        @error('batch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="name" class="form-label">Subject name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="code" class="form-label">Code</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" placeholder="e.g. NUR101">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                    <button type="submit" class="btn btn-primary">Create Subject</button>
                    <a href="{{ route('academics.subjects.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

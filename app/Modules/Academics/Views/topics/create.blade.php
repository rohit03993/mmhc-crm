@extends('auth::layout')

@section('title', 'Add Topic - Academics')
@section('page-title', 'Add Topic')

@section('content')
<div class="container-fluid py-3">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('academics.topics.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                            <option value="">Select subject</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" {{ old('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }} — {{ $s->batch->name ?? '' }} ({{ $s->batch->institution->name ?? '' }})</option>
                            @endforeach
                        </select>
                        @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="name" class="form-label">Topic name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="sort_order" class="form-label">Order</label>
                        <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create Topic</button>
                    <a href="{{ route('academics.topics.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

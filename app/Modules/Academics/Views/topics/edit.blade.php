@extends('auth::layout')

@section('title', 'Edit Topic - Academics')
@section('page-title', 'Edit Topic')

@section('content')
<div class="container-fluid py-3">
    <div class="mb-3">
        <a href="{{ route('academics.topics.resources.index', $topic) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-film me-1"></i>Procedure &amp; video library
        </a>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <span class="badge {{ $topic->is_completed ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ $topic->is_completed ? 'Completed' : 'Pending' }}
                </span>
                <small class="text-muted ms-2">Completion is set automatically when assignment submission threshold is met.</small>
            </div>
            <form action="{{ route('academics.topics.update', $topic) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" {{ old('subject_id', $topic->subject_id) == $s->id ? 'selected' : '' }}>{{ $s->name }} — {{ $s->batch->name ?? '' }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="name" class="form-label">Topic name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $topic->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="sort_order" class="form-label">Order</label>
                        <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $topic->sort_order) }}" min="0">
                        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @include('academics::partials.teaching-methods-fields', ['topic' => $topic])
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Topic</button>
                    <a href="{{ route('academics.topics.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <form action="{{ route('academics.topics.destroy', $topic) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this topic?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">Delete</button>
                    </form>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

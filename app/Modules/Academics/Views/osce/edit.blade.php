@extends('auth::layout')

@section('title', 'Edit OSCE')
@section('page-title', 'Edit OSCE session')

@section('content')
<div class="container-fluid py-3">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('academics.osce.update', $session) }}" method="POST" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-12">
                    <label for="institution_id" class="form-label">Institution <span class="text-danger">*</span></label>
                    <select name="institution_id" id="institution_id" class="form-select @error('institution_id') is-invalid @enderror" required>
                        @foreach($institutions as $i)
                            <option value="{{ $i->id }}" @selected(old('institution_id', $session->institution_id) == $i->id)>{{ $i->name }}</option>
                        @endforeach
                    </select>
                    @error('institution_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="batch_id" class="form-label">Batch</label>
                    <select name="batch_id" id="batch_id" class="form-select @error('batch_id') is-invalid @enderror">
                        <option value="">— All batches —</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}" @selected(old('batch_id', $session->batch_id) == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    @error('batch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $session->title) }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $session->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="starts_at" class="form-label">Starts at</label>
                    <input type="datetime-local" name="starts_at" id="starts_at" class="form-control @error('starts_at') is-invalid @enderror" value="{{ old('starts_at', $session->starts_at?->format('Y-m-d\TH:i')) }}">
                    @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="duration_minutes" class="form-label">Duration (minutes)</label>
                    <input type="number" name="duration_minutes" id="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" value="{{ old('duration_minutes', $session->duration_minutes) }}" min="15" max="1440">
                    @error('duration_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('academics.osce.show', $session) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

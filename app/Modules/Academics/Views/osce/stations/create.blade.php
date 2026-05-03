@extends('auth::layout')

@section('title', 'Add OSCE station')
@section('page-title', 'Add station — '.$session->title)

@section('content')
<div class="container-fluid py-3">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('academics.osce.stations.store', $session) }}" method="POST" class="row g-3">
                @csrf
                <div class="col-12">
                    <label for="name" class="form-label">Station name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="instructions" class="form-label">Instructions</label>
                    <textarea name="instructions" id="instructions" class="form-control @error('instructions') is-invalid @enderror" rows="3">{{ old('instructions') }}</textarea>
                    @error('instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="time_limit_seconds" class="form-label">Time limit (seconds)</label>
                    <input type="number" name="time_limit_seconds" id="time_limit_seconds" class="form-control @error('time_limit_seconds') is-invalid @enderror" value="{{ old('time_limit_seconds') }}" min="30" max="7200">
                    @error('time_limit_seconds')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="checklist_items_raw" class="form-label">Station checklist <span class="text-danger">*</span></label>
                    <textarea name="checklist_items_raw" id="checklist_items_raw" class="form-control font-monospace small @error('checklist_items_raw') is-invalid @enderror" rows="6" placeholder="One criterion per line. Optional: Label | 2">{{ old('checklist_items_raw') }}</textarea>
                    @error('checklist_items_raw')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save station</button>
                    <a href="{{ route('academics.osce.show', $session) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('auth::layout')

@section('title', 'Add faculty - Academics')
@section('page-title', 'Add faculty')

@section('content')
<div class="container-fluid py-3">
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('academics.faculty.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required minlength="8">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Min 8 characters</small>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirm password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="8">
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="unique_id" class="form-label">Unique ID</label>
                        <input type="text" class="form-control @error('unique_id') is-invalid @enderror" id="unique_id" name="unique_id" value="{{ old('unique_id') }}" placeholder="e.g. ACAD-F-003">
                        @error('unique_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Add faculty</button>
                    <a href="{{ route('academics.faculty.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <p class="small text-muted mt-3 mb-0">After adding, assign this faculty to batches in <strong>Batches</strong> → Edit batch → Assign students &amp; faculty.</p>
</div>
@endsection

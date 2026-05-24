@extends('auth::layout')

@section('title', 'Add student - Academics')
@section('page-title', 'Add student')

@section('content')
<div class="container-fluid py-3">
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('academics.students.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    @if(isset($institutions) && $institutions->isNotEmpty())
                    <div class="col-12">
                        <label for="institution_id" class="form-label">Institution <span class="text-danger">*</span></label>
                        <select name="institution_id" id="institution_id" class="form-select" required onchange="window.location='{{ route('academics.students.create') }}?institution_id='+this.value">
                            <option value="">— Select college —</option>
                            @foreach($institutions as $inst)
                                <option value="{{ $inst->id }}" @selected($institutionId == $inst->id)>{{ $inst->name }}</option>
                            @endforeach
                        </select>
                        @error('institution_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    @endif
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
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirm password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="8">
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
                    </div>
                    @if($batches->isNotEmpty())
                    <div class="col-12">
                        <label class="form-label">Assign to batch(es) <span class="text-danger">*</span></label>
                        @foreach($batches as $batch)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="batch_ids[]" value="{{ $batch->id }}" id="b{{ $batch->id }}" @checked(in_array($batch->id, old('batch_ids', [])))>
                                <label class="form-check-label" for="b{{ $batch->id }}">{{ $batch->name }}</label>
                            </div>
                        @endforeach
                        @error('batch_ids')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    @elseif($institutionId)
                    <div class="col-12"><p class="text-muted small mb-0">No batches for this institution yet. Create batches first.</p></div>
                    @endif
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" @disabled($institutionId && $batches->isEmpty())>Add student</button>
                    <a href="{{ route('academics.students.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

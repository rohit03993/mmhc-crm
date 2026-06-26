@extends('auth::layout')

@section('title', 'Mark Attendance - Academics')
@section('page-title', 'Mark Attendance')

@section('content')
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    @include('academics::partials.mobile-list-hero', ['title' => 'Mark attendance', 'lede' => 'Pick a batch and date to record presence.'])
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <p class="text-muted mb-3">Select a batch and date to mark attendance.</p>
            <form action="{{ route('academics.attendance.mark') }}" method="GET" class="row g-3 align-items-end academics-filter-row">
                <div class="col-md-5">
                    <label for="batch_id" class="form-label">Batch <span class="text-danger">*</span></label>
                    <select name="batch_id" id="batch_id" class="form-select" required>
                        <option value="">Select batch</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}" {{ ($batchId ?? '') == $b->id ? 'selected' : '' }}>{{ $b->name }} ({{ $b->institution->name ?? '—' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control" value="{{ $date ?? date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-edit me-1"></i>Mark attendance</button>
                </div>
            </form>
        </div>
    </div>

    @if($batches->isEmpty())
        <p class="text-muted mt-3">No batches available for you to mark attendance.</p>
    @endif
</div>
@endsection

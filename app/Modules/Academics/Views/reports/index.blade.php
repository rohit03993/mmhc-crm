@extends('auth::layout')

@section('title', 'Reports - Academics')
@section('page-title', 'Reports')

@section('content')
<div class="container-fluid py-3">
    <h2 class="h5 mb-4">Generate report</h2>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('academics.reports.show') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="type" class="form-label">Report type</label>
                    <select name="type" id="type" class="form-select" required>
                        <option value="batch_progress" {{ request('type') === 'batch_progress' ? 'selected' : '' }}>Batch progress</option>
                        <option value="faculty_performance" {{ request('type') === 'faculty_performance' ? 'selected' : '' }}>Faculty performance</option>
                        <option value="topic_completion" {{ request('type') === 'topic_completion' ? 'selected' : '' }}>Topic completion</option>
                        <option value="student_submission" {{ request('type') === 'student_submission' ? 'selected' : '' }}>Student submission</option>
                    </select>
                </div>
                @if($institutions->isNotEmpty())
                <div class="col-md-4">
                    <label for="institution_id" class="form-label">Institution</label>
                    <select name="institution_id" id="institution_id" class="form-select">
                        <option value="">All institutions</option>
                        @foreach($institutions as $inst)
                            <option value="{{ $inst->id }}" {{ request('institution_id') == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                @if($batches->isNotEmpty())
                <div class="col-md-4">
                    <label for="batch_id" class="form-label">Batch</label>
                    <select name="batch_id" id="batch_id" class="form-select">
                        <option value="">All batches</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }} ({{ $b->institution->name ?? '—' }})</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-chart-bar me-1"></i>View report</button>
                    <a href="{{ route('academics.reports.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

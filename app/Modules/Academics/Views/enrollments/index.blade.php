@extends('auth::layout')

@section('title', 'Pending enrollments - Academics')
@section('page-title', 'Pending enrollments')

@section('content')
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    @include('academics::partials.mobile-list-hero', ['title' => 'Enrollments', 'lede' => 'Review pending student applications.'])

    <h2 class="h5 mb-4 d-none d-md-block">Pending student enrollments</h2>

    @if(isset($institutions) && $institutions->isNotEmpty())
    <form action="{{ route('academics.enrollments.index') }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-auto">
            <label class="form-label small text-muted mb-0">Institution</label>
            <select name="institution_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach($institutions as $inst)
                    <option value="{{ $inst->id }}" @selected((string) request('institution_id') === (string) $inst->id)>{{ $inst->name }}</option>
                @endforeach
            </select>
        </div>
    </form>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($applications->isEmpty())
                @include('academics::partials.mobile-empty-state', [
                    'icon' => 'fa-user-clock',
                    'title' => 'All caught up',
                    'text' => 'No pending enrollment requests right now.',
                ])
                <p class="text-muted p-4 mb-0 d-none d-md-block">No pending enrollment requests.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Institute</th>
                                <th>Requested</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applications as $app)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $app->user->name ?? '—' }}</div>
                                    <div class="small text-muted">{{ $app->user->phone ?? $app->user->email }}</div>
                                </td>
                                <td>{{ $app->institution->name ?? '—' }}</td>
                                <td>{{ $app->created_at->format('M d, Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('academics.enrollments.show', $app) }}" class="btn btn-sm btn-primary">Review</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">{{ $applications->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

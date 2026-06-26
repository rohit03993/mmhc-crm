@extends('auth::layout')

@section('title', 'Subjects - Academics')
@section('page-title', 'Subjects')

@section('content')
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    @include('academics::partials.mobile-list-hero', ['title' => 'Subjects', 'lede' => 'Courses within each batch.'])

    <form action="{{ route('academics.subjects.index') }}" method="GET" class="d-md-none mb-3">
        <select name="batch_id" class="form-select" onchange="this.form.submit()">
            <option value="">All batches</option>
            @foreach($batches as $b)
                <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }} ({{ $b->institution->name ?? '' }})</option>
            @endforeach
        </select>
    </form>

    <div class="d-none d-md-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 academics-page-toolbar">
        <h2 class="h5 mb-0">Subjects</h2>
        <div class="d-flex flex-wrap gap-2">
            <form action="{{ route('academics.subjects.index') }}" method="GET" class="d-inline">
                <select name="batch_id" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                    <option value="">All batches</option>
                    @foreach($batches as $b)
                        <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }} ({{ $b->institution->name ?? '' }})</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('academics.subjects.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Subject</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($subjects->isEmpty())
                @include('academics::partials.mobile-empty-state', [
                    'icon' => 'fa-book-open',
                    'title' => 'No subjects',
                    'text' => 'Create a subject under a batch.',
                    'actionUrl' => route('academics.subjects.create'),
                    'actionLabel' => 'Add subject',
                ])
                <p class="text-muted p-4 mb-0 d-none d-md-block">No subjects yet. Create one under a batch.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Batch</th>
                                <th>Faculty</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjects as $subject)
                            <tr>
                                <td>{{ $subject->name }}</td>
                                <td><code>{{ $subject->code ?? '—' }}</code></td>
                                <td>{{ $subject->batch->name ?? '—' }} <small class="text-muted">({{ $subject->batch->institution->name ?? '' }})</small></td>
                                <td>{{ $subject->faculty->pluck('name')->join(', ') ?: '—' }}</td>
                                <td>
                                    @if($subject->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('academics.subjects.edit', $subject) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('academics.subjects.destroy', $subject) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this subject?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $subjects->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

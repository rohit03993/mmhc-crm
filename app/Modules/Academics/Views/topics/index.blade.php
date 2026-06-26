@extends('auth::layout')

@section('title', 'Topics - Academics')
@section('page-title', 'Topics')

@section('content')
@php
    use App\Modules\Academics\Support\AcademicsTaxonomy;
@endphp
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    @include('academics::partials.mobile-list-hero', ['title' => 'Topics', 'lede' => 'Syllabus units under each subject.'])

    <form action="{{ route('academics.topics.index') }}" method="GET" class="d-md-none mb-3">
        <select name="subject_id" class="form-select" onchange="this.form.submit()">
            <option value="">All subjects</option>
            @foreach($subjects as $s)
                <option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->batch->name ?? '' }})</option>
            @endforeach
        </select>
    </form>

    <div class="d-none d-md-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 academics-page-toolbar">
        <h2 class="h5 mb-0">Topics</h2>
        <div class="d-flex flex-wrap gap-2">
            <form action="{{ route('academics.topics.index') }}" method="GET" class="d-inline">
                <select name="subject_id" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                    <option value="">All subjects</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->batch->name ?? '' }})</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('academics.topics.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Topic</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($topics->isEmpty())
                @include('academics::partials.mobile-empty-state', [
                    'icon' => 'fa-book',
                    'title' => 'No topics yet',
                    'text' => 'Create a topic under a subject.',
                    'actionUrl' => route('academics.topics.create'),
                    'actionLabel' => 'Add topic',
                ])
                <p class="text-muted p-4 mb-0 d-none d-md-block">No topics yet. Create one under a subject (faculty see only their assigned subjects).</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topics as $topic)
                            <tr>
                                <td>{{ $topic->sort_order }}</td>
                                <td>{{ $topic->name }}</td>
                                <td>{{ $topic->subject->name ?? '—' }} <small class="text-muted">({{ $topic->subject->batch->name ?? '' }})</small></td>
                                <td class="small text-muted">{{ Str::limit(AcademicsTaxonomy::teachingMethodLabels($topic->teaching_method_keys), 48) }}</td>
                                <td>
                                    @if($topic->is_completed)
                                        <span class="badge bg-success">Completed</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('academics.topics.edit', $topic) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('academics.topics.destroy', $topic) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this topic?');">
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
                    {{ $topics->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

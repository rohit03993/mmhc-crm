@extends('auth::layout')

@section('title', 'Assignments - Academics')
@section('page-title', 'Assignments')

@section('content')
@php
    use App\Modules\Academics\Support\AcademicsTaxonomy;
@endphp
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    @include('academics::partials.mobile-list-hero', ['title' => 'Assignments', 'lede' => 'Tasks linked to topics and due dates.'])

    <form action="{{ route('academics.assignments.index') }}" method="GET" class="d-md-none mb-3">
        <select name="topic_id" class="form-select" onchange="this.form.submit()">
            <option value="">All topics</option>
            @foreach($topics as $t)
                <option value="{{ $t->id }}" {{ request('topic_id') == $t->id ? 'selected' : '' }}>{{ $t->name }} ({{ $t->subject->name ?? '' }})</option>
            @endforeach
        </select>
    </form>

    <div class="d-none d-md-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 academics-page-toolbar">
        <h2 class="h5 mb-0">Assignments</h2>
        <div class="d-flex flex-wrap gap-2">
            <form action="{{ route('academics.assignments.index') }}" method="GET" class="d-inline">
                <select name="topic_id" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                    <option value="">All topics</option>
                    @foreach($topics as $t)
                        <option value="{{ $t->id }}" {{ request('topic_id') == $t->id ? 'selected' : '' }}>{{ $t->name }} ({{ $t->subject->name ?? '' }})</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('academics.assignments.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Assignment</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($assignments->isEmpty())
                @include('academics::partials.mobile-empty-state', [
                    'icon' => 'fa-tasks',
                    'title' => 'No assignments',
                    'text' => 'Create one under a topic.',
                    'actionUrl' => route('academics.assignments.create'),
                    'actionLabel' => 'Add assignment',
                ])
                <p class="text-muted p-4 mb-0 d-none d-md-block">No assignments yet. Create one under a topic.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Topic</th>
                                <th>Due date</th>
                                <th>Attachments</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $a)
                            <tr>
                                <td>
                                    <a href="{{ route('academics.assignments.show', $a) }}">{{ $a->title }}</a>
                                    @if($a->isPastDue())
                                        <span class="badge bg-danger ms-1">Overdue</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ AcademicsTaxonomy::assignmentTypeLabel($a->assignment_type) }}</span></td>
                                <td>{{ $a->topic->name ?? '—' }} <small class="text-muted">({{ $a->topic->subject->name ?? '' }})</small></td>
                                <td>{{ $a->due_date ? $a->due_date->format('M d, Y') : '—' }}</td>
                                <td>{{ count($a->attachments ?? []) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('academics.assignments.show', $a) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    @if(in_array(auth()->user()->role, ['institution_admin', 'faculty']))
                                    <a href="{{ route('academics.assignments.submissions', $a) }}" class="btn btn-sm btn-outline-info">Submissions</a>
                                    @endif
                                    <a href="{{ route('academics.assignments.edit', $a) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('academics.assignments.destroy', $a) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this assignment?');">
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
                    {{ $assignments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

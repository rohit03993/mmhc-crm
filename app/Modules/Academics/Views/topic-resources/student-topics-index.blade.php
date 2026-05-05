@extends('auth::layout')

@section('title', 'Learning resources - Academics')
@section('page-title', 'Learning resources')

@section('content')
<div class="container-fluid py-3">
    <p class="text-muted mb-4">Materials shared by faculty for topics in your batch. Open a topic to view videos and files.</p>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($topics->isEmpty())
                <p class="text-muted p-4 mb-0">No topics yet. Topics appear when your college adds subjects and curriculum.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Topic</th>
                                <th>Subject</th>
                                <th>Batch</th>
                                <th class="text-end">Library</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topics as $t)
                            <tr>
                                <td class="fw-medium">{{ $t->name }}</td>
                                <td>{{ $t->subject->name ?? '—' }}</td>
                                <td>{{ $t->subject->batch->name ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('academics.topics.student-library', $t) }}" class="btn btn-sm btn-outline-primary">Open resources</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <a href="{{ route('academics.my-assignments') }}" class="btn btn-outline-secondary btn-sm mt-3">Back to my assignments</a>
</div>
@endsection

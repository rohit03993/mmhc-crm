@extends('auth::layout')

@section('title', 'My Assignments - Academics')
@section('page-title', 'My Assignments')

@section('content')
<div class="container-fluid py-3">
    <h2 class="h5 mb-4">Assignments for your batch</h2>

    <div class="card">
        <div class="card-body p-0">
            @if($assignments->isEmpty())
                <p class="text-muted p-4 mb-0">No assignments assigned to you yet. Assignments appear when faculty create them for your batch's subjects.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Assignment</th>
                                <th>Topic · Subject</th>
                                <th>Due date</th>
                                <th>Status</th>
                                <th>Resources</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $a)
                            @php
                                $sub = $a->submissions->first();
                            @endphp
                            <tr>
                                <td>{{ $a->title }}</td>
                                <td>{{ $a->topic->name ?? '—' }} · {{ $a->topic->subject->name ?? '—' }}</td>
                                <td>{{ $a->due_date ? $a->due_date->format('M d, Y') : '—' }}</td>
                                <td>
                                    <a href="{{ route('academics.topics.student-library', $a->topic_id) }}" class="btn btn-sm btn-outline-secondary">Topic library</a>
                                </td>
                                <td>
                                    @if($sub)
                                        <span class="badge bg-success">Submitted</span>
                                        @if($sub->isLate())
                                            <span class="badge bg-warning text-dark">Late</span>
                                        @endif
                                        @if($sub->checklist_points_possible !== null && (float) $sub->checklist_points_possible > 0)
                                            <span class="badge bg-light text-dark border ms-1">Checklist {{ $sub->checklist_points_earned }}/{{ $sub->checklist_points_possible }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @foreach($a->exams as $lex)
                                        @if($examAccess->canTake(auth()->user(), $lex))
                                            <a href="{{ route('academics.exams.show', $lex) }}" class="btn btn-sm btn-outline-success mb-1">Quiz</a>
                                        @endif
                                    @endforeach
                                    @if($sub)
                                        @if($sub->file_path)
                                            <a href="{{ route('academics.submissions.download', $sub) }}" class="btn btn-sm btn-outline-secondary">Download mine</a>
                                        @endif
                                        <a href="{{ route('academics.submit.form', $a) }}" class="btn btn-sm btn-outline-primary">Re-submit</a>
                                    @else
                                        <a href="{{ route('academics.submit.form', $a) }}" class="btn btn-sm btn-primary">Submit</a>
                                    @endif
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

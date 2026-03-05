@extends('auth::layout')

@section('title', 'Submissions - ' . $assignment->title)
@section('page-title', 'Submissions')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="h5 mb-1">{{ $assignment->title }}</h2>
            <p class="text-muted small mb-0">
                Topic: {{ $assignment->topic->name ?? '—' }} · Subject: {{ $assignment->topic->subject->name ?? '—' }} · Batch: {{ $assignment->topic->subject->batch->name ?? '—' }}
            </p>
        </div>
        <a href="{{ route('academics.assignments.show', $assignment) }}" class="btn btn-outline-secondary">Back to assignment</a>
    </div>

    @php
        $total = $students->count();
        $submitted = $submissions->count();
        $pct = $total ? round($submitted / $total * 100) : 0;
    @endphp
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Submission status</h5>
            <p class="mb-0">{{ $submitted }} of {{ $total }} students submitted ({{ $pct }}%).</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Submitted at</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        @php
                            $sub = $submissions->get($student->id);
                        @endphp
                        <tr>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->email }}</td>
                            <td>
                                @if($sub)
                                    <span class="badge bg-success">Submitted</span>
                                    @if($sub->isLate())
                                        <span class="badge bg-warning text-dark">Late</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Pending</span>
                                @endif
                            </td>
                            <td>{{ $sub ? $sub->submitted_at->format('M d, Y H:i') : '—' }}</td>
                            <td class="text-end">
                                @if($sub)
                                    <a href="{{ route('academics.submissions.download', $sub) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

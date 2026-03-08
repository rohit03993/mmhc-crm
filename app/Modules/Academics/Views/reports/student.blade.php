@extends('auth::layout')

@section('title', $student->name . ' – Student Report - Academics')
@section('page-title', 'Student report')

@section('content')
<div class="container-fluid py-3 py-md-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
        <h2 class="h5 mb-0">Full report: {{ $student->name }}</h2>
        <a href="{{ route('academics.reports.show', ['type' => 'student_submission']) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to student report</a>
    </div>

    {{-- Institute & batch --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3"><i class="fas fa-university text-primary me-2"></i>Institute &amp; batch</h5>
            <div class="row g-2">
                <div class="col-12 col-md-6">
                    <span class="text-muted small">Institution</span>
                    <p class="mb-0 fw-medium">{{ $institution->name ?? '—' }}</p>
                </div>
                <div class="col-12 col-md-6">
                    <span class="text-muted small">Current batch(es)</span>
                    <p class="mb-0 fw-medium">{{ $batches->pluck('name')->join(', ') ?: '—' }}</p>
                </div>
                <div class="col-12 col-md-6">
                    <span class="text-muted small">Email</span>
                    <p class="mb-0">{{ $student->email }}</p>
                </div>
                <div class="col-12 col-md-6">
                    <span class="text-muted small">Unique ID</span>
                    <p class="mb-0">{{ $student->unique_id ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- SPI (overall) --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm border-primary">
                <div class="card-body text-center py-3">
                    <p class="small text-muted mb-1">SPI (Progress)</p>
                    <p class="h4 mb-0 text-primary">{{ $spi }}%</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance: period filter + summary for selected period --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-3">
                <h5 class="card-title mb-0"><i class="fas fa-calendar-check text-info me-2"></i>Attendance <span class="fw-normal text-muted small">({{ $periodLabel ?? 'All time' }})</span></h5>
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('academics.reports.student', ['user' => $student->id, 'period' => 'this_month']) }}" class="btn {{ ($currentPeriod ?? 'this_month') === 'this_month' ? 'btn-primary' : 'btn-outline-secondary' }}">This month</a>
                    <a href="{{ route('academics.reports.student', ['user' => $student->id, 'period' => 'last_month']) }}" class="btn {{ ($currentPeriod ?? '') === 'last_month' ? 'btn-primary' : 'btn-outline-secondary' }}">Last month</a>
                    <a href="{{ route('academics.reports.student', ['user' => $student->id, 'period' => 'all']) }}" class="btn {{ ($currentPeriod ?? '') === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">All time</a>
                </div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-4 col-md-2">
                    <div class="rounded border bg-light p-2 text-center">
                        <p class="small text-muted mb-0">Days</p>
                        <p class="h5 mb-0">{{ $attendanceStats['total'] }}</p>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="rounded border border-success bg-success bg-opacity-10 p-2 text-center">
                        <p class="small text-muted mb-0">Present</p>
                        <p class="h5 mb-0 text-success">{{ $attendanceStats['present'] }}</p>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="rounded border border-danger bg-danger bg-opacity-10 p-2 text-center">
                        <p class="small text-muted mb-0">Absent</p>
                        <p class="h5 mb-0 text-danger">{{ $attendanceStats['absent'] }}</p>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="rounded border border-warning bg-warning bg-opacity-10 p-2 text-center">
                        <p class="small text-muted mb-0">Leave</p>
                        <p class="h5 mb-0 text-warning">{{ $attendanceStats['leave'] }}</p>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="rounded border border-primary bg-primary bg-opacity-10 p-2 text-center">
                        <p class="small text-muted mb-0">Attendance %</p>
                        <p class="h5 mb-0 text-primary">{{ $attendanceStats['percentage'] }}%</p>
                    </div>
                </div>
            </div>
            @if($attendanceRows->isEmpty())
                <p class="text-muted mb-0">No attendance records yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Batch</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendanceRows->take(30) as $a)
                            <tr>
                                <td>{{ $a->date->format('M d, Y') }}</td>
                                <td>{{ $a->batch->name ?? '—' }}</td>
                                <td>
                                    @if($a->status === \App\Modules\Academics\Models\Attendance::STATUS_PRESENT)
                                        <span class="badge bg-success">Present</span>
                                    @elseif($a->status === \App\Modules\Academics\Models\Attendance::STATUS_ABSENT)
                                        <span class="badge bg-danger">Absent</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Leave</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($attendanceRows->count() > 30)
                    <p class="small text-muted mt-2 mb-0">Showing latest 30 of {{ $attendanceRows->count() }} records.</p>
                @endif
            @endif
        </div>
    </div>

    {{-- Assignments --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3"><i class="fas fa-tasks text-secondary me-2"></i>Assignments</h5>
            @if($eligibleAssignments->isEmpty())
                <p class="text-muted mb-0">No assignments assigned yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Assignment</th>
                                <th>Subject / Topic</th>
                                <th>Due date</th>
                                <th>Status</th>
                                <th>Submitted at</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($eligibleAssignments as $a)
                            @php $sub = $submissionsByAssignment->get($a->id); @endphp
                            <tr>
                                <td>{{ $a->title }}</td>
                                <td>{{ $a->topic->subject->name ?? '—' }} / {{ $a->topic->name ?? '—' }}</td>
                                <td>{{ $a->due_date ? $a->due_date->format('M d, Y') : '—' }}</td>
                                <td>
                                    @if($sub)
                                        <span class="badge bg-success">Submitted</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $sub && $sub->submitted_at ? $sub->submitted_at->format('M d, Y H:i') : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

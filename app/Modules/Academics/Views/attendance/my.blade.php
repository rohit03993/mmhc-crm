@extends('auth::layout')

@section('title', 'My Attendance - Academics')
@section('page-title', 'My Attendance')

@section('content')
<div class="container-fluid py-3">
    @if(isset($periodLabel))
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <p class="text-muted small mb-0">Attendance ({{ $periodLabel }})</p>
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ route('academics.attendance.my', ['period' => 'this_month']) }}" class="btn {{ ($currentPeriod ?? 'this_month') === 'this_month' ? 'btn-primary' : 'btn-outline-secondary' }}">This month</a>
            <a href="{{ route('academics.attendance.my', ['period' => 'last_month']) }}" class="btn {{ ($currentPeriod ?? '') === 'last_month' ? 'btn-primary' : 'btn-outline-secondary' }}">Last month</a>
            <a href="{{ route('academics.attendance.my', ['period' => 'all']) }}" class="btn {{ ($currentPeriod ?? '') === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">All time</a>
        </div>
    </div>
    @endif
    @if(isset($stats))
    <div class="row g-3 mb-4">
        <div class="col-6 col-md">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <div class="h4 mb-0">{{ $stats['total'] }}</div>
                    <small class="text-muted">Days</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card bg-success bg-opacity-10">
                <div class="card-body text-center">
                    <div class="h4 mb-0 text-success">{{ $stats['present'] }}</div>
                    <small class="text-muted">Present</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card bg-danger bg-opacity-10">
                <div class="card-body text-center">
                    <div class="h4 mb-0 text-danger">{{ $stats['absent'] }}</div>
                    <small class="text-muted">Absent</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card bg-warning bg-opacity-10">
                <div class="card-body text-center">
                    <div class="h4 mb-0 text-warning">{{ $stats['leave'] }}</div>
                    <small class="text-muted">Leave</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card bg-primary bg-opacity-10">
                <div class="card-body text-center">
                    <div class="h4 mb-0 text-primary">{{ $stats['percentage'] }}%</div>
                    <small class="text-muted">Attendance %</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            @if($attendances->isEmpty())
                <p class="text-muted p-4 mb-0">No attendance records yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Batch</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendances as $a)
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
                <div class="p-3 border-top">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

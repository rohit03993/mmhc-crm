@extends('auth::layout')

@section('title', 'My Attendance - Academics')
@section('page-title', 'My Attendance')

@section('content')
<div class="container-fluid py-3">
    @if(isset($stats))
    <div class="row g-3 mb-4">
        <div class="col-6 col-md">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <div class="h4 mb-0">{{ $stats['total'] }}</div>
                    <small class="text-muted">Total sessions</small>
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

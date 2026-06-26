@extends('auth::layout')

@section('title', 'My Attendance - Academics')
@section('page-title', 'My Attendance')

@section('content')
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    {{-- Mobile intro --}}
    <div class="acad-m-hero d-md-none">
        <p class="acad-m-hero__label">Your attendance</p>
        <h2 class="acad-m-hero__title">{{ $periodLabel ?? 'This month' }}</h2>
        @if(isset($stats))
            <div class="acad-attend-ring" aria-label="Attendance {{ $stats['percentage'] }} percent">
                <svg class="acad-attend-ring__svg" viewBox="0 0 120 120" aria-hidden="true">
                    <circle class="acad-attend-ring__track" cx="60" cy="60" r="52"></circle>
                    <circle class="acad-attend-ring__fill" cx="60" cy="60" r="52"
                            style="--pct: {{ min(100, max(0, (float) $stats['percentage'])) }}"></circle>
                </svg>
                <div class="acad-attend-ring__center">
                    <span class="acad-attend-ring__value">{{ $stats['percentage'] }}%</span>
                    <span class="acad-attend-ring__hint">Present rate</span>
                </div>
            </div>
        @endif
    </div>

    @if(isset($periodLabel))
    <div class="acad-period-pills mb-3">
        <a href="{{ route('academics.attendance.my', ['period' => 'this_month']) }}"
           class="acad-period-pill {{ ($currentPeriod ?? 'this_month') === 'this_month' ? 'is-active' : '' }}">This month</a>
        <a href="{{ route('academics.attendance.my', ['period' => 'last_month']) }}"
           class="acad-period-pill {{ ($currentPeriod ?? '') === 'last_month' ? 'is-active' : '' }}">Last month</a>
        <a href="{{ route('academics.attendance.my', ['period' => 'all']) }}"
           class="acad-period-pill {{ ($currentPeriod ?? '') === 'all' ? 'is-active' : '' }}">All time</a>
    </div>
    @endif

    @if(isset($stats))
    <div class="acad-stat-chips mb-3">
        <div class="acad-stat-chip acad-stat-chip--neutral">
            <span class="acad-stat-chip__val">{{ $stats['total'] }}</span>
            <span class="acad-stat-chip__lbl">Days</span>
        </div>
        <div class="acad-stat-chip acad-stat-chip--ok">
            <span class="acad-stat-chip__val">{{ $stats['present'] }}</span>
            <span class="acad-stat-chip__lbl">Present</span>
        </div>
        <div class="acad-stat-chip acad-stat-chip--bad">
            <span class="acad-stat-chip__val">{{ $stats['absent'] }}</span>
            <span class="acad-stat-chip__lbl">Absent</span>
        </div>
        <div class="acad-stat-chip acad-stat-chip--warn">
            <span class="acad-stat-chip__val">{{ $stats['leave'] }}</span>
            <span class="acad-stat-chip__lbl">Leave</span>
        </div>
    </div>
    @endif

    <p class="d-none d-md-block text-muted small mb-3">Attendance ({{ $periodLabel ?? '' }})</p>

    @if($attendances->isEmpty())
        @include('academics::partials.mobile-empty-state', [
            'icon' => 'fa-calendar-check',
            'title' => 'No attendance yet',
            'text' => 'Records appear when your college marks daily attendance for your batch.',
        ])
        <p class="text-muted p-4 mb-0 d-none d-md-block">No attendance records yet.</p>
    @else
        {{-- Mobile timeline --}}
        <div class="acad-timeline d-md-none">
            @foreach($attendances as $a)
                @php
                    $statusClass = match($a->status) {
                        \App\Modules\Academics\Models\Attendance::STATUS_PRESENT => 'is-present',
                        \App\Modules\Academics\Models\Attendance::STATUS_ABSENT => 'is-absent',
                        default => 'is-leave',
                    };
                    $statusLabel = match($a->status) {
                        \App\Modules\Academics\Models\Attendance::STATUS_PRESENT => 'Present',
                        \App\Modules\Academics\Models\Attendance::STATUS_ABSENT => 'Absent',
                        default => 'Leave',
                    };
                @endphp
                <article class="acad-timeline__item {{ $statusClass }}">
                    <div class="acad-timeline__dot" aria-hidden="true"></div>
                    <div class="acad-timeline__body">
                        <div class="acad-timeline__row">
                            <time class="acad-timeline__date">{{ $a->date->format('D, M j') }}</time>
                            <span class="acad-timeline__badge">{{ $statusLabel }}</span>
                        </div>
                        <p class="acad-timeline__sub mb-0">{{ $a->batch->name ?? 'Batch' }}</p>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Desktop table --}}
        <div class="card d-none d-md-block">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 mmhc-no-mobile-cards">
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
            </div>
        </div>
        <div class="mt-3">{{ $attendances->links() }}</div>
    @endif
</div>
@endsection

@extends('auth::layout')

@section('title', 'My Assignments - Academics')
@section('page-title', 'My Assignments')

@section('content')
@php
    use App\Modules\Academics\Support\StudentAssignmentStatus;
@endphp
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    <div class="acad-m-hero d-md-none">
        <p class="acad-m-hero__label">Coursework</p>
        <h2 class="acad-m-hero__title">My tasks</h2>
        <p class="acad-m-hero__lede">Tap a task to read instructions, learn, and submit.</p>
    </div>

    @isset($spiBreakdown)
        <div class="acad-homework-spi acad-homework-spi--compact mb-3">
            <div>
                <span class="acad-homework-spi__label">SPI progress</span>
                <strong class="acad-homework-spi__value">{{ $spiBreakdown['percent'] }}%</strong>
            </div>
            <p class="acad-homework-spi__hint mb-0">
                {{ $spiBreakdown['verified'] }}/{{ $spiBreakdown['total'] }} credited
                @if($spiBreakdown['not_submitted'] > 0)
                    · {{ $spiBreakdown['not_submitted'] }} pending
                @endif
            </p>
        </div>
    @endisset

    @if($assignments->isEmpty())
        @include('academics::partials.mobile-empty-state', [
            'icon' => 'fa-tasks',
            'title' => 'No assignments yet',
            'text' => 'Faculty will post tasks for your batch subjects here.',
            'actionUrl' => route('academics.learning-resources'),
            'actionLabel' => 'Browse learning resources',
        ])
        <p class="text-muted p-4 mb-0 d-none d-md-block">No assignments assigned to you yet.</p>
    @else
        <div class="acad-assignment-list d-md-none">
            @foreach($assignments as $a)
                @php
                    $sub = $a->submissions->first();
                    $st = StudentAssignmentStatus::for($a, $sub, $mentorVerification);
                @endphp
                <a href="{{ route('academics.my-assignments.show', $a) }}" class="acad-assignment-card acad-assignment-card--link">
                    <div class="acad-assignment-card__top">
                        <h3 class="acad-assignment-card__title">{{ $a->title }}</h3>
                        <span class="acad-status-pill acad-status-pill--{{ $st['pill'] }}">{{ $st['label'] }}</span>
                    </div>
                    <p class="acad-assignment-card__topic mb-1">
                        {{ $a->topic->name ?? '—' }} · {{ $a->topic->subject->name ?? '—' }}
                    </p>
                    <p class="acad-assignment-card__due mb-0">
                        <i class="far fa-calendar" aria-hidden="true"></i>
                        Due {{ $a->due_date ? $a->due_date->format('M j, Y') : '—' }}
                    </p>
                    <span class="acad-assignment-card__open">Open task <i class="fas fa-chevron-right" aria-hidden="true"></i></span>
                </a>
            @endforeach
        </div>

        <div class="card d-none d-md-block">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 mmhc-no-mobile-cards">
                        <thead class="table-light">
                            <tr>
                                <th>Assignment</th>
                                <th>Topic · Subject</th>
                                <th>Due date</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $a)
                            @php
                                $sub = $a->submissions->first();
                                $st = StudentAssignmentStatus::for($a, $sub, $mentorVerification);
                            @endphp
                            <tr>
                                <td>{{ $a->title }}</td>
                                <td>{{ $a->topic->name ?? '—' }} · {{ $a->topic->subject->name ?? '—' }}</td>
                                <td>{{ $a->due_date ? $a->due_date->format('M d, Y') : '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $st['pill'] === 'ok' ? 'success' : ($st['pill'] === 'warn' ? 'warning text-dark' : 'secondary') }}">{{ $st['label'] }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('academics.my-assignments.show', $a) }}" class="btn btn-sm btn-primary">Open</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">{{ $assignments->links() }}</div>
            </div>
        </div>
        <div class="mt-3 d-md-none">{{ $assignments->links() }}</div>
    @endif
</div>
@endsection

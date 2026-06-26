@extends('auth::layout')

@section('title', 'Learning resources - Academics')
@section('page-title', 'Learning resources')

@section('content')
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    <div class="acad-m-hero d-md-none">
        <p class="acad-m-hero__label">Study materials</p>
        <h2 class="acad-m-hero__title">Learning resources</h2>
        <p class="acad-m-hero__lede">Videos and files shared by faculty for your batch topics.</p>
    </div>

    <p class="text-muted mb-4 d-none d-md-block">Materials shared by faculty for topics in your batch.</p>

    @if($topics->isEmpty())
        @include('academics::partials.mobile-empty-state', [
            'icon' => 'fa-photo-video',
            'title' => 'No topics yet',
            'text' => 'Topics appear when your college adds subjects and curriculum to your batch.',
            'actionUrl' => route('academics.dashboard'),
            'actionLabel' => 'Back to home',
        ])
        <p class="text-muted p-4 mb-0 d-none d-md-block">No topics yet.</p>
    @else
        <div class="acad-topic-list d-md-none">
            @foreach($topics as $t)
                <a href="{{ route('academics.topics.student-library', $t) }}" class="acad-topic-card">
                    <div class="acad-topic-card__icon" aria-hidden="true">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="acad-topic-card__body">
                        <h3 class="acad-topic-card__title">{{ $t->name }}</h3>
                        <p class="acad-topic-card__sub mb-0">{{ $t->subject->name ?? 'Subject' }} · {{ $t->subject->batch->name ?? 'Batch' }}</p>
                    </div>
                    <i class="fas fa-chevron-right acad-topic-card__chev" aria-hidden="true"></i>
                </a>
            @endforeach
        </div>

        <div class="card shadow-sm d-none d-md-block">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 mmhc-no-mobile-cards">
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
            </div>
        </div>
    @endif

    <a href="{{ route('academics.my-assignments') }}" class="acad-text-link mt-3 d-inline-flex d-md-none">
        <i class="fas fa-tasks" aria-hidden="true"></i> My assignments
    </a>
    <a href="{{ route('academics.my-assignments') }}" class="btn btn-outline-secondary btn-sm mt-3 d-none d-md-inline-block">Back to my assignments</a>
</div>
@endsection

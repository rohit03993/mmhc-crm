@extends('auth::layout')

@section('title', 'Open classrooms')
@section('page-title', 'Open classrooms')

@section('content')
@php
    $isLearner = in_array(auth()->user()->role, ['student', 'nurse', 'caregiver'], true);
    $joinedIds = $joinedIds ?? [];
    $joinedCount = $joined->count();
    $mineCount = $mine->count();
@endphp
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    <div class="acad-m-hero d-md-none">
        <p class="acad-m-hero__label">Open learning</p>
        <h2 class="acad-m-hero__title">Classrooms</h2>
        <p class="acad-m-hero__lede mb-0">
            @if($isLearner)
                Browse teachers’ public rooms and join in one tap — no college approval needed.
            @else
                Create a room or browse what learners can join.
            @endif
        </p>
    </div>

    <p class="text-muted mb-3 d-none d-md-block">
        @if($isLearner)
            Browse teachers’ public classrooms and join to access notes and assignments.
        @else
            Create a classroom or browse what learners can join.
        @endif
    </p>

    @if($isLearner)
        <div class="acad-oc-tip d-md-none mb-3">
            <div class="acad-oc-tip__icon" aria-hidden="true"><i class="fas fa-hand-pointer"></i></div>
            <div class="acad-oc-tip__body">
                <strong class="acad-oc-tip__title">How to join</strong>
                <p class="acad-oc-tip__text mb-0">Pick a classroom under <em>Browse</em>, tap <strong>Join</strong>, then open it for notes and tasks.</p>
                @if(in_array(auth()->user()->role, ['nurse', 'caregiver'], true))
                    <a href="{{ route('academics.mentorship.index') }}" class="acad-oc-tip__link mt-2 d-inline-flex align-items-center gap-1">
                        <i class="fas fa-hands-helping" aria-hidden="true"></i> Open Mentors
                    </a>
                @endif
            </div>
        </div>
        @if($joinedCount > 0)
            <div class="acad-oc-stat-row d-md-none mb-3">
                <div class="acad-oc-stat">
                    <span class="acad-oc-stat__value">{{ $joinedCount }}</span>
                    <span class="acad-oc-stat__label">Joined</span>
                </div>
                <div class="acad-oc-stat">
                    <span class="acad-oc-stat__value">{{ $browse->total() }}</span>
                    <span class="acad-oc-stat__label">Available</span>
                </div>
            </div>
        @endif
    @endif

    @if(auth()->user()->role === 'faculty')
        <div class="d-none d-md-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('academics.open-classrooms.create') }}" class="acad-btn-primary">
                <i class="fas fa-plus" aria-hidden="true"></i> Create classroom
            </a>
        </div>
        <a href="{{ route('academics.open-classrooms.create') }}" class="acad-fab d-md-none" aria-label="Create classroom">
            <i class="fas fa-plus" aria-hidden="true"></i>
        </a>
    @endif

    <nav class="acad-oc-segment mb-3" aria-label="Classroom filters">
        <a class="acad-oc-segment__item {{ $tab === 'browse' ? 'is-active' : '' }}" href="{{ route('academics.open-classrooms.index', ['tab' => 'browse']) }}">Browse</a>
        @if($isLearner || $joinedCount > 0)
            <a class="acad-oc-segment__item {{ $tab === 'joined' ? 'is-active' : '' }}" href="{{ route('academics.open-classrooms.index', ['tab' => 'joined']) }}">
                Joined
                @if($joinedCount > 0)
                    <span class="acad-oc-segment__badge">{{ $joinedCount }}</span>
                @endif
            </a>
        @endif
        @if(auth()->user()->role === 'faculty')
            <a class="acad-oc-segment__item {{ $tab === 'mine' ? 'is-active' : '' }}" href="{{ route('academics.open-classrooms.index', ['tab' => 'mine']) }}">
                Mine
                @if($mineCount > 0)
                    <span class="acad-oc-segment__badge">{{ $mineCount }}</span>
                @endif
            </a>
        @endif
    </nav>

    @if($tab === 'joined')
        @if($joined->isEmpty())
            @include('academics::partials.mobile-empty-state', [
                'icon' => 'fa-user-plus',
                'title' => 'No classrooms joined yet',
                'text' => 'Browse public classrooms and tap Join to start learning.',
                'actionUrl' => route('academics.open-classrooms.index', ['tab' => 'browse']),
                'actionLabel' => 'Browse classrooms',
            ])
            <p class="text-muted p-4 mb-0 d-none d-md-block">You have not joined any classrooms yet.</p>
        @else
            <div class="acad-oc-list">
                @foreach($joined as $c)
                    @include('academics::open-classrooms.partials.card', ['classroom' => $c, 'joined' => true])
                @endforeach
            </div>
        @endif
    @elseif($tab === 'mine')
        @if($mine->isEmpty())
            @include('academics::partials.mobile-empty-state', [
                'icon' => 'fa-chalkboard',
                'title' => 'No classrooms yet',
                'text' => 'Create your first open classroom and share notes with learners.',
                'actionUrl' => route('academics.open-classrooms.create'),
                'actionLabel' => 'Create classroom',
            ])
            <p class="text-muted p-4 mb-0 d-none d-md-block">You have not created any classrooms yet.</p>
        @else
            <div class="acad-oc-list">
                @foreach($mine as $c)
                    @include('academics::open-classrooms.partials.card', ['classroom' => $c, 'owner' => true])
                @endforeach
            </div>
        @endif
    @else
        @if($browse->isEmpty())
            @include('academics::partials.mobile-empty-state', [
                'icon' => 'fa-door-open',
                'title' => 'No public classrooms yet',
                'text' => 'Teachers can create open classrooms and share notes & assignments here.',
            ])
            <p class="text-muted p-4 mb-0 d-none d-md-block">No public classrooms yet.</p>
        @else
            <div class="acad-oc-list">
                @foreach($browse as $c)
                    @php
                        $isOwner = (int) $c->owner_id === (int) auth()->id();
                        $isJoined = in_array($c->id, $joinedIds, true);
                        $canJoinCard = $isLearner && ! $isOwner && ! $isJoined && $c->is_active;
                    @endphp
                    @include('academics::open-classrooms.partials.card', [
                        'classroom' => $c,
                        'owner' => $isOwner,
                        'joined' => $isJoined,
                        'canJoin' => $canJoinCard,
                    ])
                @endforeach
            </div>
            @if($browse->hasPages())
                <div class="acad-oc-pagination mt-3">{{ $browse->links() }}</div>
            @endif
        @endif
    @endif
</div>
@endsection

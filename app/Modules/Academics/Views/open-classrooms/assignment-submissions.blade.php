@extends('auth::layout')

@section('title', 'Submissions')
@section('page-title', 'Submissions')

@section('content')
@php
    $total = $members->count();
    $submitted = $submissions->count();
    $pct = $total ? round($submitted / $total * 100) : 0;
@endphp
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    <div class="acad-m-hero d-md-none">
        <h2 class="acad-m-hero__title">{{ $assignment->title }}</h2>
        <p class="acad-m-hero__lede mb-0">{{ $submitted }}/{{ $total }} submitted ({{ $pct }}%)</p>
    </div>

    <div class="acad-submission-stats mb-3">
        <div class="acad-submission-stat"><span class="acad-submission-stat__value">{{ $submitted }}/{{ $total }}</span><span class="acad-submission-stat__label">Submitted</span></div>
        <div class="acad-submission-stat"><span class="acad-submission-stat__value">{{ $pct }}%</span><span class="acad-submission-stat__label">Done</span></div>
    </div>

    <div class="acad-faculty-submission-list">
        @foreach($members as $member)
            @php $sub = $submissions->get($member->id); @endphp
            <article class="acad-faculty-submission-card">
                <div class="acad-faculty-submission-card__top">
                    <h3 class="acad-faculty-submission-card__name">{{ $member->name }}</h3>
                    <span class="acad-status-pill acad-status-pill--{{ $sub ? 'ok' : 'pending' }}">{{ $sub ? 'Submitted' : 'Pending' }}</span>
                </div>
                @if($sub)
                    <p class="acad-faculty-submission-card__meta">{{ $sub->submitted_at->format('M j, Y g:i A') }}</p>
                    @if($sub->file_path)
                        <a href="{{ route('academics.open-classrooms.submissions.download', [$openClassroom, $sub]) }}" class="acad-btn-primary acad-btn-primary--sm">Download</a>
                    @endif
                @endif
            </article>
        @endforeach
    </div>

    <a href="{{ route('academics.open-classrooms.assignments.show', [$openClassroom, $assignment]) }}" class="acad-text-link mt-3 d-inline-flex">Back to assignment</a>
</div>
@endsection

@extends('auth::layout')

@section('title', 'Topic library')
@section('page-title', $topic->name)

@section('content')
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    <a href="{{ route('academics.learning-resources') }}" class="acad-text-link d-md-none mb-3">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> All topics
    </a>

    <div class="acad-m-hero d-md-none">
        <p class="acad-m-hero__label">Learning</p>
        <h2 class="acad-m-hero__title">{{ $topic->name }}</h2>
        <p class="acad-m-hero__lede">{{ $topic->subject->name ?? '' }}@if(optional($topic->subject->batch)->name) · {{ $topic->subject->batch->name }}@endif</p>
    </div>

    <p class="text-muted small mb-4 d-none d-md-block">{{ $topic->subject->name ?? '' }} · {{ $topic->subject->batch->name ?? '' }}</p>

    @if($topic->resources->isEmpty())
        @include('academics::partials.mobile-empty-state', [
            'icon' => 'fa-book-open',
            'title' => 'No resources yet',
            'text' => 'Your faculty will add videos and files for this topic.',
            'actionUrl' => route('academics.learning-resources'),
            'actionLabel' => 'Browse topics',
        ])
        <p class="text-muted p-4 mb-0 d-none d-md-block">No resources for this topic yet.</p>
    @else
        <div class="acad-resource-list d-md-none">
            @foreach($topic->resources as $r)
                <article class="acad-resource-card">
                    <h3 class="acad-resource-card__title">{{ $r->title }}</h3>
                    @if($r->description)
                        <p class="acad-resource-card__desc">{{ $r->description }}</p>
                    @endif
                    <div class="acad-resource-card__actions">
                        @if($r->resource_type === \App\Modules\Academics\Models\TopicResource::TYPE_VIDEO_LINK && $r->video_url)
                            <a href="{{ $r->video_url }}" target="_blank" rel="noopener" class="acad-btn-primary">Watch video</a>
                        @elseif($r->file_path)
                            <a href="{{ route('academics.topics.resources.download', [$topic, $r]) }}" class="acad-btn-primary">Download</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="card d-none d-md-block">
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($topic->resources as $r)
                        <li class="list-group-item">
                            <div class="fw-medium">{{ $r->title }}</div>
                            @if($r->description)
                                <p class="small text-muted mb-2">{{ $r->description }}</p>
                            @endif
                            @if($r->resource_type === \App\Modules\Academics\Models\TopicResource::TYPE_VIDEO_LINK && $r->video_url)
                                <a href="{{ $r->video_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary">Open video</a>
                            @elseif($r->file_path)
                                <a href="{{ route('academics.topics.resources.download', [$topic, $r]) }}" class="btn btn-sm btn-outline-primary">Download</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2 mt-3 d-none d-md-flex">
        <a href="{{ route('academics.learning-resources') }}" class="btn btn-outline-secondary btn-sm">All topics</a>
        <a href="{{ route('academics.my-assignments') }}" class="btn btn-outline-secondary btn-sm">My assignments</a>
    </div>
</div>
@endsection

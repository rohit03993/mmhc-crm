@extends('auth::layout')

@section('title', 'Topic library')
@section('page-title', $topic->name)

@section('content')
<div class="container-fluid py-3">
    <p class="text-muted small mb-4">{{ $topic->subject->name ?? '' }} · {{ $topic->subject->batch->name ?? '' }}</p>
    <div class="card">
        <div class="card-body p-0">
            @if($topic->resources->isEmpty())
                <p class="text-muted p-4 mb-0">No resources for this topic yet.</p>
            @else
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
            @endif
        </div>
    </div>
    <a href="{{ route('academics.my-assignments') }}" class="btn btn-outline-secondary btn-sm mt-3">Back to assignments</a>
</div>
@endsection

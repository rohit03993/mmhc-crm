@extends('auth::layout')

@section('title', 'Topic resources')
@section('page-title', 'Procedure & video library')

@section('content')
<div class="container-fluid py-3">
    <div class="mb-4">
        <p class="text-muted small mb-1">Topic: <strong>{{ $topic->name }}</strong> · {{ $topic->subject->name ?? '' }} ({{ $topic->subject->batch->name ?? '' }})</p>
        <a href="{{ route('academics.topics.resources.create', $topic) }}" class="btn btn-primary btn-sm">Add resource</a>
        <a href="{{ route('academics.topics.edit', $topic) }}" class="btn btn-outline-secondary btn-sm">Back to topic</a>
    </div>
    <div class="card">
        <div class="card-body p-0">
            @if($topic->resources->isEmpty())
                <p class="text-muted p-4 mb-0">No resources yet. Add a video link or upload a PDF checklist.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($topic->resources as $r)
                        <li class="list-group-item d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <span class="fw-medium">{{ $r->title }}</span>
                                <span class="badge bg-light text-dark border ms-1">{{ str_replace('_', ' ', $r->resource_type) }}</span>
                                @if($r->description)
                                    <p class="small text-muted mb-0 mt-1">{{ $r->description }}</p>
                                @endif
                                @if($r->resource_type === \App\Modules\Academics\Models\TopicResource::TYPE_VIDEO_LINK && $r->video_url)
                                    <a href="{{ $r->video_url }}" target="_blank" rel="noopener" class="small d-inline-block mt-1">{{ $r->video_url }}</a>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                @if($r->file_path)
                                    <a href="{{ route('academics.topics.resources.download', [$topic, $r]) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                @endif
                                <a href="{{ route('academics.topics.resources.edit', [$topic, $r]) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('academics.topics.resources.destroy', [$topic, $r]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this resource?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection

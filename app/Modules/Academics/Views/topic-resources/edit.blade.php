@extends('auth::layout')

@section('title', 'Edit resource')
@section('page-title', 'Edit topic resource')

@section('content')
<div class="container-fluid py-3">
    <p class="text-muted small mb-3">Topic: <strong>{{ $topic->name }}</strong></p>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('academics.topics.resources.update', [$topic, $resource]) }}" method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-12">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $resource->title) }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description', $resource->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="resource_type" class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="resource_type" id="resource_type" class="form-select @error('resource_type') is-invalid @enderror" required>
                        <option value="{{ \App\Modules\Academics\Models\TopicResource::TYPE_VIDEO_LINK }}" @selected(old('resource_type', $resource->resource_type) === \App\Modules\Academics\Models\TopicResource::TYPE_VIDEO_LINK)>Video link</option>
                        <option value="{{ \App\Modules\Academics\Models\TopicResource::TYPE_FILE }}" @selected(old('resource_type', $resource->resource_type) === \App\Modules\Academics\Models\TopicResource::TYPE_FILE)>File</option>
                        <option value="{{ \App\Modules\Academics\Models\TopicResource::TYPE_CHECKLIST }}" @selected(old('resource_type', $resource->resource_type) === \App\Modules\Academics\Models\TopicResource::TYPE_CHECKLIST)>Checklist file</option>
                    </select>
                    @error('resource_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12" id="wrap-video">
                    <label for="video_url" class="form-label">Video URL</label>
                    <input type="url" name="video_url" id="video_url" class="form-control @error('video_url') is-invalid @enderror" value="{{ old('video_url', $resource->video_url) }}">
                    @error('video_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12" id="wrap-file">
                    <label for="file" class="form-label">Replace file</label>
                    <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror">
                    @if($resource->file_path)
                        <p class="small text-muted mb-0 mt-1">Current file on disk — upload to replace.</p>
                    @endif
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="sort_order" class="form-label">Sort order</label>
                    <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', $resource->sort_order) }}" min="0">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('academics.topics.resources.index', $topic) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        (function () {
            var type = document.getElementById('resource_type');
            var wv = document.getElementById('wrap-video');
            var wf = document.getElementById('wrap-file');
            function sync() {
                var v = type.value;
                var isVideo = v === '{{ \App\Modules\Academics\Models\TopicResource::TYPE_VIDEO_LINK }}';
                wv.classList.toggle('d-none', !isVideo);
                wf.classList.toggle('d-none', isVideo);
            }
            type.addEventListener('change', sync);
            sync();
        })();
    </script>
</div>
@endsection

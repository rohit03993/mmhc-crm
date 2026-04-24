@extends('auth::layout')

@section('title', 'Community Feed')

@section('content')
<div class="container-fluid px-3 px-md-4 py-4 community-page">
    <div class="community-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h2 class="mb-2"><i class="fas fa-users me-2"></i>Community Hub</h2>
                <p class="mb-0 text-white-75">Share wins, ask for support, and stay aligned as one care team.</p>
            </div>
            <span class="badge rounded-pill text-bg-light px-3 py-2">
                <i class="fas fa-bolt me-1 text-warning"></i>Main Experience
            </span>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-6 col-lg-3">
                <div class="community-stat"><strong>{{ number_format($stats['posts']) }}</strong><span>Posts</span></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="community-stat"><strong>{{ number_format($stats['members']) }}</strong><span>Members</span></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="community-stat"><strong>{{ number_format($stats['reactions']) }}</strong><span>Reactions</span></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="community-stat"><strong>{{ number_format($stats['event_responses']) }}</strong><span>Event RSVPs</span></div>
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin() || auth()->user()->isNurse() || auth()->user()->isCaregiver())
    <div class="card mb-4 composer-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Create Post</h5>
                <small class="text-muted">Visible to all logged-in users</small>
            </div>
            <form method="POST" action="{{ route('community.posts.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="post_type" id="postType" class="form-select" required onchange="togglePostFields()">
                            <option value="text">Text</option>
                            <option value="image">Image</option>
                            @if(auth()->user()->isAdmin())
                                <option value="event">Event</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Message</label>
                        <textarea name="content" class="form-control" rows="3" placeholder="Share an update with the team...">{{ old('content') }}</textarea>
                    </div>
                    <div class="col-md-6 d-none" id="imageField">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-4 d-none" id="eventTitleField">
                        <label class="form-label">Event Title</label>
                        <input type="text" name="event_title" class="form-control" value="{{ old('event_title') }}">
                    </div>
                    <div class="col-md-4 d-none" id="eventDateField">
                        <label class="form-label">Event Date</label>
                        <input type="datetime-local" name="event_date" class="form-control" value="{{ old('event_date') }}">
                    </div>
                    <div class="col-md-4 d-none" id="eventLocationField">
                        <label class="form-label">Event Location</label>
                        <input type="text" name="event_location" class="form-control" value="{{ old('event_location') }}">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-paper-plane me-1"></i>Publish
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($posts->count() > 0)
        @foreach($posts as $post)
            @include('community::feed.partials.post-card', ['post' => $post])
        @endforeach
        <div class="mt-4 pagination-wrap">{{ $posts->links() }}</div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5>No posts yet</h5>
                <p class="text-muted mb-0">Be the first to share something in the community.</p>
            </div>
        </div>
    @endif
</div>

<style>
    .community-page .community-hero {
        background: linear-gradient(135deg, #1d4ed8 0%, #6d28d9 100%);
        border-radius: 18px;
        color: #fff;
        padding: 1.2rem 1.2rem 1rem;
        box-shadow: 0 16px 35px rgba(37, 99, 235, 0.25);
    }
    .community-page .text-white-75 { color: rgba(255,255,255,0.82); }
    .community-page .community-stat {
        background: rgba(255,255,255,0.14);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 14px;
        padding: 0.8rem 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .community-page .community-stat strong { font-size: 1.1rem; }
    .community-page .community-stat span { font-size: 0.85rem; opacity: 0.9; }
    .community-page .composer-card { border-radius: 16px; }
    .community-page .pagination-wrap svg { width: 1rem; height: 1rem; }
</style>

<script>
function togglePostFields() {
    const type = document.getElementById('postType').value;
    const imageField = document.getElementById('imageField');
    const eventTitleField = document.getElementById('eventTitleField');
    const eventDateField = document.getElementById('eventDateField');
    const eventLocationField = document.getElementById('eventLocationField');

    imageField.classList.toggle('d-none', type !== 'image');
    eventTitleField.classList.toggle('d-none', type !== 'event');
    eventDateField.classList.toggle('d-none', type !== 'event');
    eventLocationField.classList.toggle('d-none', type !== 'event');
}
document.addEventListener('DOMContentLoaded', togglePostFields);
</script>
@endsection


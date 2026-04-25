@extends('auth::layout')

@section('title', 'Community Feed')

@section('content')
<div class="container-fluid px-2 px-md-3 py-3 community-page">
    <div class="community-shell">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i>{{ $errors->first() }}
        </div>
    @endif

    <div class="community-hero mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h2 class="mb-2"><i class="fas fa-users me-2"></i>Community Hub</h2>
                <p class="mb-0 text-white-75">Share wins, ask for support, and stay aligned as one care team.</p>
            </div>
            <span class="badge rounded-pill text-bg-light px-3 py-2">
                <i class="fas fa-bolt me-1 text-warning"></i>Main Experience
            </span>
        </div>
        <div class="row g-2 mt-2">
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

    @if(isset($pinnedAnnouncements) && $pinnedAnnouncements->isNotEmpty())
    <div class="card mb-3 border-0 shadow-sm community-surface-card">
        <div class="card-body">
            <h5 class="mb-3"><i class="fas fa-bullhorn me-2 text-primary"></i>Pinned Announcements</h5>
            <div class="list-group list-group-flush">
                @foreach($pinnedAnnouncements as $announcement)
                    <a href="#post-{{ $announcement->id }}" class="list-group-item list-group-item-action px-0">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-semibold">{{ \Illuminate\Support\Str::limit($announcement->content ?: ($announcement->event_title ?: 'Announcement'), 120) }}</div>
                            <small class="text-muted">{{ $announcement->created_at->diffForHumans() }}</small>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if(auth()->user()->isAdmin() || auth()->user()->isNurse() || auth()->user()->isCaregiver())
    <div class="card mb-3 composer-card community-surface-card" id="composerCard">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Create Post</h5>
                <small class="text-muted">Visible to all logged-in users</small>
            </div>
            <form method="POST" action="{{ route('community.posts.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-3 col-lg-2">
                        <label class="form-label">Type</label>
                        <select name="post_type" id="postType" class="form-select" required onchange="togglePostFields()">
                            <option value="text">Text</option>
                            <option value="image">Image</option>
                            @if(auth()->user()->isAdmin())
                                <option value="event">Event</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-9 col-lg-10">
                        <label class="form-label">Message</label>
                        <textarea name="content" class="form-control" rows="2" placeholder="Share an update with the team...">{{ old('content') }}</textarea>
                    </div>
                    @if(auth()->user()->isAdmin())
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" name="is_pinned" id="isPinned">
                            <label class="form-check-label" for="isPinned">Pin to top</label>
                        </div>
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" value="1" name="is_announcement" id="isAnnouncement">
                            <label class="form-check-label" for="isAnnouncement">Mark as announcement</label>
                        </div>
                    </div>
                    @endif
                    <div class="col-md-6 col-lg-4 d-none" id="imageField">
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
                <div class="mt-2">
                    <button type="submit" class="btn btn-primary px-4 submit-post-btn">
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
        <div class="mt-3 pagination-wrap">{{ $posts->links() }}</div>
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
</div>

@if(auth()->user()->isAdmin() || auth()->user()->isNurse() || auth()->user()->isCaregiver())
    <button type="button" class="btn btn-primary shadow floating-compose-btn d-md-none" id="floatingComposeBtn">
        <i class="fas fa-plus me-1"></i>New Post
    </button>
@endif

<style>
    .community-page {
        --community-border: rgba(148, 163, 184, 0.22);
        --community-surface: #ffffff;
        --community-bg: #f8fafc;
        --community-text-soft: #64748b;
    }
    .community-page .community-shell {
        max-width: 980px;
        margin: 0 auto;
    }
    .community-page .community-surface-card {
        background: var(--community-surface);
        border: 1px solid var(--community-border) !important;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05) !important;
    }
    .community-page .community-hero {
        background:
            radial-gradient(1200px 200px at top right, rgba(255, 255, 255, 0.18), transparent 45%),
            linear-gradient(135deg, #1d4ed8 0%, #6d28d9 100%);
        border-radius: 14px;
        color: #fff;
        padding: 0.9rem 1rem 0.8rem;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.2);
    }
    .community-page .community-hero h2 { font-size: 1.35rem; margin-bottom: 0.25rem !important; }
    .community-page .community-hero p { font-size: 0.9rem; }
    .community-page .text-white-75 { color: rgba(255,255,255,0.82); }
    .community-page .community-stat {
        background: rgba(255,255,255,0.14);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 10px;
        padding: 0.5rem 0.7rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .community-page .community-stat strong { font-size: 0.95rem; }
    .community-page .community-stat span { font-size: 0.75rem; opacity: 0.9; }
    .community-page .composer-card {
        border-radius: 12px;
        border: 1px solid var(--community-border);
    }
    .community-page .composer-card .form-control,
    .community-page .composer-card .form-select {
        border-radius: 10px;
        border-color: rgba(148, 163, 184, 0.35);
        min-height: 38px;
    }
    .community-page .composer-card .form-control:focus,
    .community-page .composer-card .form-select:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 0.16rem rgba(59, 130, 246, 0.16);
    }
    .community-page .post-card {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--community-border);
        transition: transform 180ms ease, box-shadow 180ms ease;
    }
    .community-page .post-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.09) !important;
    }
    .community-page .card-title,
    .community-page h5,
    .community-page h6 {
        letter-spacing: -0.01em;
    }
    .community-page .post-content {
        white-space: pre-wrap;
        line-height: 1.55;
    }
    .community-page .author-avatar {
        width: 30px;
        height: 30px;
        border-radius: 999px;
        background: linear-gradient(135deg, #dbeafe 0%, #c4b5fd 100%);
        color: #1e293b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.78rem;
        flex-shrink: 0;
    }
    .community-page .post-image { max-height: 280px; width: 100%; object-fit: cover; }
    .community-page .reaction-scroll { overflow-x: auto; padding-bottom: 0.25rem; }
    .community-page .reaction-scroll::-webkit-scrollbar { height: 6px; }
    .community-page .reaction-scroll::-webkit-scrollbar-thumb {
        background: rgba(100, 116, 139, 0.45);
        border-radius: 999px;
    }
    .community-page .action-btn {
        min-width: 30px;
        min-height: 30px;
        border-radius: 8px;
        font-size: 0.8rem;
    }
    .community-page .btn-outline-primary,
    .community-page .btn-outline-warning,
    .community-page .btn-outline-danger,
    .community-page .btn-outline-secondary {
        border-width: 1px;
    }
    .community-page .btn-primary {
        background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
        border: none;
    }
    .community-page .btn-primary:hover {
        filter: brightness(0.97);
    }
    .community-page .comment-toggle-btn {
        border-radius: 999px;
        padding-inline: 0.8rem;
        color: #334155;
        background: #f8fafc;
    }
    .community-page .submit-post-btn {
        min-height: 34px;
        border-radius: 8px;
        font-size: 0.875rem;
    }
    .community-page .floating-compose-btn {
        position: fixed;
        right: 16px;
        bottom: 18px;
        z-index: 1030;
        border-radius: 999px;
        padding: 0.62rem 1rem;
    }
    .community-page .pagination-wrap svg { width: 1rem; height: 1rem; }
    .community-page details > summary {
        list-style: none;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .community-page details > summary::-webkit-details-marker { display: none; }
    .community-page details > summary::before {
        content: "+";
        display: inline-flex;
        width: 18px;
        height: 18px;
        border-radius: 999px;
        align-items: center;
        justify-content: center;
        background: #e2e8f0;
        color: #334155;
        font-weight: 600;
    }
    .community-page details[open] > summary::before { content: "-"; }
    .community-page .list-group-item {
        border-color: rgba(148, 163, 184, 0.2);
    }
    .community-page .list-group-item:hover {
        background: #f8fafc;
    }
    @media (max-width: 768px) {
        .community-page .community-hero {
            border-radius: 12px;
            padding: 0.85rem;
        }
        .community-page .post-card .card-body {
            padding: 0.75rem;
        }
        .community-page .composer-card .card-body {
            padding: 0.75rem;
        }
        .community-page .community-stat {
            padding: 0.5rem 0.65rem;
        }
        .community-page .post-meta-actions {
            justify-content: flex-start;
        }
        .community-page .reaction-scroll .btn {
            white-space: nowrap;
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
        }
        .community-page .post-image { max-height: 210px; }
    }
    @media (min-width: 769px) {
        .community-page .floating-compose-btn {
            display: none;
        }
    }
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

document.addEventListener('DOMContentLoaded', () => {
    const composeBtn = document.getElementById('floatingComposeBtn');
    const composerCard = document.getElementById('composerCard');
    if (composeBtn && composerCard) {
        composeBtn.addEventListener('click', () => {
            composerCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            const textarea = composerCard.querySelector('textarea[name="content"]');
            if (textarea) {
                textarea.focus({ preventScroll: true });
            }
        });
    }
});
</script>
@endsection


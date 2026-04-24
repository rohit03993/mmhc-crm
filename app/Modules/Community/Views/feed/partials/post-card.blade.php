<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h6 class="mb-1">{{ $post->user->name }}</h6>
                <small class="text-muted">{{ ucfirst($post->user->role) }} • {{ $post->created_at->diffForHumans() }}</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill text-bg-secondary px-3">{{ strtoupper($post->post_type) }}</span>
                @if(auth()->user()->isAdmin() || auth()->id() === $post->user_id)
                <form method="POST" action="{{ route('community.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                </form>
                @endif
            </div>
        </div>

        @if($post->content)
            <p class="mb-3 fs-6">{{ $post->content }}</p>
        @endif

        @if($post->image_path)
            <img src="{{ storage_asset($post->image_path) }}" alt="Post image" class="img-fluid rounded-3 mb-3" style="max-height: 460px; width: 100%; object-fit: cover;">
        @endif

        @if($post->post_type === 'event')
            <div class="p-3 rounded-3 border mb-3 bg-light">
                <div class="fw-semibold">{{ $post->event_title }}</div>
                <div><i class="fas fa-calendar-alt me-1"></i>{{ optional($post->event_date)->format('M d, Y h:i A') }}</div>
                <div><i class="fas fa-map-marker-alt me-1"></i>{{ $post->event_location }}</div>
            </div>
        @endif

        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <form method="POST" action="{{ route('community.reactions.toggle', $post) }}">
                @csrf
                <input type="hidden" name="page" value="{{ request('page', 1) }}">
                <button class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fas fa-thumbs-up me-1"></i>
                    {{ $post->reactions->contains('user_id', auth()->id()) ? 'Unlike' : 'Like' }} ({{ $post->reactions_count }})
                </button>
            </form>

            <span class="badge rounded-pill text-bg-light"><i class="fas fa-comment me-1"></i>{{ $post->comments_count }} comments</span>

            @if($post->post_type === 'event')
                @php
                    $myInterest = $post->eventInterests->firstWhere('user_id', auth()->id());
                @endphp
                <form method="POST" action="{{ route('community.events.interest', $post) }}" class="d-inline-flex">
                    @csrf
                    <input type="hidden" name="page" value="{{ request('page', 1) }}">
                    <input type="hidden" name="status" value="interested">
                    <button class="btn btn-sm rounded-pill px-3 {{ optional($myInterest)->status === 'interested' ? 'btn-warning' : 'btn-outline-warning' }}">Interested</button>
                </form>
                <form method="POST" action="{{ route('community.events.interest', $post) }}" class="d-inline-flex">
                    @csrf
                    <input type="hidden" name="page" value="{{ request('page', 1) }}">
                    <input type="hidden" name="status" value="going">
                    <button class="btn btn-sm rounded-pill px-3 {{ optional($myInterest)->status === 'going' ? 'btn-success' : 'btn-outline-success' }}">Going</button>
                </form>
                @if($myInterest)
                    <form method="POST" action="{{ route('community.events.interest', $post) }}" class="d-inline-flex">
                        @csrf
                        <input type="hidden" name="page" value="{{ request('page', 1) }}">
                        <input type="hidden" name="status" value="none">
                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3">Clear</button>
                    </form>
                @endif
            @endif
        </div>

        <div class="border-top pt-3">
            <h6 class="mb-2">Comments</h6>
            @forelse($post->comments as $comment)
                <div class="mb-2 p-2 bg-light rounded-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <small><strong>{{ $comment->user->name }}</strong> • {{ $comment->created_at->diffForHumans() }}</small>
                        @if(auth()->user()->isAdmin() || auth()->id() === $comment->user_id)
                            <form method="POST" action="{{ route('community.comments.destroy', $comment) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-link btn-sm text-danger p-0">Delete</button>
                            </form>
                        @endif
                    </div>
                    <div>{{ $comment->content }}</div>
                </div>
            @empty
                <small class="text-muted d-block mb-2">No comments yet.</small>
            @endforelse

            <form method="POST" action="{{ route('community.comments.store', $post) }}">
                @csrf
                <input type="hidden" name="page" value="{{ request('page', 1) }}">
                <div class="input-group">
                    <input type="text" name="content" class="form-control" placeholder="Write a comment..." required maxlength="1000">
                    <button class="btn btn-primary">Comment</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="card mb-3 border-0 shadow-sm post-card" id="post-{{ $post->id }}">
    <div class="card-body p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
            <div class="d-flex align-items-start gap-2">
                <div class="author-avatar">{{ strtoupper(substr($post->user->name, 0, 1)) }}</div>
                <div>
                    <h6 class="mb-1 fw-semibold">{{ $post->user->name }}</h6>
                    <small class="text-muted">{{ ucfirst($post->user->role) }} • {{ $post->created_at->diffForHumans() }}</small>
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-1 post-meta-actions">
                @if($post->is_pinned)
                    <span class="badge rounded-pill text-bg-warning px-2 py-1 fw-medium"><i class="fas fa-thumbtack me-1"></i>Pinned</span>
                @endif
                @if($post->is_announcement)
                    <span class="badge rounded-pill text-bg-info px-2 py-1 fw-medium"><i class="fas fa-bullhorn me-1"></i>Announcement</span>
                @endif
                <span class="badge rounded-pill text-bg-secondary px-2 py-1 fw-medium">{{ strtoupper($post->post_type) }}</span>
                @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('community.posts.pin', $post) }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-warning action-btn" title="{{ $post->is_pinned ? 'Unpin' : 'Pin' }}">
                            <i class="fas fa-thumbtack"></i>
                        </button>
                    </form>
                @endif
                @if(auth()->user()->isAdmin() || auth()->id() === $post->user_id)
                    <button class="btn btn-sm btn-outline-primary action-btn" data-bs-toggle="collapse" data-bs-target="#postEdit{{ $post->id }}">
                        <i class="fas fa-pen me-1"></i>Edit
                    </button>
                    <form method="POST" action="{{ route('community.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger action-btn"><i class="fas fa-trash"></i></button>
                    </form>
                @endif
            </div>
        </div>

        <div class="collapse mb-2" id="postEdit{{ $post->id }}">
            <div class="card card-body border bg-light-subtle">
                <form method="POST" action="{{ route('community.posts.update', $post) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="page" value="{{ request('page', 1) }}">
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Message</label>
                        <textarea name="content" class="form-control" rows="3" maxlength="3000">{{ $post->content }}</textarea>
                    </div>
                    @if($post->post_type === 'event')
                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Event title</label>
                                <input type="text" name="event_title" class="form-control" value="{{ $post->event_title }}" maxlength="255" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Event date</label>
                                <input type="datetime-local" name="event_date" class="form-control" value="{{ optional($post->event_date)->format('Y-m-d\TH:i') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Event location</label>
                                <input type="text" name="event_location" class="form-control" value="{{ $post->event_location }}" maxlength="255" required>
                            </div>
                        </div>
                    @endif
                    @if($post->post_type === 'image')
                        <div class="row g-2 mb-2">
                            <div class="col-md-8">
                                <label class="form-label small text-muted mb-1">Replace image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            @if($post->image_path)
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="removeImage{{ $post->id }}">
                                        <label class="form-check-label" for="removeImage{{ $post->id }}">Remove old image</label>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                    @if(auth()->user()->isAdmin())
                        <div class="d-flex flex-wrap gap-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_pinned" value="1" id="editPinned{{ $post->id }}" @checked($post->is_pinned)>
                                <label class="form-check-label" for="editPinned{{ $post->id }}">Pinned</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_announcement" value="1" id="editAnn{{ $post->id }}" @checked($post->is_announcement)>
                                <label class="form-check-label" for="editAnn{{ $post->id }}">Announcement</label>
                            </div>
                        </div>
                    @endif
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm px-3">Save changes</button>
                        <button type="button" class="btn btn-light btn-sm px-3" data-bs-toggle="collapse" data-bs-target="#postEdit{{ $post->id }}">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        @if($post->content)
            <p class="mb-2 fs-6 post-content">{{ $post->content }}</p>
        @endif

        @if($post->image_path)
            @php
                $communityImageUrl = storage_url($post->image_path) ?? storage_asset($post->image_path);
                $communityImageFallback = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="600"><rect width="100%" height="100%" fill="#f1f5f9"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#64748b" font-family="Arial" font-size="28">Image unavailable</text></svg>');
            @endphp
            <img src="{{ $communityImageUrl }}" alt="Post image" class="img-fluid rounded-3 mb-2 post-image" onerror="this.onerror=null;this.src='{{ $communityImageFallback }}';">
        @endif

        @if($post->post_type === 'event')
            <div class="p-2 rounded-3 border mb-2 bg-light-subtle">
                <div class="fw-semibold">{{ $post->event_title }}</div>
                <div><i class="fas fa-calendar-alt me-1"></i>{{ optional($post->event_date)->format('M d, Y h:i A') }}</div>
                <div><i class="fas fa-map-marker-alt me-1"></i>{{ $post->event_location }}</div>
            </div>
        @endif

        <div class="reaction-scroll mb-2">
            <div class="d-flex align-items-center gap-2 flex-nowrap">
                @php
                    $myReaction = optional($post->reactions->firstWhere('user_id', auth()->id()))->reaction_type;
                    $reactionCounts = $post->reactions->groupBy('reaction_type')->map->count();
                    $reactionOptions = [
                        'like' => ['label' => 'Like', 'icon' => 'fa-thumbs-up'],
                        'care' => ['label' => 'Care', 'icon' => 'fa-hand-holding-heart'],
                        'support' => ['label' => 'Support', 'icon' => 'fa-hands-helping'],
                        'celebrate' => ['label' => 'Celebrate', 'icon' => 'fa-star'],
                    ];
                @endphp
                @foreach($reactionOptions as $type => $option)
                    <form method="POST" action="{{ route('community.reactions.react', $post) }}">
                        @csrf
                        <input type="hidden" name="page" value="{{ request('page', 1) }}">
                        <input type="hidden" name="reaction_type" value="{{ $type }}">
                        <button class="btn btn-sm rounded-pill px-3 {{ $myReaction === $type ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fas {{ $option['icon'] }} me-1"></i>{{ $option['label'] }} ({{ $reactionCounts[$type] ?? 0 }})
                        </button>
                    </form>
                @endforeach
                <span class="badge rounded-pill text-bg-light px-2 py-1"><i class="fas fa-comment me-1"></i>{{ $post->comments_count }} comments</span>
                @if($post->post_type === 'event')
                    @php $myInterest = $post->eventInterests->firstWhere('user_id', auth()->id()); @endphp
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
        </div>

        <div class="border-top pt-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Comments</h6>
                <button class="btn btn-sm btn-light border comment-toggle-btn" data-bs-toggle="collapse" data-bs-target="#commentsBlock{{ $post->id }}">
                    <i class="fas fa-comments me-1"></i>{{ $post->comments_count > 0 ? 'Show thread' : 'Add comment' }}
                </button>
            </div>
            <div class="collapse" id="commentsBlock{{ $post->id }}">
                @forelse($post->comments->whereNull('parent_id') as $comment)
                    <div class="mb-2 p-2 bg-light-subtle rounded-3 border">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <small><strong>{{ $comment->user->name }}</strong> • {{ $comment->created_at->diffForHumans() }}</small>
                            <div class="d-flex align-items-center gap-2">
                                @if(auth()->user()->isAdmin() || auth()->id() === $comment->user_id)
                                    <button class="btn btn-link btn-sm p-0 text-primary" data-bs-toggle="collapse" data-bs-target="#commentEdit{{ $comment->id }}">Edit</button>
                                    <form method="POST" action="{{ route('community.comments.destroy', $comment) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-link btn-sm text-danger p-0">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <div>{{ $comment->content }}</div>
                        <div class="collapse mt-2" id="commentEdit{{ $comment->id }}">
                            <form method="POST" action="{{ route('community.comments.update', $comment) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="page" value="{{ request('page', 1) }}">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="content" class="form-control" value="{{ $comment->content }}" required maxlength="1000">
                                    <button class="btn btn-primary">Save</button>
                                </div>
                            </form>
                        </div>

                        <div class="mt-2">
                            <details>
                                <summary class="small text-primary" style="cursor: pointer;">Reply</summary>
                                <form method="POST" action="{{ route('community.comments.store', $post) }}" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="page" value="{{ request('page', 1) }}">
                                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="content" class="form-control" placeholder="Write a reply..." required maxlength="1000">
                                        <button class="btn btn-outline-primary">Post reply</button>
                                    </div>
                                </form>
                            </details>
                        </div>

                        @foreach($comment->replies as $reply)
                            <div class="mt-2 ms-3 p-2 bg-white border rounded-3 shadow-sm">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <small><strong>{{ $reply->user->name }}</strong> • {{ $reply->created_at->diffForHumans() }}</small>
                                    <div class="d-flex align-items-center gap-2">
                                        @if(auth()->user()->isAdmin() || auth()->id() === $reply->user_id)
                                            <button class="btn btn-link btn-sm p-0 text-primary" data-bs-toggle="collapse" data-bs-target="#replyEdit{{ $reply->id }}">Edit</button>
                                            <form method="POST" action="{{ route('community.comments.destroy', $reply) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-link btn-sm text-danger p-0">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div>{{ $reply->content }}</div>
                                <div class="collapse mt-2" id="replyEdit{{ $reply->id }}">
                                    <form method="POST" action="{{ route('community.comments.update', $reply) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="page" value="{{ request('page', 1) }}">
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="content" class="form-control" value="{{ $reply->content }}" required maxlength="1000">
                                            <button class="btn btn-primary">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach
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
</div>


<?php

namespace App\Modules\Community\Services;

use App\Models\Core\User;
use App\Modules\Community\Models\CommunityPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

class PostService
{
    public function getFeed(int $perPage = 10): LengthAwarePaginator
    {
        return CommunityPost::query()
            ->with([
                'user:id,name,role,unique_id',
                'comments.user:id,name,role,unique_id',
                'comments.replies.user:id,name,role,unique_id',
                'reactions:id,post_id,user_id,reaction_type',
                'eventInterests:id,post_id,user_id,status',
            ])
            ->withCount(['comments', 'reactions', 'eventInterests'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('pinned_at')
            ->latest()
            ->paginate($perPage);
    }

    public function createPost(User $user, array $data): CommunityPost
    {
        $payload = [
            'user_id' => $user->id,
            'post_type' => $data['post_type'],
            'is_pinned' => !empty($data['is_pinned']),
            'is_announcement' => !empty($data['is_announcement']),
            'pinned_at' => !empty($data['is_pinned']) ? now() : null,
            'content' => $data['content'] ?? null,
            'event_title' => $data['event_title'] ?? null,
            'event_date' => $data['event_date'] ?? null,
            'event_location' => $data['event_location'] ?? null,
        ];

        if (!empty($data['image']) && $data['image'] instanceof UploadedFile) {
            $payload['image_path'] = $data['image']->store('community/posts', 'public');
        }

        return CommunityPost::create($payload);
    }
}


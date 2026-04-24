<?php

namespace App\Modules\Community\Services;

use App\Models\Core\User;
use App\Modules\Community\Models\CommunityNotification;
use App\Modules\Community\Models\CommunityPost;

class CommunityNotificationService
{
    public function notifyReaction(User $actor, CommunityPost $post, string $reactionType): void
    {
        if ($post->user_id === $actor->id) {
            return;
        }

        CommunityNotification::create([
            'recipient_user_id' => $post->user_id,
            'actor_user_id' => $actor->id,
            'post_id' => $post->id,
            'type' => 'reaction',
            'meta' => ['reaction_type' => $reactionType],
        ]);
    }

    public function notifyComment(User $actor, CommunityPost $post, string $commentPreview): void
    {
        if ($post->user_id === $actor->id) {
            return;
        }

        CommunityNotification::create([
            'recipient_user_id' => $post->user_id,
            'actor_user_id' => $actor->id,
            'post_id' => $post->id,
            'type' => 'comment',
            'meta' => ['comment_preview' => $commentPreview],
        ]);
    }
}


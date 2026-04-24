<?php

namespace App\Modules\Community\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Community\Models\CommunityComment;
use App\Modules\Community\Models\CommunityPost;
use App\Modules\Community\Services\CommunityNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct(
        private CommunityNotificationService $notificationService
    ) {}

    public function store(Request $request, CommunityPost $post)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:community_comments,id',
        ]);

        $comment = CommunityComment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        $this->notificationService->notifyComment(
            Auth::user(),
            $post,
            str($comment->content)->limit(100)->toString()
        );

        return redirect()->route('community.index', ['page' => $request->get('page')])
            ->with('success', 'Comment added.');
    }

    public function destroy(CommunityComment $comment)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $comment->user_id !== $user->id) {
            abort(403, 'You are not allowed to delete this comment.');
        }

        $comment->delete();
        return redirect()->route('community.index')->with('success', 'Comment deleted.');
    }
}


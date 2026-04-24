<?php

namespace App\Modules\Community\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Community\Models\CommunityPost;
use App\Modules\Community\Models\CommunityReaction;
use App\Modules\Community\Services\CommunityNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReactionController extends Controller
{
    public function __construct(
        private CommunityNotificationService $notificationService
    ) {}

    public function react(Request $request, CommunityPost $post)
    {
        $request->validate([
            'reaction_type' => 'required|in:like,care,support,celebrate',
        ]);

        $userId = Auth::id();
        $reactionType = $request->string('reaction_type')->toString();

        $existing = CommunityReaction::where('post_id', $post->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            if ($existing->reaction_type === $reactionType) {
                $existing->delete();
                return redirect()->route('community.index', ['page' => $request->get('page')])->with('success', 'Reaction removed.');
            }

            $existing->update(['reaction_type' => $reactionType]);
            $this->notificationService->notifyReaction(Auth::user(), $post, $reactionType);
            return redirect()->route('community.index', ['page' => $request->get('page')])->with('success', 'Reaction updated.');
        }

        CommunityReaction::create([
            'post_id' => $post->id,
            'user_id' => $userId,
            'reaction_type' => $reactionType,
        ]);

        $this->notificationService->notifyReaction(Auth::user(), $post, $reactionType);

        return redirect()->route('community.index', ['page' => $request->get('page')])->with('success', 'Reaction added.');
    }
}


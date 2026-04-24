<?php

namespace App\Modules\Community\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Community\Models\CommunityPost;
use App\Modules\Community\Models\CommunityReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReactionController extends Controller
{
    public function toggle(Request $request, CommunityPost $post)
    {
        $userId = Auth::id();

        $existing = CommunityReaction::where('post_id', $post->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
            return redirect()->route('community.index', ['page' => $request->get('page')])->with('success', 'Like removed.');
        }

        CommunityReaction::create([
            'post_id' => $post->id,
            'user_id' => $userId,
            'reaction_type' => 'like',
        ]);

        return redirect()->route('community.index', ['page' => $request->get('page')])->with('success', 'Post liked.');
    }
}


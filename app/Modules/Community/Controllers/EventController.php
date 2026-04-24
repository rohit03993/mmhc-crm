<?php

namespace App\Modules\Community\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Community\Models\CommunityEventInterest;
use App\Modules\Community\Models\CommunityPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function setInterest(Request $request, CommunityPost $post)
    {
        if ($post->post_type !== 'event') {
            return redirect()->route('community.index')->with('error', 'Interest is allowed for event posts only.');
        }

        $request->validate([
            'status' => 'required|in:interested,going,none',
        ]);

        $userId = Auth::id();
        $status = $request->string('status')->toString();

        if ($status === 'none') {
            CommunityEventInterest::where('post_id', $post->id)
                ->where('user_id', $userId)
                ->delete();

            return redirect()->route('community.index', ['page' => $request->get('page')])->with('success', 'Event response cleared.');
        }

        CommunityEventInterest::updateOrCreate(
            ['post_id' => $post->id, 'user_id' => $userId],
            ['status' => $status]
        );

        return redirect()->route('community.index', ['page' => $request->get('page')])->with('success', 'Event response updated.');
    }
}


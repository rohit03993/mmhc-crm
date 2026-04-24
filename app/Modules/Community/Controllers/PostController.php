<?php

namespace App\Modules\Community\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Community\Models\CommunityEventInterest;
use App\Modules\Community\Models\CommunityPost;
use App\Modules\Community\Models\CommunityReaction;
use App\Modules\Community\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    public function index()
    {
        $posts = $this->postService->getFeed(10);
        $stats = [
            'posts' => CommunityPost::count(),
            'members' => User::where('is_active', true)->count(),
            'reactions' => CommunityReaction::count(),
            'event_responses' => CommunityEventInterest::count(),
        ];

        return view('community::feed.index', compact('posts', 'stats'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'post_type' => 'required|in:text,image,event',
            'content' => 'nullable|string|max:3000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'event_title' => 'nullable|string|max:255',
            'event_date' => 'nullable|date|after_or_equal:today',
            'event_location' => 'nullable|string|max:255',
        ]);

        if (!in_array($user->role, ['admin', 'nurse', 'caregiver'])) {
            abort(403, 'You are not allowed to create posts.');
        }

        $postType = $request->string('post_type')->toString();
        if (in_array($postType, ['text', 'image']) && !in_array($user->role, ['admin', 'nurse', 'caregiver'])) {
            abort(403, 'You are not allowed to create this post type.');
        }
        if ($postType === 'event' && $user->role !== 'admin') {
            abort(403, 'Only admin can create event posts.');
        }

        if ($postType === 'text' && blank($request->content)) {
            return back()->withErrors(['content' => 'Text post content is required.'])->withInput();
        }
        if ($postType === 'image' && !$request->hasFile('image')) {
            return back()->withErrors(['image' => 'Image is required for image post.'])->withInput();
        }
        if ($postType === 'event') {
            $request->validate([
                'event_title' => 'required|string|max:255',
                'event_date' => 'required|date|after_or_equal:today',
                'event_location' => 'required|string|max:255',
                'content' => 'required|string|max:3000',
            ]);
        }

        $this->postService->createPost($user, $request->all());

        return redirect()->route('community.index')->with('success', 'Post created successfully.');
    }

    public function destroy(CommunityPost $post)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $post->user_id !== $user->id) {
            abort(403, 'You are not allowed to delete this post.');
        }

        $post->delete();
        return redirect()->route('community.index')->with('success', 'Post deleted successfully.');
    }
}


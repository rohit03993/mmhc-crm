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
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    public function index()
    {
        $posts = $this->postService->getFeed(10);
        $pinnedAnnouncements = CommunityPost::query()
            ->where('is_pinned', true)
            ->where('is_announcement', true)
            ->latest('pinned_at')
            ->latest('created_at')
            ->limit(5)
            ->get();

        $stats = [
            'posts' => CommunityPost::count(),
            'members' => User::where('is_active', true)->count(),
            'reactions' => CommunityReaction::count(),
            'event_responses' => CommunityEventInterest::count(),
        ];

        return view('community::feed.index', compact('posts', 'stats', 'pinnedAnnouncements'));
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
            'is_pinned' => 'nullable|boolean',
            'is_announcement' => 'nullable|boolean',
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

        $payload = $request->all();
        if (!$user->isAdmin()) {
            $payload['is_pinned'] = false;
            $payload['is_announcement'] = false;
        }

        $this->postService->createPost($user, $payload);

        return redirect()->route('community.index')->with('success', 'Post created successfully.');
    }

    public function togglePin(CommunityPost $post)
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403, 'Only admin can pin posts.');
        }

        $post->update([
            'is_pinned' => !$post->is_pinned,
            'pinned_at' => !$post->is_pinned ? now() : null,
        ]);

        return redirect()->route('community.index')
            ->with('success', $post->is_pinned ? 'Post pinned to top.' : 'Post unpinned.');
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

    public function update(Request $request, CommunityPost $post)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $post->user_id !== $user->id) {
            abort(403, 'You are not allowed to edit this post.');
        }

        $request->validate([
            'content' => 'nullable|string|max:3000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'remove_image' => 'nullable|boolean',
            'event_title' => 'nullable|string|max:255',
            'event_date' => 'nullable|date|after_or_equal:today',
            'event_location' => 'nullable|string|max:255',
            'is_pinned' => 'nullable|boolean',
            'is_announcement' => 'nullable|boolean',
        ]);

        if ($post->post_type === 'text' && blank($request->content)) {
            return redirect()->route('community.index', ['page' => $request->get('page')])
                ->withErrors(['content' => 'Text post content is required.']);
        }

        if ($post->post_type === 'event') {
            $request->validate([
                'event_title' => 'required|string|max:255',
                'event_date' => 'required|date|after_or_equal:today',
                'event_location' => 'required|string|max:255',
                'content' => 'required|string|max:3000',
            ]);
        }

        $removeImage = $request->boolean('remove_image');
        if ($post->post_type === 'image' && $removeImage && !$request->hasFile('image')) {
            return redirect()->route('community.index', ['page' => $request->get('page')])
                ->withErrors(['image' => 'Image post must have an image. Upload a new one before removing the current image.']);
        }

        if ($removeImage && !empty($post->image_path)) {
            Storage::disk('public')->delete($post->image_path);
            $post->image_path = null;
        }

        if ($request->hasFile('image')) {
            if (!empty($post->image_path)) {
                Storage::disk('public')->delete($post->image_path);
            }
            $post->image_path = $request->file('image')->store('community/posts', 'public');
        }

        $post->content = $request->input('content');
        $post->event_title = $post->post_type === 'event' ? $request->input('event_title') : null;
        $post->event_date = $post->post_type === 'event' ? $request->input('event_date') : null;
        $post->event_location = $post->post_type === 'event' ? $request->input('event_location') : null;

        if ($user->isAdmin()) {
            $wasPinned = (bool) $post->is_pinned;
            $post->is_pinned = $request->boolean('is_pinned');
            $post->is_announcement = $request->boolean('is_announcement');
            if ($post->is_pinned && !$wasPinned) {
                $post->pinned_at = now();
            }
            if (!$post->is_pinned) {
                $post->pinned_at = null;
            }
        }

        $post->save();

        return redirect()->route('community.index', ['page' => $request->get('page')])
            ->with('success', 'Post updated successfully.');
    }
}


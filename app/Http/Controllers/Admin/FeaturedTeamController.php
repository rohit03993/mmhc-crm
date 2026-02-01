<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FeaturedTeamController extends Controller
{
    public function index()
    {
        $items = FeaturedTeam::ordered()->get();
        return view('admin.featured-team.index', compact('items'));
    }

    public function create()
    {
        return view('admin.featured-team.edit', ['item' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'title' => 'nullable|string|max:255',
            'rating' => 'nullable|numeric|min:0|max:5',
            'reviews_count' => 'nullable|integer|min:0',
            'bio' => 'nullable|string|max:1000',
            'skills' => 'nullable|string|max:500',
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('featured-team', 'public');
        }

        $maxOrder = FeaturedTeam::max('sort_order') ?? 0;
        FeaturedTeam::create([
            'name' => $validated['name'],
            'image_path' => $path,
            'title' => $validated['title'] ?? null,
            'rating' => $validated['rating'] ?? null,
            'reviews_count' => $validated['reviews_count'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'skills' => $validated['skills'] ?? null,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.featured-team.index')
            ->with('success', 'Team member added.');
    }

    public function edit(FeaturedTeam $featuredTeam)
    {
        return view('admin.featured-team.edit', ['item' => $featuredTeam]);
    }

    public function update(Request $request, FeaturedTeam $featuredTeam)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'title' => 'nullable|string|max:255',
            'rating' => 'nullable|numeric|min:0|max:5',
            'reviews_count' => 'nullable|integer|min:0',
            'bio' => 'nullable|string|max:1000',
            'skills' => 'nullable|string|max:500',
        ]);

        $featuredTeam->name = $validated['name'];
        $featuredTeam->title = $validated['title'] ?? null;
        $featuredTeam->rating = $validated['rating'] ?? null;
        $featuredTeam->reviews_count = $validated['reviews_count'] ?? null;
        $featuredTeam->bio = $validated['bio'] ?? null;
        $featuredTeam->skills = $validated['skills'] ?? null;

        if ($request->hasFile('image')) {
            if ($featuredTeam->image_path && Storage::disk('public')->exists($featuredTeam->image_path)) {
                Storage::disk('public')->delete($featuredTeam->image_path);
            }
            $featuredTeam->image_path = $request->file('image')->store('featured-team', 'public');
        }

        $featuredTeam->save();

        return redirect()->route('admin.featured-team.index')
            ->with('success', 'Team member updated.');
    }

    public function destroy(FeaturedTeam $featuredTeam)
    {
        if ($featuredTeam->image_path && Storage::disk('public')->exists($featuredTeam->image_path)) {
            Storage::disk('public')->delete($featuredTeam->image_path);
        }
        $featuredTeam->delete();
        return redirect()->route('admin.featured-team.index')
            ->with('success', 'Team member removed.');
    }

    public function moveUp(FeaturedTeam $featuredTeam)
    {
        $prev = FeaturedTeam::where('sort_order', '<', $featuredTeam->sort_order)->ordered()->latest('sort_order')->first();
        if ($prev) {
            [$featuredTeam->sort_order, $prev->sort_order] = [$prev->sort_order, $featuredTeam->sort_order];
            $featuredTeam->save();
            $prev->save();
        }
        return redirect()->back()->with('success', 'Order updated.');
    }

    public function moveDown(FeaturedTeam $featuredTeam)
    {
        $next = FeaturedTeam::where('sort_order', '>', $featuredTeam->sort_order)->ordered()->first();
        if ($next) {
            [$featuredTeam->sort_order, $next->sort_order] = [$next->sort_order, $featuredTeam->sort_order];
            $featuredTeam->save();
            $next->save();
        }
        return redirect()->back()->with('success', 'Order updated.');
    }
}

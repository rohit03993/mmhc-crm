<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AchievementMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AchievementMediaController extends Controller
{
    public function index()
    {
        $items = AchievementMedia::ordered()->get();
        return view('admin.achievement-media.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|max:5120',
            'caption' => 'nullable|string|max:255',
        ]);

        $path = $request->file('image')->store('achievement-media', 'public');
        $maxOrder = AchievementMedia::max('sort_order') ?? 0;

        AchievementMedia::create([
            'image_path' => $path,
            'caption' => $validated['caption'] ?? null,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.achievement-media.index')
            ->with('success', 'Image added successfully.');
    }

    public function edit(AchievementMedia $achievementMedia)
    {
        return view('admin.achievement-media.edit', compact('achievementMedia'));
    }

    public function update(Request $request, AchievementMedia $achievementMedia)
    {
        $validated = $request->validate([
            'image' => 'nullable|image|max:5120',
            'caption' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            if ($achievementMedia->image_path && Storage::disk('public')->exists($achievementMedia->image_path)) {
                Storage::disk('public')->delete($achievementMedia->image_path);
            }
            $achievementMedia->image_path = $request->file('image')->store('achievement-media', 'public');
        }
        $achievementMedia->caption = $validated['caption'] ?? null;
        $achievementMedia->save();

        return redirect()->route('admin.achievement-media.index')
            ->with('success', 'Carousel item updated.');
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:achievement_media,id',
        ]);

        foreach ($validated['order'] as $position => $id) {
            AchievementMedia::where('id', $id)->update(['sort_order' => $position]);
        }

        return redirect()->back()->with('success', 'Order updated.');
    }

    public function moveUp(AchievementMedia $achievementMedia)
    {
        $prev = AchievementMedia::where('sort_order', '<', $achievementMedia->sort_order)->ordered()->latest('sort_order')->first();
        if ($prev) {
            [$achievementMedia->sort_order, $prev->sort_order] = [$prev->sort_order, $achievementMedia->sort_order];
            $achievementMedia->save();
            $prev->save();
        }
        return redirect()->back()->with('success', 'Order updated.');
    }

    public function moveDown(AchievementMedia $achievementMedia)
    {
        $next = AchievementMedia::where('sort_order', '>', $achievementMedia->sort_order)->ordered()->first();
        if ($next) {
            [$achievementMedia->sort_order, $next->sort_order] = [$next->sort_order, $achievementMedia->sort_order];
            $achievementMedia->save();
            $next->save();
        }
        return redirect()->back()->with('success', 'Order updated.');
    }

    public function destroy(AchievementMedia $achievementMedia)
    {
        if ($achievementMedia->image_path && Storage::disk('public')->exists($achievementMedia->image_path)) {
            Storage::disk('public')->delete($achievementMedia->image_path);
        }
        $achievementMedia->delete();

        return redirect()->route('admin.achievement-media.index')
            ->with('success', 'Image removed.');
    }
}

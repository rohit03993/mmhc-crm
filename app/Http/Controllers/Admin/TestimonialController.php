<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    public function index()
    {
        $items = Testimonial::ordered()->get();
        return view('admin.testimonials.index', compact('items'));
    }

    public function create()
    {
        return view('admin.testimonials.edit', ['item' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'quote' => 'required|string|max:2000',
            'rating' => 'nullable|numeric|min:0|max:5',
            'patient_since' => 'nullable|string|max:100',
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('testimonials', 'public');
        }

        $maxOrder = Testimonial::max('sort_order') ?? 0;
        Testimonial::create([
            'name' => $validated['name'],
            'image_path' => $path,
            'quote' => $validated['quote'],
            'rating' => $validated['rating'] ?? null,
            'patient_since' => $validated['patient_since'] ?? null,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial added.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', ['item' => $testimonial]);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'quote' => 'required|string|max:2000',
            'rating' => 'nullable|numeric|min:0|max:5',
            'patient_since' => 'nullable|string|max:100',
        ]);

        $testimonial->name = $validated['name'];
        $testimonial->quote = $validated['quote'];
        $testimonial->rating = $validated['rating'] ?? null;
        $testimonial->patient_since = $validated['patient_since'] ?? null;

        if ($request->hasFile('image')) {
            if ($testimonial->image_path && Storage::disk('public')->exists($testimonial->image_path)) {
                Storage::disk('public')->delete($testimonial->image_path);
            }
            $testimonial->image_path = $request->file('image')->store('testimonials', 'public');
        }

        $testimonial->save();

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial updated.');
    }

    public function destroy(Testimonial $testimonial)
    {
        if ($testimonial->image_path && Storage::disk('public')->exists($testimonial->image_path)) {
            Storage::disk('public')->delete($testimonial->image_path);
        }
        $testimonial->delete();
        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial removed.');
    }

    public function moveUp(Testimonial $testimonial)
    {
        $prev = Testimonial::where('sort_order', '<', $testimonial->sort_order)->ordered()->latest('sort_order')->first();
        if ($prev) {
            [$testimonial->sort_order, $prev->sort_order] = [$prev->sort_order, $testimonial->sort_order];
            $testimonial->save();
            $prev->save();
        }
        return redirect()->back()->with('success', 'Order updated.');
    }

    public function moveDown(Testimonial $testimonial)
    {
        $next = Testimonial::where('sort_order', '>', $testimonial->sort_order)->ordered()->first();
        if ($next) {
            [$testimonial->sort_order, $next->sort_order] = [$next->sort_order, $testimonial->sort_order];
            $testimonial->save();
            $next->save();
        }
        return redirect()->back()->with('success', 'Order updated.');
    }
}

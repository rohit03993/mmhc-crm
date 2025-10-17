<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use App\Models\HealthcarePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PageContentController extends Controller
{
    /**
     * Display a listing of page sections.
     */
    public function index()
    {
        $sections = PageContent::orderBy('section')->get();
        return view('admin.page-content.index', compact('sections'));
    }

    /**
     * Show the form for editing a section.
     */
    public function edit($id)
    {
        $section = PageContent::findOrFail($id);
        
        // If this is the Plans section, include healthcare plans data
        if ($section->section === 'plans') {
            $healthcarePlans = HealthcarePlan::orderBy('sort_order')->get();
            return view('admin.page-content.edit', compact('section', 'healthcarePlans'));
        }
        
        return view('admin.page-content.edit', compact('section'));
    }

    /**
     * Update the specified section.
     */
    public function update(Request $request, $id)
    {
        $section = PageContent::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'content' => 'nullable|array',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:5120', // 5MB max
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($section->image_path && Storage::disk('public')->exists($section->image_path)) {
                Storage::disk('public')->delete($section->image_path);
            }

            // Store new image
            $path = $request->file('image')->store('page-content', 'public');
            $section->image_path = $path;
        }

        $section->title = $validated['title'] ?? $section->title;
        $section->subtitle = $validated['subtitle'] ?? $section->subtitle;
        $section->content = $validated['content'] ?? $section->content;
        $section->is_active = $request->has('is_active');
        $section->save();

        return redirect()->route('admin.page-content.index')
            ->with('success', 'Page section updated successfully!');
    }

    /**
     * API endpoint to get section data
     */
    public function getSection($section)
    {
        $content = PageContent::getSection($section);
        return response()->json($content);
    }

    /**
     * Show the form for editing a healthcare plan
     */
    public function editPlan(HealthcarePlan $healthcarePlan)
    {
        return view('admin.page-content.edit-plan', compact('healthcarePlan'));
    }

    /**
     * Update the specified healthcare plan
     */
    public function updatePlan(Request $request, HealthcarePlan $healthcarePlan)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'duration_text' => 'required|string|max:50',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'icon_class' => 'nullable|string|max:255',
            'color_theme' => 'required|string|max:50',
            'is_popular' => 'boolean',
            'popular_label' => 'nullable|string|max:255',
            'button_text' => 'required|string|max:255',
            'button_link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validatedData['features'] = array_filter($validatedData['features'] ?? []);

        $healthcarePlan->update($validatedData);

        return redirect()->route('admin.page-content.index')->with('success', 'Healthcare plan updated successfully!');
    }

    /**
     * Create a new healthcare plan
     */
    public function createPlan()
    {
        return view('admin.page-content.create-plan');
    }

    /**
     * Store a new healthcare plan
     */
    public function storePlan(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'duration_text' => 'required|string|max:50',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'icon_class' => 'nullable|string|max:255',
            'color_theme' => 'required|string|max:50',
            'is_popular' => 'boolean',
            'popular_label' => 'nullable|string|max:255',
            'button_text' => 'required|string|max:255',
            'button_link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validatedData['slug'] = \Illuminate\Support\Str::slug($validatedData['name']);
        $validatedData['features'] = array_filter($validatedData['features'] ?? []);

        HealthcarePlan::create($validatedData);

        return redirect()->route('admin.page-content.index')->with('success', 'Healthcare plan created successfully!');
    }

    /**
     * Delete a healthcare plan
     */
    public function deletePlan(HealthcarePlan $healthcarePlan)
    {
        $healthcarePlan->delete();
        return redirect()->route('admin.page-content.index')->with('success', 'Healthcare plan deleted successfully!');
    }
}


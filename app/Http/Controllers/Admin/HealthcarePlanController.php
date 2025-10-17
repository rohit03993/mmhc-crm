<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HealthcarePlan;
use Illuminate\Http\Request;

class HealthcarePlanController extends Controller
{
    /**
     * Display a listing of healthcare plans.
     */
    public function index()
    {
        $plans = HealthcarePlan::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.healthcare-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new plan.
     */
    public function create()
    {
        return view('admin.healthcare-plans.create');
    }

    /**
     * Store a newly created plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'duration_days' => 'required|integer|min:1',
            'features' => 'required|array|min:1',
            'features.*' => 'required|string',
            'icon_class' => 'nullable|string',
            'color_theme' => 'required|in:blue,green,purple,orange,red,yellow',
            'is_popular' => 'boolean',
            'popular_label' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'button_text' => 'required|string|max:50',
            'button_link' => 'nullable|string',
        ]);

        // Process features array
        $validated['features'] = array_filter($validated['features']);

        HealthcarePlan::create($validated);

        return redirect()->route('admin.healthcare-plans.index')
            ->with('success', 'Healthcare plan created successfully!');
    }

    /**
     * Show the form for editing a plan.
     */
    public function edit(HealthcarePlan $healthcarePlan)
    {
        return view('admin.healthcare-plans.edit', compact('healthcarePlan'));
    }

    /**
     * Update the specified plan.
     */
    public function update(Request $request, HealthcarePlan $healthcarePlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'duration_days' => 'required|integer|min:1',
            'features' => 'required|array|min:1',
            'features.*' => 'required|string',
            'icon_class' => 'nullable|string',
            'color_theme' => 'required|in:blue,green,purple,orange,red,yellow',
            'is_popular' => 'boolean',
            'popular_label' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'button_text' => 'required|string|max:50',
            'button_link' => 'nullable|string',
        ]);

        // Process features array
        $validated['features'] = array_filter($validated['features']);

        $healthcarePlan->update($validated);

        return redirect()->route('admin.healthcare-plans.index')
            ->with('success', 'Healthcare plan updated successfully!');
    }

    /**
     * Remove the specified plan.
     */
    public function destroy(HealthcarePlan $healthcarePlan)
    {
        $healthcarePlan->delete();

        return redirect()->route('admin.healthcare-plans.index')
            ->with('success', 'Healthcare plan deleted successfully!');
    }
}

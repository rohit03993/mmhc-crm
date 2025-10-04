<?php

namespace App\Modules\Plans\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Plans\Models\Plan;
use App\Modules\Plans\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    protected $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    /**
     * Display available plans for patients
     */
    public function index()
    {
        $plans = Plan::active()->ordered()->get();
        
        return view('plans::plans.index', compact('plans'));
    }

    /**
     * Show specific plan details
     */
    public function show(Plan $plan)
    {
        if (!$plan->is_active) {
            abort(404);
        }
        
        return view('plans::plans.show', compact('plan'));
    }

    /**
     * Admin: Display all plans
     */
    public function adminIndex()
    {
        $plans = Plan::ordered()->paginate(15);
        
        return view('plans::admin.plans.index', compact('plans'));
    }

    /**
     * Admin: Show create plan form
     */
    public function create()
    {
        return view('plans::admin.plans.create');
    }

    /**
     * Admin: Store new plan
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|in:INR,USD',
            'duration_days' => 'required|integer|min:1',
            'features' => 'required|array|min:1',
            'features.*' => 'required|string',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $plan = $this->planService->createPlan($request->all());

        return redirect()->route('admin.plans')
            ->with('success', "Plan '{$plan->name}' created successfully!");
    }

    /**
     * Admin: Show edit plan form
     */
    public function edit(Plan $plan)
    {
        return view('plans::admin.plans.edit', compact('plan'));
    }

    /**
     * Admin: Update plan
     */
    public function update(Request $request, Plan $plan)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|in:INR,USD',
            'duration_days' => 'required|integer|min:1',
            'features' => 'required|array|min:1',
            'features.*' => 'required|string',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $this->planService->updatePlan($plan, $request->all());

        return redirect()->route('admin.plans')
            ->with('success', "Plan '{$plan->name}' updated successfully!");
    }

    /**
     * Admin: Delete plan
     */
    public function destroy(Plan $plan)
    {
        // Check if plan has active subscriptions
        if ($plan->subscriptions()->active()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete plan with active subscriptions!');
        }

        $planName = $plan->name;
        $plan->delete();

        return redirect()->route('admin.plans')
            ->with('success', "Plan '{$planName}' deleted successfully!");
    }
}

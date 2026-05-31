<?php

namespace App\Modules\Plans\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Plans\Models\Plan;
use App\Modules\Plans\Services\PlanService;
use App\Modules\Plans\Services\SubscriptionPaymentService;
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
        try {
            $plans = Plan::active()->forHealthcareAudience()->ordered()->get();
        } catch (\Exception $e) {
            // If plans table doesn't exist or error, return empty collection
            $plans = collect([]);
        }

        $paymentService = app(SubscriptionPaymentService::class);
        $razorpayEnabled = $paymentService->isRazorpayEnabled();
        $patientManualEnabled = (bool) config('payments.subscription.manual_enabled', false);
        $patientManualWithRazorpay = (bool) config('payments.subscription.manual_with_razorpay', false);
        $patientCheckoutAvailable = $razorpayEnabled
            || ($patientManualEnabled && (! $razorpayEnabled || $patientManualWithRazorpay));

        return view('plans::plans.index', compact(
            'plans',
            'razorpayEnabled',
            'patientManualEnabled',
            'patientManualWithRazorpay',
            'patientCheckoutAvailable'
        ));
    }

    /**
     * Show specific plan details
     */
    public function show(Plan $plan)
    {
        if (! $plan->is_active) {
            abort(404);
        }

        if ($plan->isStudentPlan() && (! auth()->check() || auth()->user()->role !== 'student')) {
            abort(404);
        }

        return view('plans::plans.show', compact('plan'));
    }

    /**
     * Admin: Display all plans
     */
    public function adminIndex()
    {
        $plans = Plan::ordered()->paginate(10);

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
            'monthly_price' => 'nullable|numeric|min:0',
            'members_included' => 'nullable|integer|min:1',
            'currency' => 'required|string|in:INR,USD',
            'duration_days' => 'required|integer|min:1',
            'features' => 'required|array|min:1',
            'features.*' => 'required|string',
            'icon_class' => 'nullable|string|max:100',
            'color_theme' => 'nullable|string|in:blue,green,purple,orange,red',
            'popular_label' => 'nullable|string|max:100',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'integer|min:0',
            'payment_options' => 'nullable|array',
            'payment_options.*.price' => 'required_with:payment_options.*|numeric|min:0',
            'payment_options.*.label' => 'required_with:payment_options.*|string|max:100',
            'payment_options.*.description' => 'nullable|string|max:255',
            'payment_options.*.payable_years' => 'nullable|numeric|min:0',
            'payment_options.*.care_benefits_years' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['payment_options'] = $this->processPaymentOptions($request);

        $plan = $this->planService->createPlan($data);

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
            'monthly_price' => 'nullable|numeric|min:0',
            'members_included' => 'nullable|integer|min:1',
            'currency' => 'required|string|in:INR,USD',
            'duration_days' => 'required|integer|min:1',
            'features' => 'required|array|min:1',
            'features.*' => 'required|string',
            'icon_class' => 'nullable|string|max:100',
            'color_theme' => 'nullable|string|in:blue,green,purple,orange,red',
            'popular_label' => 'nullable|string|max:100',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'integer|min:0',
            'payment_options' => 'nullable|array',
            'payment_options.*.price' => 'required_with:payment_options.*|numeric|min:0',
            'payment_options.*.label' => 'required_with:payment_options.*|string|max:100',
            'payment_options.*.description' => 'nullable|string|max:255',
            'payment_options.*.payable_years' => 'nullable|numeric|min:0',
            'payment_options.*.care_benefits_years' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['payment_options'] = $this->processPaymentOptions($request);

        $this->planService->updatePlan($plan, $data);

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

    /**
     * Process payment options from request
     * Only includes enabled payment options
     */
    private function processPaymentOptions(Request $request): ?array
    {
        $enabledOptions = $request->input('payment_options_enabled', []);
        $paymentOptions = $request->input('payment_options', []);
        $processedOptions = [];

        foreach ($enabledOptions as $frequency => $enabled) {
            if ($enabled && isset($paymentOptions[$frequency])) {
                $option = $paymentOptions[$frequency];

                // Build the option data
                $processedOptions[$frequency] = [
                    'price' => (float) ($option['price'] ?? 0),
                    'label' => $option['label'] ?? ucfirst(str_replace('_', ' ', $frequency)),
                    'description' => $option['description'] ?? '',
                ];

                // Add years data for non-monthly options
                if ($frequency !== 'monthly') {
                    $processedOptions[$frequency]['payable_years'] = isset($option['payable_years']) && $option['payable_years'] !== ''
                        ? (float) $option['payable_years']
                        : null;
                    $processedOptions[$frequency]['care_benefits_years'] = isset($option['care_benefits_years']) && $option['care_benefits_years'] !== ''
                        ? (float) $option['care_benefits_years']
                        : null;
                }
            }
        }

        return ! empty($processedOptions) ? $processedOptions : null;
    }
}

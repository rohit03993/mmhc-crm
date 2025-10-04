<?php

namespace App\Modules\Plans\Services;

use App\Modules\Plans\Models\Plan;

class PlanService
{
    /**
     * Create a new plan
     */
    public function createPlan(array $data): Plan
    {
        return Plan::create($data);
    }

    /**
     * Update an existing plan
     */
    public function updatePlan(Plan $plan, array $data): Plan
    {
        $plan->update($data);
        return $plan;
    }

    /**
     * Get all active plans ordered by sort order
     */
    public function getActivePlans()
    {
        return Plan::active()->ordered()->get();
    }

    /**
     * Get popular plans
     */
    public function getPopularPlans()
    {
        return Plan::active()->popular()->ordered()->get();
    }

    /**
     * Get plan statistics
     */
    public function getPlanStats()
    {
        return [
            'total_plans' => Plan::count(),
            'active_plans' => Plan::active()->count(),
            'popular_plans' => Plan::popular()->count(),
            'total_subscriptions' => Plan::with('subscriptions')->get()->sum(function ($plan) {
                return $plan->subscriptions->count();
            }),
            'active_subscriptions' => Plan::with('subscriptions')->get()->sum(function ($plan) {
                return $plan->subscriptions->where('status', 'active')->count();
            }),
        ];
    }

    /**
     * Get plan performance data
     */
    public function getPlanPerformance()
    {
        return Plan::with(['subscriptions' => function ($query) {
            $query->where('status', 'active');
        }])->get()->map(function ($plan) {
            return [
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'price' => $plan->price,
                'active_subscriptions' => $plan->subscriptions->count(),
                'revenue' => $plan->subscriptions->sum(function ($subscription) {
                    return $subscription->payments()->sum('amount');
                }),
            ];
        });
    }
}

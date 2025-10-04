<?php

namespace App\Modules\Plans\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Plans\Models\Plan;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Plans\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display user's subscriptions
     */
    public function index()
    {
        $user = Auth::user();
        $subscriptions = $this->subscriptionService->getUserSubscriptions($user);
        
        return view('plans::subscriptions.index', compact('user', 'subscriptions'));
    }

    /**
     * Show specific subscription details
     */
    public function show(Subscription $subscription)
    {
        // Check if user owns this subscription or is admin
        if ($subscription->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        return view('plans::subscriptions.show', compact('subscription'));
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $plan = Plan::findOrFail($request->plan_id);

        // Check if user already has an active subscription
        if ($user->subscriptions()->active()->exists()) {
            return redirect()->back()
                ->with('error', 'You already have an active subscription!');
        }

        $subscription = $this->subscriptionService->createSubscription($user, $plan, $request->all());

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription created successfully! Please complete the payment.');
    }

    /**
     * Cancel subscription
     */
    public function cancel(Subscription $subscription)
    {
        // Check if user owns this subscription
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$subscription->isActive()) {
            return redirect()->back()
                ->with('error', 'Only active subscriptions can be cancelled!');
        }

        $this->subscriptionService->cancelSubscription($subscription);

        return redirect()->back()
            ->with('success', 'Subscription cancelled successfully!');
    }

    /**
     * Renew subscription
     */
    public function renew(Subscription $subscription)
    {
        // Check if user owns this subscription
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        if ($subscription->isActive()) {
            return redirect()->back()
                ->with('error', 'Subscription is already active!');
        }

        $this->subscriptionService->renewSubscription($subscription);

        return redirect()->back()
            ->with('success', 'Subscription renewed successfully!');
    }

    /**
     * Admin: Display all subscriptions
     */
    public function adminIndex()
    {
        $subscriptions = $this->subscriptionService->getAllSubscriptions();
        
        return view('plans::admin.subscriptions.index', compact('subscriptions'));
    }

    /**
     * Admin: View specific subscription
     */
    public function adminView(Subscription $subscription)
    {
        return view('plans::admin.subscriptions.view', compact('subscription'));
    }

    /**
     * Admin: Approve subscription
     */
    public function approve(Subscription $subscription)
    {
        if ($subscription->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending subscriptions can be approved!');
        }

        $this->subscriptionService->approveSubscription($subscription, Auth::user());

        return redirect()->back()
            ->with('success', 'Subscription approved successfully!');
    }

    /**
     * Admin: Reject subscription
     */
    public function reject(Subscription $subscription)
    {
        if ($subscription->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending subscriptions can be rejected!');
        }

        $this->subscriptionService->rejectSubscription($subscription, Auth::user());

        return redirect()->back()
            ->with('success', 'Subscription rejected successfully!');
    }
}

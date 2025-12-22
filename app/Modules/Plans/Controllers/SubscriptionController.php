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
            'payment_frequency' => 'required|in:monthly,half_yearly,annually,full_payment',
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
        if ($this->subscriptionService->hasActiveSubscription($user)) {
            return redirect()->back()
                ->with('error', 'You already have an active subscription!');
        }

        try {
            $subscription = $this->subscriptionService->createSubscription($user, $plan, $request->all());

            return redirect()->route('subscriptions.show', $subscription)
                ->with('success', 'Subscription created successfully! Please complete the payment.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create subscription: ' . $e->getMessage())
                ->withInput();
        }
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
    public function adminIndex(Request $request)
    {
        $status = $request->get('status', 'all');
        $subscriptions = $this->subscriptionService->getAllSubscriptions($status);
        $stats = $this->subscriptionService->getSubscriptionStats();
        
        // Get counts for filter tabs
        $counts = [
            'all' => Subscription::count(),
            'pending' => Subscription::where('status', 'pending')->count(),
            'active' => Subscription::where('status', 'active')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
        ];
        
        return view('plans::admin.subscriptions.index', compact('subscriptions', 'stats', 'counts'));
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

    /**
     * Submit payment proof (screenshot or transaction ID)
     */
    public function submitPayment(Request $request, Subscription $subscription)
    {
        // Check if user owns this subscription
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        // Validate payment proof
        $validator = Validator::make($request->all(), [
            'payment_screenshot' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
            'transaction_id' => 'nullable|string|max:255',
            'payment_notes' => 'nullable|string|max:1000',
        ], [
            'payment_screenshot.image' => 'Please upload a valid image file.',
            'payment_screenshot.max' => 'Screenshot size must be less than 5MB.',
            'transaction_id.required_without' => 'Please upload screenshot OR enter transaction ID.',
        ]);

        // Ensure at least one payment proof is provided
        if (!$request->hasFile('payment_screenshot') && !$request->filled('transaction_id')) {
            return redirect()->back()
                ->with('error', 'Please upload payment screenshot OR enter transaction ID.')
                ->withInput();
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = [
                'payment_notes' => $request->payment_notes,
            ];

            // Handle screenshot upload
            if ($request->hasFile('payment_screenshot')) {
                $screenshot = $request->file('payment_screenshot');
                $filename = 'subscriptions/' . $subscription->id . '_' . time() . '.' . $screenshot->getClientOriginalExtension();
                $screenshot->storeAs('public', $filename);
                $data['payment_screenshot'] = $filename;
            }

            // Handle transaction ID
            if ($request->filled('transaction_id')) {
                $data['transaction_id'] = $request->transaction_id;
            }

            // Update subscription with payment proof
            $subscription->update($data);

            // Update payment status to partially_paid (waiting for admin verification)
            $subscription->update([
                'payment_status' => 'partially_paid',
            ]);

            return redirect()->route('subscriptions.show', $subscription)
                ->with('success', 'Payment proof submitted successfully! Admin will verify and activate your subscription.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to submit payment proof: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Admin: Verify payment and activate subscription
     */
    public function verifyPayment(Subscription $subscription)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($subscription->payment_status === 'paid') {
            return redirect()->back()
                ->with('info', 'Payment has already been verified.');
        }

        if (!$subscription->payment_screenshot && !$subscription->transaction_id) {
            return redirect()->back()
                ->with('error', 'No payment proof found to verify.');
        }

        try {
            $this->subscriptionService->verifyPayment($subscription, Auth::user());

            return redirect()->back()
                ->with('success', 'Payment verified and subscription activated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to verify payment: ' . $e->getMessage());
        }
    }

    /**
     * Admin: Reject payment
     */
    public function rejectPayment(Request $request, Subscription $subscription)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            $this->subscriptionService->rejectPayment($subscription, Auth::user(), $request->rejection_reason);

            return redirect()->back()
                ->with('success', 'Payment rejected. User will be notified.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to reject payment: ' . $e->getMessage());
        }
    }
}

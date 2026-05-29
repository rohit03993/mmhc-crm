<?php

namespace App\Modules\Plans\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Plans\Models\Plan;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Plans\Services\RazorpayService;
use App\Modules\Plans\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    protected $subscriptionService;

    protected $razorpayService;

    public function __construct(SubscriptionService $subscriptionService, RazorpayService $razorpayService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->razorpayService = $razorpayService;
    }

    /**
     * Display user's subscriptions
     */
    public function index()
    {
        try {
            $user = Auth::user();

            if (! $user) {
                return redirect()->route('auth.login');
            }

            // Get user's subscriptions
            try {
                $subscriptions = $this->subscriptionService->getUserSubscriptions($user);
            } catch (\Exception $e) {
                \Log::error('Error getting subscriptions in index', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $subscriptions = collect();
            }

            // Ensure it's a collection
            if (! ($subscriptions instanceof Collection)) {
                $subscriptions = collect($subscriptions ?: []);
            }

            // Get active subscription for upgrade/downgrade options (only one active at a time)
            $activeSubscription = null;
            try {
                $activeSubscription = $subscriptions->where('status', 'active')
                    ->where('end_date', '>', now())
                    ->sortByDesc('end_date')
                    ->first();
            } catch (\Exception $e) {
                \Log::error('Error getting active subscription', ['error' => $e->getMessage()]);
            }

            $pendingSubscription = null;
            try {
                $pendingSubscription = $subscriptions
                    ->filter(fn ($s) => $s->status === 'pending')
                    ->sortByDesc('created_at')
                    ->first();
            } catch (\Exception $e) {
                \Log::error('Error getting pending subscription', ['error' => $e->getMessage()]);
            }

            // Filter subscriptions to show only relevant ones (hide old cancelled/rejected/expired)
            // Show: pending, active, recent expired (last 6 months), recent cancelled (last 6 months)
            $filteredSubscriptions = $subscriptions->filter(function ($sub) {
                if (in_array($sub->status, ['pending', 'active'])) {
                    return true;
                }
                if ($sub->status === 'expired' && $sub->end_date->isAfter(now()->subMonths(6))) {
                    return true;
                }
                if ($sub->status === 'cancelled' && $sub->updated_at->isAfter(now()->subMonths(6))) {
                    return true;
                }

                return false;
            });

            // Get all available plans for upgrade/downgrade (only show if active subscription exists)
            $availablePlans = collect();
            if ($activeSubscription) {
                try {
                    $availablePlans = Plan::active()->ordered()->get();
                } catch (\Exception $e) {
                    \Log::error('Error getting available plans', ['error' => $e->getMessage()]);
                }
            }

            return view('plans::subscriptions.index', compact(
                'user',
                'subscriptions',
                'filteredSubscriptions',
                'activeSubscription',
                'pendingSubscription',
                'availablePlans'
            ));
        } catch (\Throwable $e) {
            \Log::error('Fatal error in subscriptions index', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response('Unable to load subscriptions. Please try again later.', 500);
        }
    }

    /**
     * Show specific subscription details
     */
    public function show($subscriptionId)
    {
        try {
            // Convert to integer and find subscription
            $subscriptionId = (int) $subscriptionId;

            if ($subscriptionId <= 0) {
                \Log::error('Invalid subscription ID', [
                    'subscription_id' => $subscriptionId,
                    'user_id' => Auth::id(),
                    'url' => request()->url(),
                ]);

                return redirect()->route('subscriptions.index')
                    ->with('error', 'Invalid subscription ID.');
            }

            // Load subscription with all required relationships
            $subscription = Subscription::with(['plan', 'user', 'referrer'])->find($subscriptionId);

            if (! $subscription) {
                \Log::error('Subscription not found', [
                    'subscription_id' => $subscriptionId,
                    'user_id' => Auth::id(),
                    'url' => request()->url(),
                ]);

                return redirect()->route('subscriptions.index')
                    ->with('error', 'Subscription not found.');
            }

            $currentUserId = Auth::id();
            $subscriptionUserId = $subscription->user_id;
            $isAdmin = Auth::user()->isAdmin();

            // Check if user owns this subscription or is admin
            if ($subscriptionUserId !== $currentUserId && ! $isAdmin) {
                \Log::warning('Unauthorized subscription access attempt', [
                    'subscription_id' => $subscription->id,
                    'subscription_user_id' => $subscriptionUserId,
                    'current_user_id' => $currentUserId,
                    'is_admin' => $isAdmin,
                    'url' => request()->url(),
                ]);

                return redirect()->route('subscriptions.index')
                    ->with('error', 'You do not have permission to view this subscription.');
            }

            // Ensure plan is loaded (required for view)
            if (! $subscription->plan) {
                \Log::error('Subscription plan not found', [
                    'subscription_id' => $subscription->id,
                    'plan_id' => $subscription->plan_id,
                ]);

                return redirect()->route('subscriptions.index')
                    ->with('error', 'Subscription plan not found. Please contact support.');
            }

            return view('plans::subscriptions.show', compact('subscription'));

        } catch (\Exception $e) {
            \Log::error('Error showing subscription', [
                'subscription_id' => $subscriptionId ?? 'unknown',
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('subscriptions.index')
                ->with('error', 'An error occurred while loading the subscription. Please try again or contact support if the problem persists.');
        }
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'payment_frequency' => 'required|in:monthly,half_yearly,annually,full_payment,student_launch',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string|max:500',
            'referrer_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $plan = Plan::findOrFail($request->plan_id);

        if ($plan->isStudentPlan() && $user->role !== 'student') {
            return redirect()->back()->with('error', 'This plan is only available to students.');
        }

        if ($user->role === 'student' && ! $plan->isStudentPlan()) {
            return redirect()->route('student-subscription.offer')
                ->with('info', 'Students must use the Student Journey membership plan.');
        }

        // Check if user already has an active subscription
        $activeSubscription = $this->subscriptionService->getActiveSubscription($user);
        $isUpgrade = $request->has('upgrade') && $request->upgrade == '1';

        // If user has active subscription and it's not an upgrade, show upgrade option
        if ($activeSubscription && ! $isUpgrade) {
            return redirect()->back()
                ->with('info', 'You already have an active subscription. You can upgrade or wait for it to expire.')
                ->with('has_active_subscription', true)
                ->with('active_subscription', $activeSubscription);
        }

        // Get referrer from request or query parameter (for referral links)
        $referrerId = $request->referrer_id ?? $request->query('ref');

        // Validate referrer is a staff member (nurse or caregiver)
        if ($referrerId) {
            $referrer = \App\Models\Core\User::find($referrerId);
            if (! $referrer || (! $referrer->isNurse() && ! $referrer->isCaregiver())) {
                $referrerId = null; // Invalid referrer, ignore it
            }
        }

        try {
            $data = $request->all();
            $data['referrer_id'] = $referrerId;

            // Check if this is an upgrade/downgrade
            if ($isUpgrade && $activeSubscription) {
                $subscription = $this->subscriptionService->upgradeDowngradeSubscription(
                    $activeSubscription,
                    $plan,
                    $data
                );
                $message = 'Subscription upgrade/downgrade initiated! Please complete the payment.';
            } else {
                $subscription = $this->subscriptionService->createSubscription($user, $plan, $data);
                $message = 'Subscription created successfully! Please complete the payment.';
            }

            // Ensure subscription was created successfully
            if (! $subscription || ! $subscription->id) {
                \Log::error('Subscription creation failed - no ID returned', [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'subscription' => $subscription,
                ]);
                throw new \Exception('Subscription was not created successfully. Please try again.');
            }

            // Refresh subscription to ensure it's fully saved and relationships are available
            $subscription->refresh();

            // Verify subscription exists in database before redirecting
            $subscriptionExists = Subscription::where('id', $subscription->id)
                ->where('user_id', $user->id)
                ->exists();

            if (! $subscriptionExists) {
                \Log::error('Subscription not found in database after creation', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                ]);
                throw new \Exception('Subscription was created but could not be verified. Please contact support.');
            }

            // Log successful subscription creation
            \Log::info('Subscription created successfully, redirecting to payment confirmation page', [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payment_frequency' => $data['payment_frequency'] ?? 'unknown',
                'redirect_url' => route('subscriptions.payment-confirmation', $subscription->id),
            ]);

            // Redirect to payment confirmation page for payment
            return redirect()->route('subscriptions.payment-confirmation', $subscription->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Create subscription failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Something went wrong. Please try again.')
                ->withInput();
        }
    }

    /**
     * Delete subscription (for pending or cancelled subscriptions)
     */
    public function destroy(Subscription $subscription)
    {
        // Check if user owns this subscription
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        // Only allow deletion if subscription is pending or cancelled
        if (! in_array($subscription->status, ['pending', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Only pending or cancelled subscriptions can be deleted. Active subscriptions can only be cancelled.');
        }

        // Delete payment screenshot if exists
        if ($subscription->payment_screenshot) {
            try {
                $filePath = storage_path('app/public/'.$subscription->payment_screenshot);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to delete payment screenshot', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $subscription->delete();

        return redirect()->route('subscriptions.index')
            ->with('success', 'Subscription deleted successfully!');
    }

    /**
     * Cancel subscription (only for active subscriptions - after admin approval)
     */
    public function cancel(Subscription $subscription)
    {
        // Check if user owns this subscription
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        // Only allow cancellation for active subscriptions
        if ($subscription->status !== 'active') {
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

        $filterUser = null;
        if ($request->filled('user_id')) {
            $filterUser = User::find((int) $request->user_id);
            if ($filterUser && ! $this->userHasCurrentlyActiveSubscription($filterUser)) {
                $filterUser = null;
            }
        }

        $subscriptions = $this->subscriptionService->getAllSubscriptions($status, $filterUser?->id, 15);
        $stats = $this->subscriptionService->getSubscriptionStats();

        $counts = [
            'all' => Subscription::count(),
            'pending' => Subscription::where('status', 'pending')->count(),
            'active' => Subscription::where('status', 'active')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
        ];

        $subscriberLeaderboard = $this->buildSubscriberLeaderboard();
        $rankByUserId = $subscriberLeaderboard->mapWithKeys(fn ($u, $idx) => [$u->id => $idx + 1]);

        $leaderboardPerPage = 15;
        $leaderTotal = $subscriberLeaderboard->count();
        $lastLeaderPage = max(1, (int) ceil($leaderTotal / $leaderboardPerPage));
        $leaderPage = min($lastLeaderPage, max(1, (int) $request->input('leaderboard_page', 1)));
        $leaderboardPaginator = new LengthAwarePaginator(
            $subscriberLeaderboard->forPage($leaderPage, $leaderboardPerPage)->values(),
            $leaderTotal,
            $leaderboardPerPage,
            $leaderPage,
            [
                'path' => $request->url(),
                'pageName' => 'leaderboard_page',
            ]
        );
        $leaderboardPaginator->withQueryString();

        return view('plans::admin.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'stats' => $stats,
            'counts' => $counts,
            'subscriberLeaderboard' => $subscriberLeaderboard,
            'leaderboardPaginator' => $leaderboardPaginator,
            'rankByUserId' => $rankByUserId,
            'topSubscriber' => $subscriberLeaderboard->first(),
            'filterUser' => $filterUser,
        ]);
    }

    /**
     * Admin: subscriber (patient) overview — all subscriptions for one user.
     */
    public function adminSubscriberDetail(User $user)
    {
        if (! $user->subscriptions()->exists()) {
            abort(404);
        }

        $leaderboard = $this->buildSubscriberLeaderboard();
        $rankIdx = $leaderboard->search(fn ($u) => (int) $u->id === (int) $user->id);
        $leaderboardRank = $rankIdx !== false ? $rankIdx + 1 : null;

        $subscriberStats = [
            'subscription_count' => (int) $user->subscriptions()->count(),
            'active_count' => (int) $user->subscriptions()->where('status', 'active')->where('end_date', '>', now())->count(),
            'pending_count' => (int) $user->subscriptions()->where('status', 'pending')->count(),
            'expired_count' => (int) $user->subscriptions()->where('status', 'expired')->count(),
            'lifetime_total' => (float) $user->subscriptions()->sum('total_amount'),
            'active_revenue_total' => (float) $user->subscriptions()
                ->where('status', 'active')
                ->where('end_date', '>', now())
                ->sum('total_amount'),
            'lifetime_paid' => (float) $user->subscriptions()->sum('paid_amount'),
        ];

        $subscriptions = Subscription::query()
            ->where('user_id', $user->id)
            ->with(['plan', 'referrer'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $adminFilterUrl = route('admin.subscriptions', ['user_id' => $user->id, 'status' => 'all']);

        return view('plans::admin.subscriptions.subscriber-detail', [
            'user' => $user,
            'leaderboardRank' => $leaderboardRank,
            'subscriberStats' => $subscriberStats,
            'subscriptions' => $subscriptions,
            'adminFilterUrl' => $adminFilterUrl,
        ]);
    }

    /**
     * Leaderboard: only subscribers with at least one *currently active* plan
     * (status active and end_date in the future). Totals and counts use active rows only.
     */
    protected function buildSubscriberLeaderboard(): Collection
    {
        return User::query()
            ->whereHas('subscriptions', fn ($q) => $this->currentlyActiveSubscriptionScope($q))
            ->withCount([
                'subscriptions as total_subscription_count',
                'subscriptions as active_subscription_count' => fn ($q) => $this->currentlyActiveSubscriptionScope($q),
            ])
            ->withSum(
                [
                    'subscriptions as active_revenue_total' => fn ($q) => $this->currentlyActiveSubscriptionScope($q),
                ],
                'total_amount'
            )
            ->get()
            ->sort(function ($a, $b) {
                $va = (float) ($a->active_revenue_total ?? 0);
                $vb = (float) ($b->active_revenue_total ?? 0);
                if ($va !== $vb) {
                    return $vb <=> $va;
                }
                $ca = (int) ($a->active_subscription_count ?? 0);
                $cb = (int) ($b->active_subscription_count ?? 0);
                if ($ca !== $cb) {
                    return $cb <=> $ca;
                }

                return strcasecmp((string) $a->name, (string) $b->name);
            })
            ->values();
    }

    /**
     * A subscription that is active and not past end date (same idea as Subscription::isActive()).
     */
    protected function currentlyActiveSubscriptionScope($query): void
    {
        $query->where('status', 'active')->where('end_date', '>', now());
    }

    protected function userHasCurrentlyActiveSubscription(User $user): bool
    {
        return $user->subscriptions()->where('status', 'active')->where('end_date', '>', now())->exists();
    }

    /**
     * Admin: View specific subscription
     */
    public function adminView(Subscription $subscription)
    {
        return view('plans::admin.subscriptions.view', compact('subscription'));
    }

    /**
     * Admin: Re-sync a demo-seeded subscription from the plan catalogue (amounts + term). Real rows are rejected.
     */
    public function adminReconcileDemoFromCatalogue(Subscription $subscription)
    {
        if (! $subscription->isDemoSeeded()) {
            return redirect()->back()
                ->with('error', 'Only demo-seeded subscriptions can be auto-synced from the catalogue. Edit real subscriptions manually.');
        }

        try {
            $this->subscriptionService->reconcileSubscriptionFromPlanCatalogue($subscription, true);
            $subscription->refresh();

            if (class_exists(\App\Modules\Incentives\Services\IncentiveCalculatorService::class) && $subscription->referrer_id) {
                try {
                    app(\App\Modules\Incentives\Services\IncentiveCalculatorService::class)
                        ->createOrUpdateSubscriptionSaleLedger($subscription);
                } catch (\Throwable $e) {
                    Log::warning('Incentive ledger update after demo reconcile failed', [
                        'subscription_id' => $subscription->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return redirect()->back()
                ->with('success', 'Demo subscription synced from the plan catalogue (amounts, GST, term, end date).');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
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

        // Only approve if payment is already verified
        if ($subscription->payment_status !== 'paid') {
            return redirect()->back()
                ->with('error', 'Please verify payment first before approving subscription!');
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
     * Show payment confirmation page (after UPI payment attempt)
     */
    public function showPaymentConfirmation(Subscription $subscription)
    {
        // Check if user owns this subscription
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        // Only show confirmation page if payment is pending
        if ($subscription->payment_status === 'paid') {
            return redirect()->route('subscriptions.show', $subscription->id)
                ->with('info', 'Payment has already been completed.');
        }

        // Reload subscription to get latest data (in case screenshot was just submitted)
        $subscription->refresh();

        $studentSubscriptionService = app(\App\Modules\Plans\Services\StudentSubscriptionService::class);

        // Students who uploaded proof earlier (partially_paid) but were never activated
        if ($subscription->payment_status === 'partially_paid'
            && ($subscription->payment_screenshot || $subscription->transaction_id)
            && $studentSubscriptionService->isStudentPlanSubscription($subscription)
            && $studentSubscriptionService->shouldAutoActivateOnManualProof()) {
            $this->subscriptionService->verifyPayment($subscription, Auth::user());

            return redirect()
                ->route('academics.dashboard')
                ->with('success', 'Your student membership payment is confirmed. Welcome!');
        }

        $subscriptionPaymentService = app(\App\Modules\Plans\Services\SubscriptionPaymentService::class);
        $manualPaymentEnabled = $subscriptionPaymentService->allowsManualPayment($subscription);
        $razorpayEnabled = $subscriptionPaymentService->isRazorpayEnabled();
        $isStudentMembership = $studentSubscriptionService->isStudentPlanSubscription($subscription);

        return view('plans::subscriptions.payment-confirmation', compact(
            'subscription',
            'manualPaymentEnabled',
            'razorpayEnabled',
            'isStudentMembership'
        ));
    }

    /**
     * Create Razorpay order for a subscription (auth user only).
     */
    public function createRazorpayOrder(Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        if (! $this->razorpayService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Razorpay is not enabled for this environment.',
            ], 422);
        }

        if ($subscription->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This subscription is already paid.',
            ], 422);
        }

        $currency = (string) config('payments.razorpay.currency', 'INR');
        $amountPaise = (int) round(((float) $subscription->total_amount) * 100);
        if ($amountPaise <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment amount for this subscription.',
            ], 422);
        }

        try {
            $order = $this->razorpayService->createOrder([
                'amount' => $amountPaise,
                'currency' => $currency,
                'receipt' => 'sub_'.$subscription->id.'_'.time(),
                'notes' => [
                    'subscription_id' => (string) $subscription->id,
                    'user_id' => (string) $subscription->user_id,
                ],
            ]);

            $subscription->update([
                'payment_provider' => 'razorpay',
                'gateway_status' => 'created',
                'gateway_payload' => $order,
                'razorpay_order_id' => $order['id'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'order_id' => $order['id'],
                'key' => $this->razorpayService->getKeyId(),
                'amount' => $order['amount'],
                'currency' => $order['currency'],
                'subscription_id' => $subscription->id,
                'customer' => [
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'contact' => preg_replace('/\D/', '', (string) Auth::user()->phone),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Razorpay order creation failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create payment order. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify Razorpay payment callback and mark subscription paid.
     */
    public function verifyRazorpayPayment(Request $request, Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Razorpay callback payload.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($subscription->razorpay_order_id && $subscription->razorpay_order_id !== $request->razorpay_order_id) {
            return response()->json([
                'success' => false,
                'message' => 'Order mismatch for this subscription.',
            ], 422);
        }

        $valid = $this->razorpayService->verifyPaymentSignature([
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature,
        ]);

        if (! $valid) {
            return response()->json([
                'success' => false,
                'message' => 'Payment signature verification failed.',
            ], 422);
        }

        $paymentPayload = [];
        try {
            $paymentPayload = $this->razorpayService->fetchPayment($request->razorpay_payment_id);
        } catch (\Throwable $e) {
            Log::warning('Could not fetch Razorpay payment details', [
                'subscription_id' => $subscription->id,
                'payment_id' => $request->razorpay_payment_id,
                'error' => $e->getMessage(),
            ]);
        }

        $this->finalizeRazorpaySuccess($subscription, [
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature,
            'gateway_payload' => $paymentPayload,
        ], Auth::user());

        $user = Auth::user();
        $redirectUrl = app(\App\Modules\Plans\Services\StudentSubscriptionService::class)
            ->postPaymentRedirectUrl($user, $subscription->fresh());

        return response()->json([
            'success' => true,
            'message' => 'Payment verified successfully.',
            'redirect_url' => $redirectUrl,
        ]);
    }

    /**
     * Razorpay webhook endpoint (no auth).
     */
    public function razorpayWebhook(Request $request)
    {
        $signature = (string) $request->header('X-Razorpay-Signature', '');
        $payload = $request->getContent();

        if (! $this->razorpayService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Razorpay webhook signature verification failed.');

            return response()->json(['ok' => false], 400);
        }

        $event = json_decode($payload, true);
        $eventId = $event['payload']['payment']['entity']['id'] ?? null;
        $eventType = $event['event'] ?? null;

        if (! in_array($eventType, ['payment.captured', 'payment.authorized'], true)) {
            return response()->json(['ok' => true, 'ignored' => true]);
        }

        $entity = $event['payload']['payment']['entity'] ?? [];
        $orderId = $entity['order_id'] ?? null;
        $paymentId = $entity['id'] ?? null;

        if (! $orderId || ! $paymentId) {
            return response()->json(['ok' => false, 'message' => 'Invalid webhook payload'], 422);
        }

        $subscription = Subscription::query()
            ->where('razorpay_order_id', $orderId)
            ->first();

        if (! $subscription) {
            return response()->json(['ok' => true, 'message' => 'No matching subscription']);
        }

        if ($subscription->razorpay_event_id && $subscription->razorpay_event_id === $eventId) {
            return response()->json(['ok' => true, 'duplicate' => true]);
        }

        $this->finalizeRazorpaySuccess($subscription, [
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
            'razorpay_event_id' => $eventId,
            'gateway_payload' => $event,
        ]);

        return response()->json(['ok' => true]);
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

        $studentSubscriptionService = app(\App\Modules\Plans\Services\StudentSubscriptionService::class);
        if (! $studentSubscriptionService->allowsManualPayment($subscription)) {
            return redirect()->route('subscriptions.payment-confirmation', $subscription)
                ->with('error', 'Manual payment proof is disabled. Please complete payment via Razorpay.');
        }

        // Validate payment proof - Only screenshot required now
        $validator = Validator::make($request->all(), [
            'payment_screenshot' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
            'payment_notes' => 'nullable|string|max:1000',
        ], [
            'payment_screenshot.required' => 'Please upload a payment screenshot.',
            'payment_screenshot.image' => 'Please upload a valid image file.',
            'payment_screenshot.max' => 'Screenshot size must be less than 5MB.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = [
                'payment_notes' => $request->payment_notes,
            ];

            // Handle screenshot upload (required)
            $screenshot = $request->file('payment_screenshot');

            // Ensure subscriptions directory exists
            $subscriptionsDir = storage_path('app/public/subscriptions');
            if (! \File::exists($subscriptionsDir)) {
                \File::makeDirectory($subscriptionsDir, 0755, true);
            }

            $filename = 'subscriptions/'.$subscription->id.'_'.time().'.'.$screenshot->getClientOriginalExtension();

            // Store the file using Storage facade for better reliability
            $storedPath = Storage::disk('public')->putFileAs('subscriptions', $screenshot, $subscription->id.'_'.time().'.'.$screenshot->getClientOriginalExtension());

            // Verify file was actually stored
            if (! Storage::disk('public')->exists($storedPath)) {
                \Log::error('File upload failed - file not found after Storage::putFileAs', [
                    'stored_path' => $storedPath,
                    'filename' => $filename,
                    'subscription_id' => $subscription->id,
                ]);
                throw new \Exception('File upload failed. Please try again.');
            }

            \Log::info('Payment screenshot uploaded successfully', [
                'subscription_id' => $subscription->id,
                'stored_path' => $storedPath,
                'file_size' => Storage::disk('public')->size($storedPath),
            ]);

            // Store relative path (without 'public/' prefix) for database
            $data['payment_screenshot'] = $storedPath;

            // Update subscription with payment proof
            $subscription->update($data);

            if ($studentSubscriptionService->isStudentPlanSubscription($subscription)
                && $studentSubscriptionService->shouldAutoActivateOnManualProof()) {
                $this->subscriptionService->verifyPayment($subscription, Auth::user());

                return redirect()
                    ->route('academics.dashboard')
                    ->with('success', 'Payment received! Your student membership is now active.');
            }

            $subscription->update([
                'payment_status' => 'partially_paid',
            ]);

            return redirect()->route('subscriptions.payment-confirmation', $subscription)
                ->with('success', 'Payment screenshot submitted successfully! Our team will contact you within 24 hours and activate the subscription if payment is done.');

        } catch (\Exception $e) {
            \Log::error('Payment screenshot upload error', [
                'subscription_id' => $subscription->id ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Something went wrong. Please try again.')
                ->withInput();
        }
    }

    /**
     * Admin: Verify payment and activate subscription
     */
    public function verifyPayment(Subscription $subscription)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($subscription->payment_status === 'paid') {
            return redirect()->back()
                ->with('info', 'Payment has already been verified.');
        }

        if (! $subscription->payment_screenshot && ! $subscription->transaction_id) {
            return redirect()->back()
                ->with('error', 'No payment proof found to verify.');
        }

        try {
            $this->subscriptionService->verifyPayment($subscription, Auth::user());

            return redirect()->back()
                ->with('success', 'Payment verified and subscription activated successfully!');

        } catch (\Exception $e) {
            \Log::error('Verify payment failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * View payment screenshot (secure route)
     */
    public function viewPaymentScreenshot($id)
    {
        try {
            // Convert to integer
            $subscriptionId = (int) $id;

            if ($subscriptionId <= 0) {
                \Log::error('Invalid subscription ID for screenshot', [
                    'id' => $id,
                    'user_id' => Auth::id(),
                    'is_admin' => Auth::check() ? Auth::user()->isAdmin() : false,
                ]);

                return response('Invalid subscription ID', 400);
            }

            // Find subscription by ID
            $subscription = Subscription::find($subscriptionId);

            if (! $subscription) {
                \Log::error('Subscription not found for screenshot', [
                    'subscription_id' => $subscriptionId,
                    'user_id' => Auth::id(),
                    'is_admin' => Auth::check() ? Auth::user()->isAdmin() : false,
                ]);

                return response('Subscription not found', 404);
            }

            // Check if user has permission (owner or admin)
            $currentUserId = Auth::id();
            $isAdmin = Auth::check() && Auth::user()->isAdmin();
            $isOwner = $subscription->user_id === $currentUserId;

            if (! $isOwner && ! $isAdmin) {
                \Log::warning('Unauthorized screenshot access attempt', [
                    'subscription_id' => $subscriptionId,
                    'subscription_user_id' => $subscription->user_id,
                    'current_user_id' => $currentUserId,
                    'is_admin' => $isAdmin,
                ]);

                return response('Unauthorized access', 403);
            }

            // Check if screenshot exists in database
            if (! $subscription->payment_screenshot) {
                \Log::warning('Payment screenshot not found in database', [
                    'subscription_id' => $subscriptionId,
                    'user_id' => $currentUserId,
                ]);

                return response('Payment screenshot not uploaded yet', 404);
            }

            // Check if file exists in storage - try multiple paths
            $filePath = $subscription->payment_screenshot;
            $fullPath = null;

            // Try different path variations
            $pathsToTry = [
                // Try 1: Storage disk public (most common)
                Storage::disk('public')->path($filePath),
                // Try 2: Direct storage path
                storage_path('app/public/'.$filePath),
                // Try 3: Without public prefix
                storage_path('app/'.$filePath),
                // Try 4: Check if it's already a full path
                $filePath,
                // Try 5: Public storage symlink path
                public_path('storage/'.$filePath),
            ];

            foreach ($pathsToTry as $path) {
                if ($path && file_exists($path) && is_file($path)) {
                    $fullPath = $path;
                    break;
                }
            }

            if (! $fullPath || ! file_exists($fullPath)) {
                \Log::error('Payment screenshot file not found on disk', [
                    'subscription_id' => $subscriptionId,
                    'file_path_in_db' => $filePath,
                    'tried_paths' => $pathsToTry,
                    'user_id' => $currentUserId,
                ]);

                // Return a user-friendly error message
                return response('Payment screenshot file not found. The file may have been deleted or moved. Please contact support if you need assistance.', 404)
                    ->header('Content-Type', 'text/plain');
            }

            // Verify it's actually an image file
            $mimeType = mime_content_type($fullPath);
            if (! $mimeType || ! str_starts_with($mimeType, 'image/')) {
                \Log::error('Payment screenshot is not a valid image', [
                    'subscription_id' => $subscriptionId,
                    'file_path' => $fullPath,
                    'mime_type' => $mimeType,
                ]);

                return response('Invalid image file', 400);
            }

            \Log::info('Payment screenshot accessed successfully', [
                'subscription_id' => $subscriptionId,
                'user_id' => $currentUserId,
                'is_admin' => $isAdmin,
            ]);

            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="payment-screenshot-'.$subscription->id.'.jpg"',
                'Cache-Control' => 'private, max-age=3600',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Model not found in screenshot view', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response('Subscription not found', 404);
        } catch (\Exception $e) {
            \Log::error('Error viewing payment screenshot', [
                'id' => $id,
                'subscription_id' => $subscriptionId ?? 'unknown',
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response('Unable to load image. Please try again later.', 500);
        }
    }

    /**
     * Admin: Reject payment
     */
    public function rejectPayment(Request $request, Subscription $subscription)
    {
        if (! Auth::user()->isAdmin()) {
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
            \Log::error('Reject payment failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }

    private function finalizeRazorpaySuccess(Subscription $subscription, array $data, ?User $actor = null): void
    {
        if ($subscription->payment_status === 'paid') {
            return;
        }

        $subscription->update([
            'payment_provider' => 'razorpay',
            'gateway_status' => 'captured',
            'gateway_payload' => $data['gateway_payload'] ?? $subscription->gateway_payload,
            'razorpay_order_id' => $data['razorpay_order_id'] ?? $subscription->razorpay_order_id,
            'razorpay_payment_id' => $data['razorpay_payment_id'] ?? $subscription->razorpay_payment_id,
            'razorpay_signature' => $data['razorpay_signature'] ?? $subscription->razorpay_signature,
            'razorpay_event_id' => $data['razorpay_event_id'] ?? $subscription->razorpay_event_id,
            'webhook_received_at' => now(),
            'transaction_id' => $data['razorpay_payment_id'] ?? $subscription->transaction_id,
            'payment_status' => 'paid',
            'paid_amount' => $subscription->total_amount,
            'payment_verified_by' => $actor?->id,
            'payment_verified_at' => now(),
            'status' => 'active',
            'approved_by' => $actor?->id,
            'approved_at' => now(),
        ]);

        $subscription->refresh();

        if ($subscription->referrer_id) {
            try {
                app(\App\Modules\Incentives\Services\IncentiveCalculatorService::class)
                    ->createOrUpdateSubscriptionSaleLedger($subscription);
            } catch (\Throwable $e) {
                Log::error('Razorpay finalize: subscription incentive failed', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}

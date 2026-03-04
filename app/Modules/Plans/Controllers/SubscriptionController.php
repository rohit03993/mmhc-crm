<?php

namespace App\Modules\Plans\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Plans\Models\Plan;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Plans\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

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
        try {
            $user = Auth::user();
            
            if (!$user) {
                return redirect()->route('auth.login');
            }
            
            // Get user's subscriptions
            try {
            $subscriptions = $this->subscriptionService->getUserSubscriptions($user);
            } catch (\Exception $e) {
                \Log::error('Error getting subscriptions in index', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $subscriptions = collect();
            }
            
            // Ensure it's a collection
            if (!($subscriptions instanceof Collection)) {
                $subscriptions = collect($subscriptions ?: []);
            }
            
            // Get active subscription for upgrade/downgrade options (only one active at a time)
            $activeSubscription = null;
            try {
                $activeSubscription = $subscriptions->where('status', 'active')
                    ->where('end_date', '>', now())
                    ->first();
            } catch (\Exception $e) {
                \Log::error('Error getting active subscription', ['error' => $e->getMessage()]);
            }
            
            // Filter subscriptions to show only relevant ones (hide old cancelled/rejected/expired)
            // Show: pending, active, recent expired (last 6 months), recent cancelled (last 6 months)
            $filteredSubscriptions = $subscriptions->filter(function($sub) {
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
            
            return view('plans::subscriptions.index', compact('user', 'subscriptions', 'filteredSubscriptions', 'activeSubscription', 'availablePlans'));
        } catch (\Throwable $e) {
            \Log::error('Fatal error in subscriptions index', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
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
                    'url' => request()->url()
                ]);
                return redirect()->route('subscriptions.index')
                    ->with('error', 'Invalid subscription ID.');
            }
            
            // Load subscription with all required relationships
            $subscription = Subscription::with(['plan', 'user', 'referrer'])->find($subscriptionId);
            
            if (!$subscription) {
                \Log::error('Subscription not found', [
                    'subscription_id' => $subscriptionId,
                    'user_id' => Auth::id(),
                    'url' => request()->url()
                ]);
                return redirect()->route('subscriptions.index')
                    ->with('error', 'Subscription not found.');
            }
            
            $currentUserId = Auth::id();
            $subscriptionUserId = $subscription->user_id;
            $isAdmin = Auth::user()->isAdmin();
            
            // Check if user owns this subscription or is admin
            if ($subscriptionUserId !== $currentUserId && !$isAdmin) {
                \Log::warning('Unauthorized subscription access attempt', [
                    'subscription_id' => $subscription->id,
                    'subscription_user_id' => $subscriptionUserId,
                    'current_user_id' => $currentUserId,
                    'is_admin' => $isAdmin,
                    'url' => request()->url()
                ]);
                
                return redirect()->route('subscriptions.index')
                    ->with('error', 'You do not have permission to view this subscription.');
            }
            
            // Ensure plan is loaded (required for view)
            if (!$subscription->plan) {
                \Log::error('Subscription plan not found', [
                    'subscription_id' => $subscription->id,
                    'plan_id' => $subscription->plan_id
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
                'trace' => $e->getTraceAsString()
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
            'payment_frequency' => 'required|in:monthly,half_yearly,annually,full_payment',
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

        // Check if user already has an active subscription
        $activeSubscription = $this->subscriptionService->getActiveSubscription($user);
        $isUpgrade = $request->has('upgrade') && $request->upgrade == '1';
        
        // If user has active subscription and it's not an upgrade, show upgrade option
        if ($activeSubscription && !$isUpgrade) {
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
            if (!$referrer || (!$referrer->isNurse() && !$referrer->isCaregiver())) {
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
            if (!$subscription || !$subscription->id) {
                \Log::error('Subscription creation failed - no ID returned', [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'subscription' => $subscription
                ]);
                throw new \Exception('Subscription was not created successfully. Please try again.');
            }
            
            // Refresh subscription to ensure it's fully saved and relationships are available
            $subscription->refresh();
            
            // Verify subscription exists in database before redirecting
            $subscriptionExists = Subscription::where('id', $subscription->id)
                ->where('user_id', $user->id)
                ->exists();
            
            if (!$subscriptionExists) {
                \Log::error('Subscription not found in database after creation', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $user->id,
                    'plan_id' => $plan->id
                ]);
                throw new \Exception('Subscription was created but could not be verified. Please contact support.');
            }
            
            // Log successful subscription creation
            \Log::info('Subscription created successfully, redirecting to payment confirmation page', [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payment_frequency' => $data['payment_frequency'] ?? 'unknown',
                'redirect_url' => route('subscriptions.payment-confirmation', $subscription->id)
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
                'trace' => $e->getTraceAsString()
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
        if (!in_array($subscription->status, ['pending', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Only pending or cancelled subscriptions can be deleted. Active subscriptions can only be cancelled.');
        }

        // Delete payment screenshot if exists
        if ($subscription->payment_screenshot) {
            try {
                $filePath = storage_path('app/public/' . $subscription->payment_screenshot);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to delete payment screenshot', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
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

        return view('plans::subscriptions.payment-confirmation', compact('subscription'));
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
            if (!\File::exists($subscriptionsDir)) {
                \File::makeDirectory($subscriptionsDir, 0755, true);
            }
            
            $filename = 'subscriptions/' . $subscription->id . '_' . time() . '.' . $screenshot->getClientOriginalExtension();
            
            // Store the file using Storage facade for better reliability
            $storedPath = Storage::disk('public')->putFileAs('subscriptions', $screenshot, $subscription->id . '_' . time() . '.' . $screenshot->getClientOriginalExtension());
            
            // Verify file was actually stored
            if (!Storage::disk('public')->exists($storedPath)) {
                \Log::error('File upload failed - file not found after Storage::putFileAs', [
                    'stored_path' => $storedPath,
                    'filename' => $filename,
                    'subscription_id' => $subscription->id
                ]);
                throw new \Exception('File upload failed. Please try again.');
            }
            
            \Log::info('Payment screenshot uploaded successfully', [
                'subscription_id' => $subscription->id,
                'stored_path' => $storedPath,
                'file_size' => Storage::disk('public')->size($storedPath)
            ]);
            
            // Store relative path (without 'public/' prefix) for database
            $data['payment_screenshot'] = $storedPath;

            // Update subscription with payment proof
            $subscription->update($data);

            // Update payment status to partially_paid (waiting for admin verification)
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
                'trace' => $e->getTraceAsString()
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
            \Log::error('Verify payment failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
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
                    'is_admin' => Auth::check() ? Auth::user()->isAdmin() : false
                ]);
                return response('Invalid subscription ID', 400);
            }
            
            // Find subscription by ID
            $subscription = Subscription::find($subscriptionId);
            
            if (!$subscription) {
                \Log::error('Subscription not found for screenshot', [
                    'subscription_id' => $subscriptionId,
                    'user_id' => Auth::id(),
                    'is_admin' => Auth::check() ? Auth::user()->isAdmin() : false
                ]);
                return response('Subscription not found', 404);
            }
            
            // Check if user has permission (owner or admin)
            $currentUserId = Auth::id();
            $isAdmin = Auth::check() && Auth::user()->isAdmin();
            $isOwner = $subscription->user_id === $currentUserId;
            
            if (!$isOwner && !$isAdmin) {
                \Log::warning('Unauthorized screenshot access attempt', [
                    'subscription_id' => $subscriptionId,
                    'subscription_user_id' => $subscription->user_id,
                    'current_user_id' => $currentUserId,
                    'is_admin' => $isAdmin
                ]);
                return response('Unauthorized access', 403);
            }

            // Check if screenshot exists in database
            if (!$subscription->payment_screenshot) {
                \Log::warning('Payment screenshot not found in database', [
                    'subscription_id' => $subscriptionId,
                    'user_id' => $currentUserId
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
                storage_path('app/public/' . $filePath),
            // Try 3: Without public prefix
                storage_path('app/' . $filePath),
            // Try 4: Check if it's already a full path
                $filePath,
                // Try 5: Public storage symlink path
                public_path('storage/' . $filePath),
            ];
            
            foreach ($pathsToTry as $path) {
                if ($path && file_exists($path) && is_file($path)) {
                    $fullPath = $path;
                    break;
                }
            }
            
            if (!$fullPath || !file_exists($fullPath)) {
                \Log::error('Payment screenshot file not found on disk', [
                    'subscription_id' => $subscriptionId,
                    'file_path_in_db' => $filePath,
                    'tried_paths' => $pathsToTry,
                    'user_id' => $currentUserId
                ]);
                
                // Return a user-friendly error message
                return response('Payment screenshot file not found. The file may have been deleted or moved. Please contact support if you need assistance.', 404)
                    ->header('Content-Type', 'text/plain');
            }

            // Verify it's actually an image file
            $mimeType = mime_content_type($fullPath);
            if (!$mimeType || !str_starts_with($mimeType, 'image/')) {
                \Log::error('Payment screenshot is not a valid image', [
                    'subscription_id' => $subscriptionId,
                    'file_path' => $fullPath,
                    'mime_type' => $mimeType
                ]);
                return response('Invalid image file', 400);
            }
            
            \Log::info('Payment screenshot accessed successfully', [
                'subscription_id' => $subscriptionId,
                'user_id' => $currentUserId,
                'is_admin' => $isAdmin
            ]);
            
            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="payment-screenshot-' . $subscription->id . '.jpg"',
                'Cache-Control' => 'private, max-age=3600',
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Model not found in screenshot view', [
                'id' => $id,
                'error' => $e->getMessage()
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
                'trace' => $e->getTraceAsString()
            ]);
            return response('Unable to load image. Please try again later.', 500);
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
            \Log::error('Reject payment failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }
}

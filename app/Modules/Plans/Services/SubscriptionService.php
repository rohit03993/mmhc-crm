<?php

namespace App\Modules\Plans\Services;

use App\Models\Core\User;
use App\Modules\Plans\Models\Plan;
use App\Modules\Plans\Models\Subscription;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Create a new subscription
     */
    public function createSubscription(User $user, Plan $plan, array $data = []): Subscription
    {
        $startDate = now();
        $endDate = $startDate->copy()->addDays($plan->duration_days);

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'auto_renew' => $data['auto_renew'] ?? false,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Approve a subscription
     */
    public function approveSubscription(Subscription $subscription, User $approvedBy): Subscription
    {
        $subscription->update([
            'status' => 'active',
            'approved_by' => $approvedBy->id,
            'approved_at' => now(),
        ]);

        return $subscription;
    }

    /**
     * Reject a subscription
     */
    public function rejectSubscription(Subscription $subscription, User $rejectedBy): Subscription
    {
        $subscription->update([
            'status' => 'rejected',
            'approved_by' => $rejectedBy->id,
            'approved_at' => now(),
        ]);

        return $subscription;
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => 'cancelled',
        ]);

        return $subscription;
    }

    /**
     * Renew a subscription
     */
    public function renewSubscription(Subscription $subscription): Subscription
    {
        $startDate = now();
        $endDate = $startDate->copy()->addDays($subscription->plan->duration_days);

        $subscription->update([
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $subscription;
    }

    /**
     * Get user's subscriptions
     */
    public function getUserSubscriptions(User $user)
    {
        return $user->subscriptions()
                   ->with(['plan', 'payments'])
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    /**
     * Get all subscriptions for admin
     */
    public function getAllSubscriptions()
    {
        return Subscription::with(['user', 'plan', 'approvedBy'])
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);
    }

    /**
     * Get subscription statistics
     */
    public function getSubscriptionStats()
    {
        return [
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::active()->count(),
            'pending_subscriptions' => Subscription::pending()->count(),
            'expired_subscriptions' => Subscription::expired()->count(),
            'cancelled_subscriptions' => Subscription::cancelled()->count(),
        ];
    }

    /**
     * Check for expired subscriptions and update their status
     */
    public function checkExpiredSubscriptions(): int
    {
        $expiredCount = Subscription::where('status', 'active')
                                  ->where('end_date', '<', now())
                                  ->count();

        if ($expiredCount > 0) {
            Subscription::where('status', 'active')
                       ->where('end_date', '<', now())
                       ->update(['status' => 'expired']);
        }

        return $expiredCount;
    }

    /**
     * Get subscriptions expiring soon (within 7 days)
     */
    public function getExpiringSubscriptions(int $days = 7)
    {
        return Subscription::active()
                          ->whereBetween('end_date', [now(), now()->addDays($days)])
                          ->with(['user', 'plan'])
                          ->get();
    }
}

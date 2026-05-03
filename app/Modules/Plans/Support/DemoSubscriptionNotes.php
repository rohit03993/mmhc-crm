<?php

namespace App\Modules\Plans\Support;

use App\Modules\Plans\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;

/**
 * Identifies subscription rows created by demo seeders (safe to prune or auto-reconcile from catalogue).
 */
final class DemoSubscriptionNotes
{
    public const EXACT_NOTES = [
        'INCENTIVE_DEMO_SUBSCRIPTION_ACTIVE',
        'DEMO_RAZORPAY_SUBSCRIPTION_SUCCESS',
    ];

    public static function isDemo(?string $notes): bool
    {
        if ($notes === null || $notes === '') {
            return false;
        }

        if (in_array($notes, self::EXACT_NOTES, true)) {
            return true;
        }

        return str_starts_with($notes, 'DEMO_NETWORK_SUB_');
    }

    public static function scopeDemo(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereIn('notes', self::EXACT_NOTES)
                ->orWhere('notes', 'like', 'DEMO_NETWORK_SUB%');
        });
    }

    public static function subscriptionQuery(): Builder
    {
        return self::scopeDemo(Subscription::query());
    }
}

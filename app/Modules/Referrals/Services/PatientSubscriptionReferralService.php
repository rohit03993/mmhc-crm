<?php

namespace App\Modules\Referrals\Services;

use App\Models\Core\User;
use App\Modules\Plans\Models\Subscription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PatientSubscriptionReferralService
{
    public function getPlanReferralLink(User $patient): string
    {
        return route('plans.index', ['ref' => $patient->id]);
    }

    /**
     * @return array{total_referrals: int, active_referrals: int}
     */
    public function getStats(User $patient): array
    {
        $base = Subscription::query()->where('referrer_id', $patient->id);

        return [
            'total_referrals' => (clone $base)->count(),
            'active_referrals' => (clone $base)->where('status', 'active')->count(),
        ];
    }

    public function getReferralHistory(User $patient, int $perPage = 10): LengthAwarePaginator
    {
        return Subscription::query()
            ->where('referrer_id', $patient->id)
            ->with(['user:id,name,unique_id', 'plan:id,name'])
            ->latest()
            ->paginate($perPage);
    }
}

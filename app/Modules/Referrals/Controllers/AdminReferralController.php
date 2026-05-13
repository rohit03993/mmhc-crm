<?php

namespace App\Modules\Referrals\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Incentives\Models\IncentiveLedger;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Referrals\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminReferralController extends Controller
{
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    /**
     * Show admin referral dashboard
     */
    public function index(Request $request)
    {
        // All staff (nurses/caregivers) with referrals, ranked by incentive (full list for stats + filter dropdown).
        $staffWithReferrals = User::whereIn('role', ['nurse', 'caregiver'])
            ->whereHas('referrals')
            ->withCount([
                'referrals as total_referrals',
                'referrals as completed_referrals' => function ($query) {
                    $query->referralMobileOtpVerified()
                        ->where('status', 'completed')
                        ->whereNotNull('referred_id');
                },
                'referrals as pending_referrals' => function ($query) {
                    $query->where(function ($q) {
                        $q->where('status', 'pending')
                            ->orWhere('verification_status', 'pending')
                            ->orWhere(function ($legacy) {
                                $legacy->where('status', 'completed')
                                    ->where(function ($nullOrPending) {
                                        $nullOrPending->whereNull('verification_status')
                                            ->orWhere('verification_status', '!=', 'verified')
                                            ->orWhereNull('verified_at');
                                    });
                            });
                    })->whereNotNull('referred_id');
                },
            ])
            ->get()
            ->map(function ($staff) {
                // Staff referral incentives are ledger-based (fallback to referral rows if missing ledger).
                $totalAmount = (float) IncentiveLedger::query()
                    ->where('staff_id', $staff->id)
                    ->where('source_type', IncentiveLedger::SOURCE_REFERRAL)
                    ->sum('final_amount');
                if ($totalAmount <= 0) {
                    $totalAmount = (float) Referral::where('referrer_id', $staff->id)
                        ->referralMobileOtpVerified()
                        ->where('status', 'completed')
                        ->whereNotNull('referred_id')
                        ->sum('reward_amount');
                }

                // Get referral code using the service
                $referralCode = $this->referralService->getOrCreateReferralCode($staff);

                $staff->total_reward_amount = $totalAmount;
                $staff->referral_code = $referralCode;

                return $staff;
            })
            // Rank by total incentive (highest first), then completed count, then name. Reindex for Blade $loop order.
            ->sort(function ($a, $b) {
                $ta = (float) ($a->total_reward_amount ?? 0);
                $tb = (float) ($b->total_reward_amount ?? 0);
                if ($ta !== $tb) {
                    return $tb <=> $ta;
                }
                $ca = (int) $a->completed_referrals;
                $cb = (int) $b->completed_referrals;
                if ($ca !== $cb) {
                    return $cb <=> $ca;
                }

                return strcasecmp((string) $a->name, (string) $b->name);
            })
            ->values();

        $staffPerformancePerPage = 15;
        $staffTotal = $staffWithReferrals->count();
        $lastStaffPage = max(1, (int) ceil($staffTotal / $staffPerformancePerPage));
        $staffPerformancePage = min($lastStaffPage, max(1, (int) $request->input('staff_page', 1)));
        $staffPerformancePaginator = new LengthAwarePaginator(
            $staffWithReferrals->forPage($staffPerformancePage, $staffPerformancePerPage)->values(),
            $staffTotal,
            $staffPerformancePerPage,
            $staffPerformancePage,
            [
                'path' => $request->url(),
                'pageName' => 'staff_page',
            ]
        );
        $staffPerformancePaginator->withQueryString();

        // Get all referrals with details
        $allReferrals = Referral::with(['referrer', 'referred'])
            ->whereNotNull('referred_id')
            ->orderBy('completed_at', 'desc')
            ->paginate(10);

        // Overall statistics
        $overallStats = [
            'total_staff_with_referrals' => $staffWithReferrals->count(),
            'total_referrals' => Referral::whereNotNull('referred_id')->count(),
            'completed_referrals' => Referral::referralMobileOtpVerified()
                ->where('status', 'completed')
                ->whereNotNull('referred_id')
                ->count(),
            'pending_referrals' => Referral::where('status', 'pending')
                ->whereNull('referred_id')
                ->count(),
            'total_reward_amount' => (float) IncentiveLedger::query()
                ->where('source_type', IncentiveLedger::SOURCE_REFERRAL)
                ->sum('final_amount'),
            'top_referrer' => $staffWithReferrals->first(),
        ];

        // Filter by staff member if requested
        $selectedStaff = null;
        if ($request->has('staff_id')) {
            $selectedStaff = User::find($request->staff_id);
            if ($selectedStaff && $selectedStaff->isStaff()) {
                $allReferrals = Referral::where('referrer_id', $selectedStaff->id)
                    ->whereNotNull('referred_id')
                    ->with(['referrer', 'referred'])
                    ->orderBy('completed_at', 'desc')
                    ->paginate(10);
            }
        }

        return view('referrals::admin.index', compact(
            'staffWithReferrals',
            'staffPerformancePaginator',
            'allReferrals',
            'overallStats',
            'selectedStaff'
        ));
    }

    /**
     * Show detailed view for a specific staff member's referrals
     */
    public function showStaffReferrals(User $staff)
    {
        // Ensure user is staff
        if (! $staff->isStaff()) {
            abort(404, 'Staff member not found');
        }

        // Get staff referral statistics
        $referralStats = $this->referralService->getReferralStats($staff);

        // Get referral history
        $referrals = Referral::where('referrer_id', $staff->id)
            ->whereNotNull('referred_id')
            ->with('referred')
            ->orderBy('completed_at', 'desc')
            ->paginate(10);

        // Get referral code using the service
        $referralCode = $this->referralService->getOrCreateReferralCode($staff);

        $referralLink = route('auth.register', ['ref' => $referralCode]);

        // Get first referral record for display
        $firstReferralRecord = Referral::where('referrer_id', $staff->id)
            ->where('referral_code', $referralCode)
            ->orderBy('created_at', 'asc')
            ->first();

        return view('referrals::admin.staff-details', compact(
            'staff',
            'referralStats',
            'referrals',
            'referralLink',
            'referralCode',
            'firstReferralRecord'
        ));
    }
}

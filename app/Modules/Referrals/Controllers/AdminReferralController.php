<?php

namespace App\Modules\Referrals\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Referrals\Services\ReferralService;
use App\Models\Core\User;
use App\Modules\Referrals\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        // Get all staff members (nurses and caregivers) who have referrals
        $staffWithReferrals = User::whereIn('role', ['nurse', 'caregiver'])
            ->whereHas('referrals')
            ->withCount([
                'referrals as total_referrals',
                'referrals as completed_referrals' => function($query) {
                    $query->where('status', 'completed')->whereNotNull('referred_id');
                },
                'referrals as pending_referrals' => function($query) {
                    $query->where('status', 'pending')->whereNull('referred_id');
                }
            ])
            ->get()
            ->map(function($staff) {
                // Calculate total reward points and amount for this staff
                $totalPoints = Referral::where('referrer_id', $staff->id)
                    ->where('status', 'completed')
                    ->whereNotNull('referred_id')
                    ->sum('reward_points');
                
                $totalAmount = Referral::where('referrer_id', $staff->id)
                    ->where('status', 'completed')
                    ->whereNotNull('referred_id')
                    ->sum('reward_amount');
                
                // Get referral code using the service
                $referralCode = $this->referralService->getOrCreateReferralCode($staff);
                
                $staff->total_reward_points = $totalPoints;
                $staff->total_reward_amount = $totalAmount;
                $staff->referral_code = $referralCode;
                
                return $staff;
            })
            ->sortByDesc('completed_referrals');

        // Get all referrals with details
        $allReferrals = Referral::with(['referrer', 'referred'])
            ->whereNotNull('referred_id')
            ->orderBy('completed_at', 'desc')
            ->paginate(20);

        // Overall statistics
        $overallStats = [
            'total_staff_with_referrals' => $staffWithReferrals->count(),
            'total_referrals' => Referral::whereNotNull('referred_id')->count(),
            'completed_referrals' => Referral::where('status', 'completed')
                ->whereNotNull('referred_id')
                ->count(),
            'pending_referrals' => Referral::where('status', 'pending')
                ->whereNull('referred_id')
                ->count(),
            'total_reward_points' => Referral::where('status', 'completed')
                ->whereNotNull('referred_id')
                ->sum('reward_points'),
            'total_reward_amount' => Referral::where('status', 'completed')
                ->whereNotNull('referred_id')
                ->sum('reward_amount'),
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
                    ->paginate(20);
            }
        }

        return view('referrals::admin.index', compact(
            'staffWithReferrals',
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
        if (!$staff->isStaff()) {
            abort(404, 'Staff member not found');
        }

        // Get staff referral statistics
        $referralStats = $this->referralService->getReferralStats($staff);
        
        // Get referral history
        $referrals = Referral::where('referrer_id', $staff->id)
            ->whereNotNull('referred_id')
            ->with('referred')
            ->orderBy('completed_at', 'desc')
            ->paginate(20);

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

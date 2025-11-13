<?php

namespace App\Modules\Referrals\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Referrals\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
        $this->middleware('auth');
    }

    /**
     * Get referral link for authenticated staff member
     */
    public function getReferralLink()
    {
        $user = Auth::user();
        
        // Check if user is staff (nurse or caregiver)
        if (!$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Only nurses and caregivers can generate referral links.'
            ], 403);
        }
        
        $referralLink = $this->referralService->getReferralLink($user);
        
        return response()->json([
            'success' => true,
            'referral_link' => $referralLink,
            'message' => 'Referral link generated successfully.'
        ]);
    }

    /**
     * Get referral statistics for authenticated staff member
     */
    public function getReferralStats()
    {
        $user = Auth::user();
        
        // Check if user is staff
        if (!$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Only nurses and caregivers can view referral statistics.'
            ], 403);
        }
        
        $stats = $this->referralService->getReferralStats($user);
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Get referral history for authenticated staff member
     */
    public function getReferralHistory(Request $request)
    {
        $user = Auth::user();
        
        // Check if user is staff
        if (!$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Only nurses and caregivers can view referral history.'
            ], 403);
        }
        
        $limit = $request->get('limit', 10);
        $referrals = $this->referralService->getReferralHistory($user, $limit);
        
        return response()->json([
            'success' => true,
            'referrals' => $referrals
        ]);
    }
}


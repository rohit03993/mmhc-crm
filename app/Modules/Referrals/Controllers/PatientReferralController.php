<?php

namespace App\Modules\Referrals\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Referrals\Services\PatientSubscriptionReferralService;
use Illuminate\Support\Facades\Auth;

class PatientReferralController extends Controller
{
    public function __construct(
        protected PatientSubscriptionReferralService $patientReferralService
    ) {
        $this->middleware(['auth', 'role:patient']);
    }

    public function index()
    {
        $user = Auth::user();
        $stats = $this->patientReferralService->getStats($user);
        $referrals = $this->patientReferralService->getReferralHistory($user, 10);
        $planReferralLink = $this->patientReferralService->getPlanReferralLink($user);

        return view('referrals::patient.index', compact(
            'user',
            'stats',
            'referrals',
            'planReferralLink'
        ));
    }
}

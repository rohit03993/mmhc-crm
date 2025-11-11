<?php

namespace App\Modules\Rewards\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Rewards\Models\CaregiverReward;
use App\Modules\Rewards\Services\RewardService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RewardController extends Controller
{
    public function __construct(protected RewardService $rewardService)
    {
    }

    /**
     * Display a listing of rewards for the authenticated caregiver/nurse.
     */
    public function index()
    {
        $user = Auth::user();

        $rewards = CaregiverReward::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        $rewardAmount = $this->rewardService->calculateRewardAmount($user->reward_points ?? 0);

        return view('rewards::rewards.index', [
            'rewards' => $rewards,
            'totalPoints' => $user->reward_points ?? 0,
            'totalAmount' => $rewardAmount,
        ]);
    }

    /**
     * Show form for creating new reward entry.
     */
    public function create()
    {
        Log::info('Rewards create route accessed', [
            'user_id' => Auth::id(),
            'role' => Auth::check() ? Auth::user()->role : null,
        ]);
        return view('rewards::rewards.create');
    }

    /**
     * Store a newly created reward entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'required|string|max:20',
            'hospital_name' => 'required|string|max:255',
            'treatment_details' => 'nullable|string|max:2000',
        ]);

        $this->rewardService->createReward(Auth::user(), $validated);

        return redirect()
            ->route('rewards.index')
            ->with('success', 'Patient details submitted successfully. 1 reward point has been credited.');
    }

    /**
     * Display a listing of rewards for admin overview.
     */
    public function adminIndex()
    {
        $rewards = CaregiverReward::with('user')
            ->latest()
            ->paginate(20);

        $totalPoints = CaregiverReward::sum('reward_points');
        $totalAmount = CaregiverReward::sum('reward_amount');

        return view('rewards::admin.index', [
            'rewards' => $rewards,
            'totalPoints' => $totalPoints,
            'totalAmount' => $totalAmount,
        ]);
    }
}


<?php

namespace App\Modules\Rewards\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Rewards\Models\CaregiverReward;
use App\Modules\Rewards\Services\RewardService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
        $rawPhone = preg_replace('/\D/', '', (string) $request->input('patient_phone', ''));
        $rawPincode = preg_replace('/\D/', '', (string) $request->input('patient_pincode', ''));

        $validator = Validator::make(
            [
                'patient_name' => $request->input('patient_name'),
                'patient_phone' => $request->input('patient_phone'),
                'patient_phone_digits' => $rawPhone,
                'patient_age' => $request->input('patient_age'),
                'patient_address' => $request->input('patient_address'),
                'patient_pincode' => $request->input('patient_pincode'),
                'patient_pincode_digits' => $rawPincode,
                'hospital_name' => $request->input('hospital_name'),
                'treatment_details' => $request->input('treatment_details'),
            ],
            [
                'patient_name' => 'required|string|max:255',
                'patient_phone_digits' => [
                    'required',
                    'regex:/^[0-9]{10}$/',
                    function (string $attribute, string $value, \Closure $fail) {
                        $normalized = '+91' . $value;
                        if (CaregiverReward::where('patient_phone', $normalized)->exists()) {
                            $fail('This mobile number has already been submitted.');
                        }
                    },
                ],
                'patient_age' => 'required|integer|min:1|max:150',
                'patient_address' => 'required|string|max:500',
                'patient_pincode_digits' => [
                    'required',
                    'regex:/^[1-9][0-9]{5}$/',
                ],
                'hospital_name' => 'required|string|max:255',
                'treatment_details' => 'nullable|string|max:2000',
            ],
            [
                'patient_phone_digits.regex' => 'Enter a valid 10-digit Indian mobile number.',
                'patient_age.required' => 'Patient age is required.',
                'patient_age.integer' => 'Age must be a valid number.',
                'patient_age.min' => 'Age must be at least 1 year.',
                'patient_age.max' => 'Age cannot exceed 150 years.',
                'patient_address.required' => 'Patient address is required.',
                'patient_pincode_digits.regex' => 'Enter a valid 6-digit Indian pincode (first digit must be 1-9).',
            ]
        );

        $validated = $validator->validate();

        $payload = [
            'patient_name' => $validated['patient_name'],
            'patient_phone' => '+91' . $validated['patient_phone_digits'],
            'patient_age' => (int) $validated['patient_age'],
            'patient_address' => $validated['patient_address'],
            'patient_pincode' => $validated['patient_pincode_digits'],
            'hospital_name' => $validated['hospital_name'],
            'treatment_details' => $validated['treatment_details'] ?? null,
        ];

        $this->rewardService->createReward(Auth::user(), $payload);

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


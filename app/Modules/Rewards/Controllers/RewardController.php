<?php

namespace App\Modules\Rewards\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Rewards\Models\CaregiverReward;
use App\Modules\Rewards\Services\RewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RewardController extends Controller
{
    public function __construct(protected RewardService $rewardService) {}

    /**
     * Display a listing of rewards for the authenticated caregiver/nurse.
     */
    public function index()
    {
        $user = Auth::user();

        $rewards = CaregiverReward::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        $verifiedPoints = (int) CaregiverReward::query()
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('verification_status', 'verified')->orWhereNull('verification_status');
            })
            ->sum('reward_points');
        $pendingVerificationCount = (int) CaregiverReward::query()
            ->where('user_id', $user->id)
            ->where('verification_status', 'pending')
            ->count();
        $rewardAmount = $this->rewardService->calculateRewardAmount($verifiedPoints);

        return view('rewards::rewards.index', [
            'rewards' => $rewards,
            'totalPoints' => $verifiedPoints,
            'totalAmount' => $rewardAmount,
            'pendingVerificationCount' => $pendingVerificationCount,
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
                'patient_email' => $request->input('patient_email'),
                'otp_channel' => $request->input('otp_channel', 'mobile'),
                'patient_age' => $request->input('patient_age'),
                'patient_address' => $request->input('patient_address'),
                'patient_pincode' => $request->input('patient_pincode'),
                'patient_pincode_digits' => $rawPincode,
                'hospital_name' => $request->input('hospital_name'),
                'treatment_details' => $request->input('treatment_details'),
            ],
            [
                'patient_name' => 'required|string|max:255',
                'otp_channel' => 'required|in:mobile,email',
                'patient_phone_digits' => [
                    'required',
                    'regex:/^[0-9]{10}$/',
                    function (string $attribute, string $value, \Closure $fail) {
                        $normalized = '+91'.$value;
                        if (CaregiverReward::where('patient_phone', $normalized)->exists()) {
                            $fail('This mobile number has already been submitted.');
                        }
                    },
                ],
                'patient_age' => 'required|integer|min:1|max:150',
                'patient_email' => 'nullable|email|max:255|required_if:otp_channel,email',
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
                'patient_email.required_if' => 'Patient email is required when OTP channel is Email.',
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
            'patient_phone' => '+91'.$validated['patient_phone_digits'],
            'patient_email' => $validated['patient_email'] ?? null,
            'patient_age' => (int) $validated['patient_age'],
            'patient_address' => $validated['patient_address'],
            'patient_pincode' => $validated['patient_pincode_digits'],
            'hospital_name' => $validated['hospital_name'],
            'treatment_details' => $validated['treatment_details'] ?? null,
        ];

        $reward = $this->rewardService->createPendingReward(Auth::user(), $payload);
        $sendOtp = $this->rewardService->sendVerificationOtp($reward, (string) $validated['otp_channel']);
        if (! ($sendOtp['success'] ?? false)) {
            return redirect()
                ->route('rewards.index')
                ->with('error', 'Patient details saved but OTP not sent: '.($sendOtp['message'] ?? 'unknown error'));
        }

        return redirect()
            ->route('rewards.index')
            ->with('success', 'Patient details submitted. OTP sent for verification. Reward credits only after OTP verification.');
    }

    public function sendOtp(CaregiverReward $reward)
    {
        if ((int) $reward->user_id !== (int) Auth::id()) {
            abort(403);
        }
        $user = Auth::user();
        if (! empty($user->contact_update_channel) && (! empty($user->pending_email) || ! empty($user->pending_phone))) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your pending profile contact verification first.',
            ], 422);
        }
        $channel = request()->input('otp_channel', 'mobile');
        $res = $this->rewardService->sendVerificationOtp($reward, (string) $channel);

        return response()->json($res, ($res['success'] ?? false) ? 200 : 422);
    }

    public function resendOtpFromBanner(Request $request, CaregiverReward $reward)
    {
        if ((int) $reward->user_id !== (int) Auth::id()) {
            abort(403);
        }
        $user = Auth::user();
        if (! empty($user->contact_update_channel) && (! empty($user->pending_email) || ! empty($user->pending_phone))) {
            return redirect()->back()
                ->with('error', 'Please complete your pending profile contact verification first.');
        }
        $request->validate([
            'otp_channel' => ['required', 'in:mobile,email'],
        ]);
        $res = $this->rewardService->sendVerificationOtp($reward, (string) $request->otp_channel);

        return redirect()->back()
            ->with(($res['success'] ?? false) ? 'success' : 'error', $res['message'] ?? 'Failed to send OTP.');
    }

    public function verifyOtp(Request $request, CaregiverReward $reward)
    {
        if ((int) $reward->user_id !== (int) Auth::id()) {
            abort(403);
        }
        $user = Auth::user();
        if (! empty($user->contact_update_channel) && (! empty($user->pending_email) || ! empty($user->pending_phone))) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your pending profile contact verification first.',
            ], 422);
        }
        $request->validate([
            'otp_code' => ['required', 'digits:6'],
        ]);
        $res = $this->rewardService->verifyRewardOtp($reward, (string) $request->otp_code);

        return response()->json($res, ($res['success'] ?? false) ? 200 : 422);
    }

    public function verifyOtpFromBanner(Request $request, CaregiverReward $reward)
    {
        if ((int) $reward->user_id !== (int) Auth::id()) {
            abort(403);
        }
        $user = Auth::user();
        if (! empty($user->contact_update_channel) && (! empty($user->pending_email) || ! empty($user->pending_phone))) {
            return redirect()->back()
                ->with('error', 'Please complete your pending profile contact verification first.');
        }
        $request->validate([
            'otp_code' => ['required', 'digits:6'],
        ]);
        $res = $this->rewardService->verifyRewardOtp($reward, (string) $request->otp_code);

        return redirect()->back()
            ->with(($res['success'] ?? false) ? 'success' : 'error', $res['message'] ?? 'OTP verification failed.');
    }

    /**
     * Display a listing of rewards for admin overview.
     */
    public function adminIndex()
    {
        $rewards = CaregiverReward::with('user')
            ->latest()
            ->paginate(10);

        $totalPoints = CaregiverReward::sum('reward_points');
        $totalAmount = CaregiverReward::sum('reward_amount');

        return view('rewards::admin.index', [
            'rewards' => $rewards,
            'totalPoints' => $totalPoints,
            'totalAmount' => $totalAmount,
        ]);
    }
}

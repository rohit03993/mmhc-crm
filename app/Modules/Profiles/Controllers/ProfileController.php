<?php

namespace App\Modules\Profiles\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Academics\Models\AcademicExam;
use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Services\StudentAcademicReportDataService;
use App\Modules\Incentives\Models\IncentiveLedger;
use App\Modules\Payments\Models\StaffPayment;
use App\Modules\Payments\Services\StaffPayoutService;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Auth\Services\UserService;
use App\Modules\Profiles\Services\ProfileService;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Rewards\Models\CaregiverReward;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Services\Services\StaffIncentiveDetailsDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    protected $profileService;

    protected UserService $userService;

    public function __construct(ProfileService $profileService, UserService $userService)
    {
        $this->profileService = $profileService;
        $this->userService = $userService;
    }

    /**
     * Active / recent subscription context for patient profile.
     */
    protected function getPatientSubscriptionSummary(User $user): ?array
    {
        if (! $user->isPatient()) {
            return null;
        }

        $active = Subscription::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->with('plan')
            ->orderByDesc('end_date')
            ->first();

        return [
            'active' => $active,
            'total_records' => (int) Subscription::where('user_id', $user->id)->count(),
        ];
    }

    /**
     * Document bucket counts for profile dashboard (aligned with upload types).
     */
    protected function getProfileDocumentCategoryCounts(User $user): array
    {
        if ($user->isPatient()) {
            return [
                'medical_group' => (int) $user->documents()->whereIn('document_type', ['medical_report', 'lab_report', 'past_medical_history'])->count(),
                'aadhaar' => (int) $user->documents()->where('document_type', 'aadhaar_card')->count(),
                'prescription' => (int) $user->documents()->where('document_type', 'prescription')->count(),
                'insurance' => (int) $user->documents()->where('document_type', 'insurance_card')->count(),
            ];
        }

        if ($user->isStaff()) {
            return [
                'certificate' => (int) $user->documents()->where('document_type', 'certificate')->count(),
                'id_proof' => (int) $user->documents()->where('document_type', 'id_proof')->count(),
                'medical_license' => (int) $user->documents()->where('document_type', 'medical_license')->count(),
                'insurance' => (int) $user->documents()->where('document_type', 'insurance')->count(),
            ];
        }

        return [
            'certificate' => 0,
            'id_proof' => 0,
            'medical_license' => 0,
            'insurance' => 0,
        ];
    }

    /**
     * Show user profile
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $profile = $this->profileService->getProfile($user);
            $subscriptionSummary = $this->getPatientSubscriptionSummary($user);
            $documentCategoryCounts = $this->getProfileDocumentCategoryCounts($user);

            return view('profiles::profile.index', compact(
                'user',
                'profile',
                'subscriptionSummary',
                'documentCategoryCounts'
            ));
        } catch (\Exception $e) {
            Log::error('Profile load failed', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);

            return redirect()->route('dashboard')
                ->with('error', 'Unable to load profile. Please try again.');
        }
    }

    /**
     * Show profile edit form
     */
    public function edit()
    {
        try {
            $user = Auth::user();
            $profile = $this->profileService->getProfile($user);
            $effectivePhone = (string) ($user->pending_phone ?: $user->phone ?? '');
            $phoneDigits = preg_replace('/\D+/', '', $effectivePhone);
            $phoneForInput = strlen($phoneDigits) >= 10 ? substr($phoneDigits, -10) : $phoneDigits;
            $emailForInput = $user->usesPlaceholderEmail()
                ? (string) old('email', '')
                : (string) ($user->pending_email ?: $user->email ?? '');
            $pendingContactTarget = null;
            if ($user->contact_update_channel === 'mobile' && ! empty($user->pending_phone)) {
                $pendingContactTarget = 'Mobile: '.$this->maskPhone((string) $user->pending_phone);
            }
            $latestContactOtpDestination = (string) ($user->contact_update_otp_sent_to ?? '');

            return view('profiles::profile.edit', compact(
                'user',
                'profile',
                'phoneForInput',
                'emailForInput',
                'pendingContactTarget',
                'latestContactOtpDestination'
            ));
        } catch (\Exception $e) {
            Log::error('Profile load failed (edit)', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);

            return redirect()->route('dashboard')
                ->with('error', 'Unable to load profile. Please try again.');
        }
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => ['required', new \App\Rules\IndianMobileTenDigits],
            'email' => [
                Rule::requiredIf(fn () => ! $user->usesPlaceholderEmail()),
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date|before:today',
            'bio' => 'nullable|string|max:1000',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'specialization' => 'nullable|string|max:255',
            'availability_status' => 'nullable|in:available,busy,unavailable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $profileData = $request->only([
            'name', 'address', 'date_of_birth', 'bio',
            'experience_years', 'specialization', 'availability_status',
        ]);
        $profileData['phone'] = (string) $user->phone;

        $this->profileService->updateProfile($user, $profileData);

        $requestedEmail = trim((string) $request->input('email'));
        $requestedPhone = (string) $request->input('phone');
        $normalizedRequestedPhone = $this->normalizeIndianPhone($requestedPhone);
        if (! $normalizedRequestedPhone) {
            return redirect()->back()->withErrors(['phone' => 'Enter a valid 10-digit Indian mobile number.'])->withInput();
        }

        $effectiveCurrentEmail = $user->usesPlaceholderEmail()
            ? ''
            : (string) ($user->pending_email ?: $user->email ?? '');
        $effectiveCurrentPhone = $this->normalizeIndianPhone((string) ($user->pending_phone ?: $user->phone ?? ''));
        $emailChanged = $requestedEmail !== '' && strcasecmp($requestedEmail, $effectiveCurrentEmail) !== 0;
        $phoneChanged = $effectiveCurrentPhone !== $normalizedRequestedPhone;
        if ($emailChanged && $phoneChanged) {
            return redirect()->back()
                ->withErrors(['email' => 'Please update either email or phone at one time.'])
                ->withInput();
        }

        if (! $emailChanged && ! $phoneChanged) {
            return redirect()->route('profile.index')->with('success', 'Profile updated successfully!');
        }

        if ($emailChanged && User::query()->where('id', '!=', $user->id)->where('email', $requestedEmail)->exists()) {
            return redirect()->back()->withErrors(['email' => 'This email is already in use.'])->withInput();
        }
        if ($phoneChanged && $this->userService->phoneAlreadyRegistered($normalizedRequestedPhone, $user->id)) {
            return redirect()->back()->withErrors(['phone' => 'This phone number is already in use.'])->withInput();
        }

        if ($emailChanged && ! $phoneChanged) {
            $user->forceFill([
                'email' => $requestedEmail,
                'pending_email' => null,
                'pending_phone' => null,
                'contact_update_channel' => null,
                'contact_update_otp_hash' => null,
                'contact_update_otp_expires_at' => null,
                'contact_update_otp_attempts' => 0,
                'contact_update_otp_sent_to' => null,
                'contact_update_otp_sent_at' => null,
                'contact_update_verified_at' => null,
            ])->save();

            return redirect()->route('profile.index')->with('success', 'Profile updated successfully.');
        }

        // Mobile-only OTP verification for phone number changes.
        $user->forceFill([
            'pending_email' => null,
            'pending_phone' => $normalizedRequestedPhone,
            'contact_update_channel' => 'mobile',
            'contact_update_otp_hash' => null,
            'contact_update_otp_expires_at' => null,
            'contact_update_otp_attempts' => 0,
            'contact_update_otp_sent_to' => null,
            'contact_update_otp_sent_at' => null,
            'contact_update_verified_at' => null,
            'phone_verified_at' => null,
            'phone_verified_source' => null,
        ])->save();

        // If referral verification is pending, clear old OTP destination so user resends to updated contact.
        Referral::query()
            ->where('referred_id', $user->id)
            ->where('status', 'pending')
            ->where('verification_status', 'pending')
            ->update([
                'verification_otp_hash' => null,
                'verification_otp_expires_at' => null,
                'verification_otp_attempts' => 0,
                'verification_otp_sent_to' => null,
                'verification_otp_sent_at' => null,
            ]);

        $send = $this->sendMobileContactUpdateOtp($normalizedRequestedPhone);
        if (! ($send['success'] ?? false)) {
            return redirect()->route('profile.edit')
                ->with('error', ($send['message'] ?? 'Could not send OTP.').' Pending mobile is saved. Please click Resend OTP.');
        }

        $otpPayload = (string) ($send['otp'] ?? '');
        $bindOtp = app(\App\Modules\Auth\Services\PhoneBindOtpService::class);
        if ($otpPayload !== '') {
            $bindOtp->storeOtp((int) $user->id, $normalizedRequestedPhone, $otpPayload);
        }

        $otpDigest = $otpPayload !== ''
            ? $bindOtp->buildOtpDigest((int) $user->id, $normalizedRequestedPhone, $otpPayload)
            : null;

        $user->forceFill([
            'contact_update_otp_hash' => $otpDigest,
            'contact_update_otp_expires_at' => now()->addMinutes(5),
            'contact_update_otp_attempts' => 0,
            'contact_update_otp_sent_to' => (string) ($send['sent_to'] ?? ''),
            'contact_update_otp_sent_at' => now(),
        ])->save();

        return redirect()->route('profile.edit')
            ->with('success', 'Profile details saved. OTP sent to your new mobile number for verification.');
    }

    public function verifyContactUpdateOtp(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'otp_code' => ['required', 'digits:6'],
        ]);

        if (! $user->contact_update_channel || $user->contact_update_channel !== 'mobile' || empty($user->pending_phone)) {
            return redirect()->back()->with('error', 'No pending mobile verification found.');
        }
        if (! $user->contact_update_otp_sent_at || ! $user->contact_update_otp_expires_at) {
            return redirect()->back()->with('error', 'OTP is not sent yet. Please click Resend OTP.');
        }
        if (now()->greaterThan($user->contact_update_otp_expires_at)) {
            return redirect()->back()->with('error', 'OTP expired. Please resend OTP.');
        }
        if ((int) $user->contact_update_otp_attempts >= 3) {
            return redirect()->back()->with('error', 'Maximum OTP attempts reached. Please resend OTP.');
        }

        $bindOtp = app(\App\Modules\Auth\Services\PhoneBindOtpService::class);
        $otpValid = $bindOtp->verifyAndConsumeWithDbFallback(
            (int) $user->id,
            (string) $user->pending_phone,
            (string) $request->otp_code,
            (string) ($user->contact_update_otp_hash ?? '')
        );

        if (! $otpValid) {
            $user->increment('contact_update_otp_attempts');
            $remaining = max(0, 3 - (int) $user->fresh()->contact_update_otp_attempts);

            return redirect()->back()->with('error', $remaining > 0 ? "Invalid OTP. {$remaining} attempt(s) left." : 'Invalid OTP. No attempts left.');
        }

        $updates = [
            'contact_update_verified_at' => now(),
            'contact_update_otp_hash' => null,
            'contact_update_otp_expires_at' => null,
            'contact_update_otp_attempts' => 0,
            'contact_update_otp_sent_to' => null,
            'contact_update_otp_sent_at' => null,
            'contact_update_channel' => null,
        ];
        if (! empty($user->pending_phone)) {
            $updates['phone'] = $user->pending_phone;
            $updates['phone_verified_at'] = now();
            $updates['phone_verified_source'] = 'profile';
        }
        $updates['pending_email'] = null;
        $updates['pending_phone'] = null;
        $user->forceFill($updates)->save();

        if ($user->isStaff()) {
            app(\App\Modules\Rewards\Services\RewardService::class)->syncStaffRewardPoints($user->fresh());
        }

        return redirect()->route('profile.index')->with('success', 'Contact updated and verified successfully.');
    }

    public function resendContactUpdateOtp()
    {
        $user = Auth::user();
        if ($user->contact_update_channel !== 'mobile' || empty($user->pending_phone)) {
            return redirect()->back()->with('error', 'No pending mobile update found.');
        }
        if ($user->contact_update_otp_sent_at && $user->contact_update_otp_sent_at->gt(now()->subMinutes(15))) {
            return redirect()->back()->with('error', 'Please wait 15 minutes before resending OTP.');
        }

        $destination = (string) $user->pending_phone;
        $send = $this->sendMobileContactUpdateOtp($destination);
        if (! ($send['success'] ?? false)) {
            return redirect()->back()->with('error', $send['message'] ?? 'Could not resend OTP.');
        }

        $otpPayload = (string) ($send['otp'] ?? '');
        $bindOtp = app(\App\Modules\Auth\Services\PhoneBindOtpService::class);
        if ($otpPayload !== '') {
            $bindOtp->storeOtp((int) $user->id, $destination, $otpPayload);
        }

        $otpDigest = $otpPayload !== ''
            ? $bindOtp->buildOtpDigest((int) $user->id, $destination, $otpPayload)
            : null;

        $user->forceFill([
            'contact_update_otp_hash' => $otpDigest,
            'contact_update_otp_expires_at' => now()->addMinutes(5),
            'contact_update_otp_attempts' => 0,
            'contact_update_otp_sent_to' => (string) ($send['sent_to'] ?? ''),
            'contact_update_otp_sent_at' => now(),
        ])->save();

        return redirect()->back()->with('success', 'OTP resent to pending mobile.');
    }

    private function sendMobileContactUpdateOtp(string $normalizedPhone): array
    {
        $otp = (string) random_int(100000, 999999);
        $send = app(\App\Modules\Auth\Services\SmsOtpService::class)->sendCustomOtp($normalizedPhone, $otp);
        if (! ($send['success'] ?? false)) {
            return ['success' => false, 'message' => $send['message'] ?? 'Could not send OTP to mobile.'];
        }

        return ['success' => true, 'otp' => $otp, 'sent_to' => 'Mobile: '.$this->maskPhone($normalizedPhone)];
    }

    private function normalizeIndianPhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (! $digits) {
            return null;
        }
        if (strlen($digits) === 10) {
            return '91'.$digits;
        }
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return $digits;
        }

        return null;
    }

    private function maskPhone(string $normalizedPhone): string
    {
        return str_repeat('*', max(0, strlen($normalizedPhone) - 4)).substr($normalizedPhone, -4);
    }

    /**
     * Upload profile avatar
     */
    public function uploadAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid image file. Please upload a valid image (max 2MB).',
            ]);
        }

        $user = Auth::user();
        $avatarPath = $this->profileService->uploadAvatar($user, $request->file('avatar'));

        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully!',
            'avatar_url' => Storage::url($avatarPath),
        ]);
    }

    /**
     * Admin: View all profiles
     */
    public function adminIndex()
    {
        $users = User::with('profile')->withCount('documents')->paginate(10);

        return view('profiles::admin.index', compact('users'));
    }

    /**
     * Admin: View specific user profile
     */
    public function adminView(Request $request, User $user)
    {
        try {
            $profile = $this->profileService->getProfile($user);
        } catch (\Exception $e) {
            Log::error('Admin profile view failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.profiles')
                ->with('error', 'Unable to load profile. Please try again.');
        }

        // Note: User has both a `documents` JSON column (cast) and a `documents()` relation — property access
        // resolves to the column. Use an explicit query for uploaded Document models.
        $studentAcademic = null;
        if ($user->role === 'student') {
            $studentAcademic = app(StudentAcademicReportDataService::class)->build($request, $user);
        }

        $profileDocumentsPaginator = null;
        if ($user->role !== 'student') {
            $profileDocumentsPaginator = $user->documents()
                ->orderByDesc('created_at')
                ->paginate(8, ['*'], 'prof_doc_page')
                ->withQueryString();
        }

        $profileStats = $this->buildAdminProfileStats($user);
        $academicAdminSummary = $this->buildAcademicAdminSummary($user);

        $incentiveDetailsData = $user->isStaff()
            ? app(StaffIncentiveDetailsDataService::class)->buildForStaff($user)
            : null;

        $staffPaymentPending = $user->isStaff()
            ? app(StaffPayoutService::class)->calculatePendingPayments($user)
            : null;
        $staffPaymentHistory = $user->isStaff()
            ? StaffPayment::query()
                ->where('staff_id', $user->id)
                ->latest('paid_at')
                ->latest('id')
                ->limit(20)
                ->get()
            : collect();

        return view('profiles::admin.view', compact(
            'user',
            'profile',
            'profileStats',
            'academicAdminSummary',
            'studentAcademic',
            'profileDocumentsPaginator',
            'incentiveDetailsData',
            'staffPaymentPending',
            'staffPaymentHistory'
        ));
    }

    /**
     * Summary metrics for admin profile & stats page (lightweight counts / sums).
     *
     * @return array{staff: ?array<string, mixed>, patient: ?array<string, mixed>}
     */
    /**
     * Inline academics snapshot for admin CRM profile (faculty / institution admin).
     *
     * @return array<string, mixed>|null
     */
    private function buildAcademicAdminSummary(User $user): ?array
    {
        if (! $user->hasAcademicRole() || $user->role === 'student') {
            return null;
        }

        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $instId = (int) $user->academic_institution_id;
            $batches = Batch::where('institution_id', $instId)->orderBy('name')->get();
            $subjects = Subject::query()
                ->whereHas('batch', fn ($q) => $q->where('institution_id', $instId))
                ->with('batch')
                ->orderBy('name')
                ->limit(50)
                ->get();

            return [
                'batches' => $batches,
                'subjects' => $subjects,
                'assignments_count' => Assignment::whereHas('topic.subject.batch', fn ($q) => $q->where('institution_id', $instId))->count(),
                'exams_count' => AcademicExam::where('institution_id', $instId)->count(),
            ];
        }

        if ($user->role === 'faculty') {
            $batches = $user->academicBatches()->with('institution')->orderBy('name')->get();
            $subjectIds = DB::table('academic_subject_faculty')->where('user_id', $user->id)->pluck('subject_id');
            $subjects = Subject::query()
                ->whereIn('id', $subjectIds)
                ->with('batch.institution')
                ->orderBy('name')
                ->get();
            $assignmentsCount = 0;
            $examsCount = 0;
            if ($subjects->isNotEmpty() && $user->academic_institution_id) {
                $sid = $subjects->pluck('id');
                $batchIds = $subjects->pluck('batch_id')->unique()->filter()->values()->all();
                $assignmentsCount = Assignment::whereHas('topic', fn ($q) => $q->whereIn('subject_id', $sid))->count();
                $examsCount = AcademicExam::query()
                    ->where('institution_id', $user->academic_institution_id)
                    ->where(function ($q) use ($sid, $user, $batchIds) {
                        $q->whereIn('subject_id', $sid)
                            ->orWhere('created_by', $user->id);
                        if ($batchIds !== []) {
                            $q->orWhereIn('batch_id', $batchIds);
                        }
                    })
                    ->count();
            }

            return [
                'batches' => $batches,
                'subjects' => $subjects,
                'assignments_count' => $assignmentsCount,
                'exams_count' => $examsCount,
            ];
        }

        return null;
    }

    private function buildAdminProfileStats(User $user): array
    {
        $out = ['staff' => null, 'patient' => null];

        if ($user->isStaff()) {
            $base = ServiceRequest::query()->where('assigned_staff_id', $user->id);
            $ledger = IncentiveLedger::query()->where('staff_id', $user->id);
            $ledgerTotal = (float) (clone $ledger)->sum('final_amount');
            $payoutService = app(StaffPayoutService::class);
            $heldEarnings = $payoutService->calculateHeldDueToUnverifiedMobile($user);
            $patientRewardsTotal = $user->hasVerifiedPhone()
                ? (float) CaregiverReward::query()
                    ->where('user_id', $user->id)
                    ->verified()
                    ->sum('reward_amount')
                : 0.0;
            $out['staff'] = [
                'services_total' => (clone $base)->count(),
                'services_completed' => (clone $base)->where('status', 'completed')->count(),
                'services_approved' => (clone $base)->whereNotNull('admin_approved_at')->count(),
                'referrals_completed' => Referral::query()
                    ->where('referrer_id', $user->id)
                    ->referralMobileOtpVerified()
                    ->where('status', 'completed')
                    ->count(),
                // Ledger only (services + subscription sale + referral ledger rows). Patient rewards are separate rows in caregiver_rewards.
                'incentive_total' => $ledgerTotal,
                'patient_rewards_total' => $patientRewardsTotal,
                'combined_earnings' => $ledgerTotal + $patientRewardsTotal,
                'held_earnings_total' => (float) ($heldEarnings['total'] ?? 0),
                'held_earnings' => $heldEarnings,
                'mobile_verified' => $user->hasVerifiedPhone(),
                'incentive_unsettled' => (float) (clone $ledger)->where('payment_settled', false)->sum('final_amount'),
                'incentive_service' => (float) IncentiveLedger::query()
                    ->where('staff_id', $user->id)
                    ->where('source_type', IncentiveLedger::SOURCE_SERVICE_REQUEST)
                    ->sum('final_amount'),
                'incentive_subscription' => (float) IncentiveLedger::query()
                    ->where('staff_id', $user->id)
                    ->where('source_type', IncentiveLedger::SOURCE_SUBSCRIPTION_SALE)
                    ->sum('final_amount'),
                'incentive_referral' => (float) IncentiveLedger::query()
                    ->where('staff_id', $user->id)
                    ->where('source_type', IncentiveLedger::SOURCE_REFERRAL)
                    ->sum('final_amount'),
                'subscription_sales_count' => Subscription::query()
                    ->where('referrer_id', $user->id)
                    ->count(),
            ];
        }

        if ($user->isPatient()) {
            $base = ServiceRequest::query()->where('patient_id', $user->id);
            $out['patient'] = [
                'services_total' => (clone $base)->count(),
                'services_open' => (clone $base)->whereIn('status', ['pending', 'pending_approval', 'assigned', 'in_progress'])->count(),
                'services_completed' => (clone $base)->where('status', 'completed')->count(),
                'subscriptions_total' => $user->subscriptions()->count(),
                'has_active_subscription' => $user->hasActiveSubscription(),
            ];
        }

        return $out;
    }
}

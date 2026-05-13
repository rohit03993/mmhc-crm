<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Institution;
use App\Modules\Auth\Services\UserService;
use App\Modules\Auth\Services\SmsOtpService;
use App\Modules\Referrals\Services\ReferralService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    protected $userService;

    protected $referralService;

    protected $smsOtpService;

    public function __construct(UserService $userService, ReferralService $referralService, SmsOtpService $smsOtpService)
    {
        $this->userService = $userService;
        $this->referralService = $referralService;
        $this->smsOtpService = $smsOtpService;
    }

    /**
     * Show unified login (split layout; same credentials for all roles).
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $achievementMedia = \App\Models\AchievementMedia::ordered()->get();

        return view('auth::login', compact('achievementMedia'));
    }

    /**
     * Legacy URL: send everyone to the single login page.
     */
    public function showAcademicsLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('auth.login', [], 302);
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            return redirect()->intended($this->defaultPostLoginUrl());
        }

        return redirect()->back()
            ->withErrors(['email' => 'Invalid credentials'])
            ->withInput();
    }

    /**
     * Send OTP to phone via SMS (Sent.dm) for phone login. Does not leak whether the number is registered.
     */
    public function sendLoginOtp(Request $request)
    {
        $phoneDigits = preg_replace('/\D/', '', (string) $request->input('phone', ''));
        $validator = Validator::make(
            ['phone' => $phoneDigits],
            ['phone' => ['required', 'regex:/^[6-9][0-9]{9}$/']],
            [
                'phone.required' => 'Enter a 10-digit mobile number.',
                'phone.regex' => 'Enter a valid 10-digit Indian mobile number.',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('login_tab', 'phone');
        }

        $normalizedPhone = $this->userService->normalizePhone($phoneDigits);
        $user = User::where(function ($query) use ($normalizedPhone, $phoneDigits) {
            $query->where('phone', $normalizedPhone)
                ->orWhere('phone', $phoneDigits);
        })
            ->where('is_active', true)
            ->first();

        if (! $user) {
            return redirect()->back()
                ->with('success_otp', 'If an account exists for this number, you will receive an SMS with a login code shortly.')
                ->withInput()
                ->with('login_tab', 'phone');
        }

        $result = $this->smsOtpService->sendOtp($normalizedPhone);

        if (! $result['success']) {
            $message = $result['message'];
            if (config('app.debug')) {
                $message .= ' Check storage/logs/laravel.log for details.';
            }

            return redirect()->back()
                ->withErrors(['phone' => $message])
                ->withInput()
                ->with('login_tab', 'phone');
        }

        return redirect()->back()
            ->with('otp_sent', true)
            ->with('otp_phone', $phoneDigits)
            ->with('success_otp', $result['message'])
            ->with('login_tab', 'phone');
    }

    /**
     * Verify OTP and log in (phone login).
     */
    public function verifyLoginOtp(Request $request)
    {
        $phoneDigits = preg_replace('/\D/', '', (string) $request->input('phone', ''));
        $validator = Validator::make(
            ['phone' => $phoneDigits, 'otp' => $request->input('otp')],
            [
                'phone' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
                'otp' => ['required', 'string', 'size:6'],
            ],
            [
                'phone.required' => 'Enter your mobile number.',
                'phone.regex' => 'Enter a valid 10-digit Indian mobile number.',
                'otp.required' => 'Enter the 6-digit OTP.',
                'otp.size' => 'OTP must be 6 digits.',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('login_tab', 'phone');
        }

        $normalizedPhone = $this->userService->normalizePhone($phoneDigits);

        if (! $this->smsOtpService->verifyOtp($normalizedPhone, $request->input('otp'))) {
            return redirect()->back()
                ->withErrors(['otp' => 'Invalid or expired OTP. Please request a new one.'])
                ->withInput()
                ->with('login_tab', 'phone')
                ->with('otp_phone', $phoneDigits);
        }

        $user = User::where(function ($query) use ($normalizedPhone, $phoneDigits) {
            $query->where('phone', $normalizedPhone)
                ->orWhere('phone', $phoneDigits);
        })
            ->where('is_active', true)
            ->first();
        if (! $user) {
            return redirect()->back()
                ->withErrors(['otp' => 'Account not found. Please use email login or register.'])
                ->with('login_tab', 'phone');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended($this->defaultPostLoginUrl());
    }

    /**
     * Default destination after email or phone login when no intended URL is stored.
     */
    protected function defaultPostLoginUrl(): string
    {
        $user = Auth::user();

        if ($user && $user->hasAcademicRole()) {
            return route('academics.dashboard');
        }

        return route('dashboard');
    }

    /**
     * Show registration form
     */
    public function showRegister(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        if ($request->boolean('academics')) {
            if (! Schema::hasTable('academic_institutions')) {
                return redirect()->route('auth.register')
                    ->with('error', 'Academics registration is not available on this installation.');
            }

            $institutions = Institution::query()->active()->orderBy('name')->get(['id', 'name', 'code']);
            $batches = Schema::hasTable('academic_batches')
                ? Batch::query()->where('is_active', true)
                    ->orderBy('institution_id')
                    ->orderBy('name')
                    ->get(['id', 'institution_id', 'name', 'academic_year'])
                : collect();

            return view('auth::register-academics', compact('institutions', 'batches'));
        }

        // Check if referral code is present
        $referralCode = $request->get('ref');
        $referral = null;
        $referrer = null;

        if ($referralCode) {
            $referral = $this->referralService->validateReferralCode($referralCode);
            if ($referral) {
                $referrer = $referral->referrer;
            }
        }

        // Nursing Warrior flow: only Nurse Warrior & Caregiver Warrior tabs, show badge
        $warrior = $request->has('warrior');
        // Patient-only flow: only Patient registration when coming from "I'm a Patient"
        $patientOnly = $request->get('role') === 'patient';

        return view('auth::register-tabbed', compact('referralCode', 'referrer', 'warrior', 'patientOnly'));
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        if ($request->input('registration_portal') === 'academics') {
            return $this->registerAcademicUser($request);
        }

        // Normalize phone number for validation
        $phoneDigits = preg_replace('/\D/', '', $request->input('phone', ''));
        $normalizedPhone = $this->userService->normalizePhone($phoneDigits);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{10}$/',
                function ($attribute, $value, $fail) use ($normalizedPhone) {
                    // Check uniqueness with normalized phone format
                    if (User::where('phone', $normalizedPhone)->exists()) {
                        $fail('This phone number is already registered.');
                    }
                },
            ],
            'pincode' => 'required|string|regex:/^[1-9][0-9]{5}$/',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:nurse,caregiver,patient',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'qualification' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:50',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'email.unique' => 'This email address is already registered.',
            'phone.regex' => 'Phone number must be exactly 10 digits.',
            'pincode.required' => 'Pincode is required.',
            'pincode.regex' => 'Pincode must be a valid 6-digit Indian pincode.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if referral code is provided (from query string or form input)
        $referralCode = $request->get('ref') ?? $request->input('ref');
        $isReferralRegistration = ! empty($referralCode);

        // If referral code is provided, user must register as nurse or caregiver
        if ($isReferralRegistration) {
            // Validate that referral code exists and is valid
            $referral = $this->referralService->validateReferralCode($referralCode);
            if (! $referral) {
                return redirect()->back()
                    ->withErrors(['ref' => 'Invalid or expired referral code.'])
                    ->withInput();
            }

            // Validate role
            $validator->after(function ($validator) use ($request) {
                if (! in_array($request->role, ['nurse', 'caregiver'])) {
                    $validator->errors()->add('role', 'You can only register as a nurse or caregiver using a referral link.');
                }
            });

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('referralCode', $referralCode);
            }
        }

        return DB::transaction(function () use ($request, $referralCode, $isReferralRegistration, $normalizedPhone) {
            $userData = $request->only(['name', 'email', 'password', 'role', 'date_of_birth', 'address', 'pincode']);

            // Normalize and store phone number
            $userData['phone'] = $normalizedPhone;

            // Store password (mutator will auto-encrypt plain_password)
            $userData['plain_password'] = $userData['password'];
            $userData['password'] = Hash::make($userData['password']);

            // Generate unique ID based on role
            $userData['unique_id'] = $this->userService->generateUniqueId($userData['role']);

            // Get pincode coordinates from pincode database
            $pincode = $request->input('pincode');
            $pincodeData = \App\Models\Pincode::findByPincode($pincode);
            if ($pincodeData) {
                $userData['pincode'] = $pincode;
                $latitude = $pincodeData->latitude ? (float) $pincodeData->latitude : null;
                $longitude = $pincodeData->longitude ? (float) $pincodeData->longitude : null;
                $userData['latitude'] = $latitude;
                $userData['longitude'] = $longitude;

                // Set spatial POINT column for optimized queries
                // Use sentinel POINT(0 0) if coordinates missing (required for NOT NULL constraint)
                if ($latitude && $longitude) {
                    $userData['location'] = \App\Modules\Auth\Services\LocationService::createSpatialPoint($latitude, $longitude);
                } else {
                    $userData['location'] = \DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");
                }
            } else {
                // Pincode exists but coordinates not found - still store pincode
                $userData['pincode'] = $pincode;
                $userData['latitude'] = null;
                $userData['longitude'] = null;
                // Use sentinel POINT(0 0) for missing coordinates (required for NOT NULL constraint)
                $userData['location'] = \DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");
            }

            $user = User::create($userData);

            // Handle staff-specific data (nurse or caregiver)
            if (in_array($userData['role'], ['nurse', 'caregiver'])) {
                // Store additional caregiver information
                $caregiverData = $request->only(['qualification', 'experience']);

                // Handle document uploads
                if ($request->hasFile('documents')) {
                    $documents = [];
                    foreach ($request->file('documents') as $file) {
                        $filename = time().'_'.$file->getClientOriginalName();
                        $file->storeAs('caregiver_documents/'.$user->id, $filename, 'public');
                        $documents[] = $filename;
                    }
                    $caregiverData['documents'] = $documents;
                }

                // Store staff data in user's meta field or create a separate profile
                $user->update([
                    'qualification' => $caregiverData['qualification'] ?? null,
                    'experience' => $caregiverData['experience'] ?? null,
                    'documents' => json_encode($caregiverData['documents'] ?? []),
                ]);
            }

            // Process referral if referral code is provided
            if ($isReferralRegistration && $referralCode) {
                $referralProcessed = $this->referralService->processReferral($referralCode, $user);
                if (! $referralProcessed) {
                    $request->session()->flash('warning', 'Referral could not be started. The referrer may need to verify their mobile in Profile first, or OTP could not be sent to your mobile. You can retry from the dashboard when ready.');
                } else {
                    $request->session()->flash('success', 'Referral detected. OTP sent to your mobile for referral verification.');
                }
            }

            Auth::login($user);

            $roleMessage = match ($userData['role']) {
                'nurse' => 'Nurse registration successful! Your documents are under review.',
                'caregiver' => 'Caregiver registration successful! Your documents are under review.',
                'patient' => 'Patient registration successful! Welcome to MMHC CRM.',
                default => 'Registration successful!'
            };

            // Nurse/Caregiver: show Nursing Warrior welcome page with badge
            if (in_array($userData['role'], ['nurse', 'caregiver'])) {
                $request->session()->put('nursing_warrior_just_registered', true);

                return redirect()->route('auth.welcome.nursing-warrior');
            }

            // Patient: go to dashboard with success message
            return redirect()->route('dashboard')
                ->with('success', $roleMessage);
        });
    }

    /**
     * Public self-registration for student / faculty at an existing institution.
     */
    private function registerAcademicUser(Request $request)
    {
        if (! Schema::hasTable('academic_institutions') || ! Schema::hasTable('academic_batches')) {
            return redirect()->route('auth.register')
                ->with('error', 'Academics registration is not available.');
        }

        $phoneDigits = preg_replace('/\D/', '', $request->input('phone', ''));
        $normalizedPhone = $this->userService->normalizePhone($phoneDigits);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{10}$/',
                function ($attribute, $value, $fail) use ($normalizedPhone) {
                    if (User::where('phone', $normalizedPhone)->exists()) {
                        $fail('This phone number is already registered.');
                    }
                },
            ],
            'pincode' => 'required|string|regex:/^[1-9][0-9]{5}$/',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:student,faculty',
            'academic_institution_id' => [
                'required',
                'integer',
                Rule::exists('academic_institutions', 'id'),
            ],
            'academic_batch_ids' => ['required', 'array', 'min:1'],
            'academic_batch_ids.*' => ['integer', Rule::exists('academic_batches', 'id')],
            'qualification' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
        ], [
            'email.unique' => 'This email address is already registered.',
            'phone.regex' => 'Phone number must be exactly 10 digits.',
            'pincode.required' => 'Pincode is required.',
            'pincode.regex' => 'Pincode must be a valid 6-digit Indian pincode.',
        ]);

        $validator->after(function (\Illuminate\Validation\Validator $v) use ($request) {
            $id = (int) $request->input('academic_institution_id');
            if ($id > 0 && ! Institution::query()->where('id', $id)->where('is_active', true)->exists()) {
                $v->errors()->add('academic_institution_id', 'This institute is not active or could not be found.');
            }
            $this->assertAcademicBatchesBelongToInstitution($v, $request);
            if ((string) $request->input('role') === 'faculty' && empty(trim((string) $request->input('qualification', '')))) {
                $v->errors()->add('qualification', 'Qualification is required for faculty registration.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        return DB::transaction(function () use ($request, $normalizedPhone) {
            $role = (string) $request->input('role');
            $userData = $request->only(['name', 'email', 'password', 'role', 'date_of_birth', 'address', 'pincode']);
            $userData['phone'] = $normalizedPhone;
            $userData['plain_password'] = $userData['password'];
            $userData['password'] = Hash::make($userData['password']);
            $userData['unique_id'] = $this->userService->generateUniqueId($role);
            $userData['academic_institution_id'] = (int) $request->input('academic_institution_id');
            $userData['email_verified_at'] = now();
            $userData['is_active'] = true;

            $pincode = $request->input('pincode');
            $pincodeData = \App\Models\Pincode::findByPincode($pincode);
            if ($pincodeData) {
                $userData['pincode'] = $pincode;
                $latitude = $pincodeData->latitude ? (float) $pincodeData->latitude : null;
                $longitude = $pincodeData->longitude ? (float) $pincodeData->longitude : null;
                $userData['latitude'] = $latitude;
                $userData['longitude'] = $longitude;
                if ($latitude && $longitude) {
                    $userData['location'] = \App\Modules\Auth\Services\LocationService::createSpatialPoint($latitude, $longitude);
                } else {
                    $userData['location'] = DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");
                }
            } else {
                $userData['pincode'] = $pincode;
                $userData['latitude'] = null;
                $userData['longitude'] = null;
                $userData['location'] = DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");
            }

            $user = User::create($userData);

            if ($role === 'faculty' && $request->filled('qualification')) {
                $user->update(['qualification' => $request->input('qualification')]);
            }

            $this->syncUserAcademicBatches($user, $role, $userData['academic_institution_id'], $request);

            Auth::login($user);

            return redirect()->route('academics.dashboard')
                ->with('success', 'Welcome! You are enrolled under your selected batch(es).');
        });
    }

    /**
     * Show Nursing Warrior welcome page (after nurse/caregiver registration)
     */
    public function showWelcomeNursingWarrior(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('auth.login');
        }
        $user = Auth::user();
        if (! in_array($user->role, ['nurse', 'caregiver'])) {
            return redirect()->route('dashboard');
        }
        if (! $request->session()->pull('nursing_warrior_just_registered', false)) {
            return redirect()->route('dashboard');
        }

        $pendingReferralOtp = \App\Modules\Referrals\Models\Referral::query()
            ->where('referred_id', $user->id)
            ->where('status', 'pending')
            ->where('verification_status', 'pending')
            ->latest('id')
            ->first();

        return view('auth::welcome-nursing-warrior', compact('pendingReferralOtp'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }

    /**
     * Manage users (Admin only)
     */
    public function manageUsers(Request $request)
    {
        $searchQuery = trim((string) $request->input('q', ''));
        $segment = (string) $request->query('segment', 'all');
        if (! in_array($segment, ['all', 'academics', 'healthcare'], true)) {
            $segment = 'all';
        }

        $users = User::query()
            ->when($segment === 'academics', fn ($q) => $q->whereIn('role', User::academicRoleSlugs())->with([
                'academicInstitution:id,name,code',
                'academicBatches:id,name,institution_id',
            ]))
            ->when($segment === 'healthcare', fn ($q) => $q->whereNotIn('role', User::academicRoleSlugs()))
            ->when($searchQuery !== '', fn ($query) => $this->applyAdminUserSearch($query, $searchQuery))
            ->when($segment === 'academics', fn ($q) => $q->orderByRaw("CASE role WHEN 'super_admin' THEN 0 WHEN 'institution_admin' THEN 1 WHEN 'faculty' THEN 2 WHEN 'student' THEN 3 ELSE 4 END"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $institutions = Schema::hasTable('academic_institutions')
            ? Institution::query()->orderBy('name')->get(['id', 'name', 'code'])
            : collect();

        $batches = Schema::hasTable('academic_batches')
            ? Batch::query()->orderBy('institution_id')->orderBy('name')->get(['id', 'institution_id', 'name', 'academic_year'])
            : collect();

        return view('auth::admin.users', compact('users', 'searchQuery', 'segment', 'institutions', 'batches'));
    }

    /**
     * Filter users by name, email, phone (digits normalized), or unique ID when query looks like an ID.
     */
    private function applyAdminUserSearch(Builder $query, string $searchQuery): void
    {
        $digits = preg_replace('/\D/', '', $searchQuery);
        if (strlen($digits) > 10 && str_starts_with($digits, '91')) {
            $digits = substr($digits, -10);
        }

        $like = '%'.$searchQuery.'%';
        $phoneLike = $digits !== '' ? '%'.$digits.'%' : null;

        $query->where(function ($sub) use ($searchQuery, $like, $phoneLike) {
            $sub->where('name', 'like', $like)
                ->orWhere('email', 'like', $like);

            if ($phoneLike !== null) {
                $sub->orWhere('phone', 'like', $phoneLike);
            }

            if (str_contains(strtolower($searchQuery), 'uid')) {
                $sub->orWhere('unique_id', 'like', $like);
            }
        });
    }

    /**
     * Store new user (Admin only)
     */
    public function storeUser(Request $request)
    {
        // Normalize phone number for validation
        $phoneDigits = preg_replace('/\D/', '', $request->input('phone', ''));
        $normalizedPhone = $this->userService->normalizePhone($phoneDigits);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{10}$/',
                function ($attribute, $value, $fail) use ($normalizedPhone) {
                    // Check uniqueness with normalized phone format
                    if (User::where('phone', $normalizedPhone)->exists()) {
                        $fail('This phone number is already registered.');
                    }
                },
            ],
            'pincode' => 'required|string|regex:/^[1-9][0-9]{5}$/',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,nurse,caregiver,patient,super_admin,institution_admin,faculty,student',
            'academic_institution_id' => [
                'nullable',
                'integer',
                Rule::exists('academic_institutions', 'id'),
                Rule::requiredIf(fn () => in_array((string) $request->input('role'), ['institution_admin', 'faculty', 'student'], true)),
            ],
            'address' => 'nullable|string|max:500',
            'academic_batch_ids' => ['nullable', 'array'],
            'academic_batch_ids.*' => ['integer', Rule::exists('academic_batches', 'id')],
        ], [
            'email.unique' => 'This email address is already registered.',
            'phone.regex' => 'Phone number must be exactly 10 digits.',
            'pincode.required' => 'Pincode is required.',
            'pincode.regex' => 'Pincode must be a valid 6-digit Indian pincode.',
        ]);

        $validator->after(function (\Illuminate\Validation\Validator $v) use ($request) {
            $this->assertAcademicBatchesBelongToInstitution($v, $request);
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator, 'createUser')
                ->withInput();
        }

        $userData = $request->only(['name', 'email', 'password', 'role', 'address', 'pincode']);

        // Normalize and store phone number
        $userData['phone'] = $normalizedPhone;

        // Store password (mutator will auto-encrypt plain_password)
        $userData['plain_password'] = $userData['password'];
        $userData['password'] = Hash::make($userData['password']);

        // Get pincode coordinates from pincode database
        $pincode = $request->input('pincode');
        $pincodeData = \App\Models\Pincode::findByPincode($pincode);
        if ($pincodeData) {
            $userData['pincode'] = $pincode;
            $latitude = $pincodeData->latitude ? (float) $pincodeData->latitude : null;
            $longitude = $pincodeData->longitude ? (float) $pincodeData->longitude : null;
            $userData['latitude'] = $latitude;
            $userData['longitude'] = $longitude;

            // Set spatial POINT column for optimized queries
            // Use sentinel POINT(0 0) if coordinates missing (required for NOT NULL constraint)
            if ($latitude && $longitude) {
                $userData['location'] = \App\Modules\Auth\Services\LocationService::createSpatialPoint($latitude, $longitude);
            } else {
                $userData['location'] = \DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");
            }
        } else {
            // Pincode exists but coordinates not found - still store pincode
            $userData['pincode'] = $pincode;
            $userData['latitude'] = null;
            $userData['longitude'] = null;
            // Use sentinel POINT(0 0) for missing coordinates (required for NOT NULL constraint)
            $userData['location'] = \DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");
        }

        $role = (string) $request->input('role');
        if (in_array($role, ['institution_admin', 'faculty', 'student'], true)) {
            $userData['academic_institution_id'] = (int) $request->input('academic_institution_id');
        } else {
            $userData['academic_institution_id'] = null;
        }

        $user = $this->userService->createUser($userData);

        $this->syncUserAcademicBatches($user, $role, $userData['academic_institution_id'] ?? null, $request);

        $listQuery = in_array($user->role, User::academicRoleSlugs(), true) ? ['segment' => 'academics'] : [];

        return redirect()->route('admin.users', $listQuery)
            ->with('success', "User '{$user->name}' created successfully with ID: {$user->unique_id}");
    }

    /**
     * View user details (Admin only)
     */
    public function viewUser(User $user)
    {
        // Extract 10-digit phone for display
        $phoneDisplay = $this->userService->extractPhoneDigits($user->phone);

        // Get decrypted password using accessor (admin only)
        $decryptedPassword = $user->decrypted_password;

        $user->loadMissing('academicBatches:id,name,institution_id');

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'unique_id' => $user->unique_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $phoneDisplay,
                'role' => $user->role,
                'address' => $user->address,
                'pincode' => $user->pincode,
                'date_of_birth' => $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : null,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at->format('M d, Y'),
                'plain_password' => $decryptedPassword, // Use decrypted password accessor
                'reward_points' => $user->reward_points ?? 0,
                'academic_institution_id' => $user->academic_institution_id,
                'academic_batch_ids' => $user->academicBatches->pluck('id')->values()->all(),
            ],
        ]);
    }

    /**
     * Show edit user form (Admin only)
     */
    public function editUser(User $user)
    {
        // Extract 10-digit phone for display
        $phoneDisplay = $this->userService->extractPhoneDigits($user->phone);

        // Get decrypted password using accessor (admin only)
        $decryptedPassword = $user->decrypted_password;

        $user->loadMissing('academicBatches:id,name,institution_id');

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'unique_id' => $user->unique_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $phoneDisplay,
                'role' => $user->role,
                'address' => $user->address,
                'pincode' => $user->pincode,
                'date_of_birth' => $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : null,
                'is_active' => $user->is_active,
                'plain_password' => $decryptedPassword, // Use decrypted password accessor
                'academic_institution_id' => $user->academic_institution_id,
                'academic_batch_ids' => $user->academicBatches->pluck('id')->values()->all(),
            ],
        ]);
    }

    /**
     * Update user (Admin only)
     */
    public function updateUser(Request $request, User $user)
    {
        // Normalize phone number for validation
        $phoneDigits = preg_replace('/\D/', '', $request->input('phone', ''));
        $normalizedPhone = $this->userService->normalizePhone($phoneDigits);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{10}$/',
                function ($attribute, $value, $fail) use ($normalizedPhone, $user) {
                    // Check uniqueness with normalized phone format (excluding current user)
                    if (User::where('phone', $normalizedPhone)->where('id', '!=', $user->id)->exists()) {
                        $fail('This phone number is already registered.');
                    }
                },
            ],
            'pincode' => 'required|string|regex:/^[1-9][0-9]{5}$/',
            'role' => 'required|in:admin,nurse,caregiver,patient,super_admin,institution_admin,faculty,student',
            'academic_institution_id' => [
                'nullable',
                'integer',
                Rule::exists('academic_institutions', 'id'),
                Rule::requiredIf(fn () => in_array((string) $request->input('role'), ['institution_admin', 'faculty', 'student'], true)),
            ],
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'password' => 'nullable|string|min:6|confirmed',
            'academic_batch_ids' => ['nullable', 'array'],
            'academic_batch_ids.*' => ['integer', Rule::exists('academic_batches', 'id')],
        ], [
            'email.unique' => 'This email address is already registered.',
            'phone.regex' => 'Phone number must be exactly 10 digits.',
            'pincode.required' => 'Pincode is required.',
            'pincode.regex' => 'Pincode must be a valid 6-digit Indian pincode.',
        ]);

        $validator->after(function (\Illuminate\Validation\Validator $v) use ($request) {
            $this->assertAcademicBatchesBelongToInstitution($v, $request);
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator, 'updateUser')
                ->withInput();
        }

        $updateData = $request->only(['name', 'email', 'role', 'address', 'date_of_birth', 'pincode']);

        // Normalize and store phone number
        $updateData['phone'] = $normalizedPhone;

        $newRole = (string) $request->input('role');
        if (in_array($newRole, ['institution_admin', 'faculty', 'student'], true)) {
            $updateData['academic_institution_id'] = (int) $request->input('academic_institution_id');
        } else {
            $updateData['academic_institution_id'] = null;
        }

        // Handle is_active field
        if ($request->has('is_active')) {
            $updateData['is_active'] = $request->is_active == '1' || $request->is_active === true || $request->is_active === 1;
        }

        // Update password if provided (mutator will auto-encrypt plain_password)
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
            $updateData['plain_password'] = $request->password;
        }

        // Get pincode coordinates from pincode database
        $pincode = $request->input('pincode');
        $pincodeData = \App\Models\Pincode::findByPincode($pincode);
        if ($pincodeData) {
            $updateData['pincode'] = $pincode;
            $latitude = $pincodeData->latitude ? (float) $pincodeData->latitude : null;
            $longitude = $pincodeData->longitude ? (float) $pincodeData->longitude : null;
            $updateData['latitude'] = $latitude;
            $updateData['longitude'] = $longitude;

            // Set spatial POINT column for optimized queries
            // Use sentinel POINT(0 0) if coordinates missing (required for NOT NULL constraint)
            if ($latitude && $longitude) {
                $updateData['location'] = \App\Modules\Auth\Services\LocationService::createSpatialPoint($latitude, $longitude);
            } else {
                $updateData['location'] = \DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");
            }
        } else {
            // Pincode exists but coordinates not found - still store pincode
            $updateData['pincode'] = $pincode;
            $updateData['latitude'] = null;
            $updateData['longitude'] = null;
            // Use sentinel POINT(0 0) for missing coordinates (required for NOT NULL constraint)
            $updateData['location'] = \DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");
        }

        $user->update($updateData);

        $user->refresh();
        $this->syncUserAcademicBatches(
            $user,
            (string) $user->role,
            $user->academic_institution_id ? (int) $user->academic_institution_id : null,
            $request
        );

        $listQuery = in_array($user->role, User::academicRoleSlugs(), true) ? ['segment' => 'academics'] : [];

        return redirect()->route('admin.users', $listQuery)
            ->with('success', "User '{$user->name}' updated successfully!");
    }

    /**
     * Ensure selected batches belong to the submitted institution (faculty / student only).
     */
    private function assertAcademicBatchesBelongToInstitution(\Illuminate\Validation\Validator $validator, Request $request): void
    {
        if (! Schema::hasTable('academic_batches')) {
            return;
        }
        $role = (string) $request->input('role');
        $instId = (int) $request->input('academic_institution_id');
        if (! in_array($role, ['faculty', 'student'], true) || $instId < 1) {
            return;
        }
        $ids = array_values(array_filter(array_map('intval', (array) $request->input('academic_batch_ids', []))));
        if ($ids === []) {
            return;
        }
        $invalid = Batch::query()->whereIn('id', $ids)->where('institution_id', '!=', $instId)->exists();
        if ($invalid) {
            $validator->errors()->add('academic_batch_ids', 'Each selected batch must belong to the chosen institution.');
        }
    }

    /**
     * Attach faculty/students to batches (pivot type matches role). Clears when role/institution not applicable.
     */
    private function syncUserAcademicBatches(User $user, string $role, ?int $institutionId, Request $request): void
    {
        if (! Schema::hasTable('academic_batch_users')) {
            return;
        }
        $institutionId = $institutionId ?: null;
        if (! in_array($role, ['faculty', 'student'], true) || ! $institutionId) {
            $user->academicBatches()->detach();

            return;
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $request->input('academic_batch_ids', [])))));
        if ($ids === []) {
            $user->academicBatches()->detach();

            return;
        }
        $validIds = Batch::query()
            ->where('institution_id', $institutionId)
            ->whereIn('id', $ids)
            ->pluck('id');
        $pivotType = $role === 'faculty' ? 'faculty' : 'student';
        $sync = [];
        foreach ($validIds as $bid) {
            $sync[$bid] = ['type' => $pivotType];
        }
        $user->academicBatches()->sync($sync);
    }

    /**
     * Toggle user active status (Admin only)
     */
    public function toggleUserStatus(User $user)
    {
        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.users')
            ->with('success', "User '{$user->name}' has been {$status} successfully!");
    }

    /**
     * Reset user password (Admin only)
     */
    public function resetPassword(User $user)
    {
        // Generate a random 8-character password
        $newPassword = Str::random(8);

        $user->update([
            'password' => Hash::make($newPassword),
            'plain_password' => $newPassword, // Mutator will auto-encrypt
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully!',
            'new_password' => $newPassword,
        ]);
    }

    /**
     * Delete all non-admin users (Admin only)
     */
    public function deleteAllNonAdminUsers()
    {
        $protected = User::protectedFromBulkUserDeletionRoleSlugs();
        $nonAdminCount = User::whereNotIn('role', $protected)->count();
        $protectedCount = User::whereIn('role', $protected)->count();

        if ($nonAdminCount === 0) {
            return redirect()->route('admin.users')
                ->with('error', 'No deletable users found (CRM and academic platform admins stay protected).');
        }

        $deleted = $this->userService->deleteAllNonAdminUsers();

        return redirect()->route('admin.users')
            ->with('success', "Deleted {$deleted} user(s). {$protectedCount} protected account(s) remain (admin + academic super admin).");
    }
}

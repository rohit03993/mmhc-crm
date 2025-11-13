<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Auth\Services\UserService;
use App\Modules\Referrals\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $userService;
    protected $referralService;

    public function __construct(UserService $userService, ReferralService $referralService)
    {
        $this->userService = $userService;
        $this->referralService = $referralService;
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth::login');
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
            
            return redirect()->intended(route('dashboard'));
        }

        return redirect()->back()
            ->withErrors(['email' => 'Invalid credentials'])
            ->withInput();
    }

    /**
     * Show registration form
     */
    public function showRegister(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
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

        return view('auth::register-tabbed', compact('referralCode', 'referrer'));
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|regex:/^[0-9]{10}$/|unique:users',
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
            'phone.unique' => 'This phone number is already registered.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if referral code is provided (from query string or form input)
        $referralCode = $request->get('ref') ?? $request->input('ref');
        $isReferralRegistration = !empty($referralCode);
        
        // If referral code is provided, user must register as nurse or caregiver
        if ($isReferralRegistration) {
            // Validate that referral code exists and is valid
            $referral = $this->referralService->validateReferralCode($referralCode);
            if (!$referral) {
                return redirect()->back()
                    ->withErrors(['ref' => 'Invalid or expired referral code.'])
                    ->withInput();
            }
            
            // Validate role
            $validator->after(function ($validator) use ($request) {
                if (!in_array($request->role, ['nurse', 'caregiver'])) {
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

        return DB::transaction(function () use ($request, $referralCode, $isReferralRegistration) {
            $userData = $request->only(['name', 'email', 'phone', 'password', 'role', 'date_of_birth', 'address']);
            $userData['password'] = Hash::make($userData['password']);
            
            // Generate unique ID based on role
            $userData['unique_id'] = $this->userService->generateUniqueId($userData['role']);

            $user = User::create($userData);

            // Handle staff-specific data (nurse or caregiver)
            if (in_array($userData['role'], ['nurse', 'caregiver'])) {
                // Store additional caregiver information
                $caregiverData = $request->only(['qualification', 'experience']);
                
                // Handle document uploads
                if ($request->hasFile('documents')) {
                    $documents = [];
                    foreach ($request->file('documents') as $file) {
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $file->storeAs('caregiver_documents/' . $user->id, $filename, 'public');
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
                if ($referralProcessed) {
                    // Referral processed successfully - reward points already awarded
                }
            }

            Auth::login($user);

            $roleMessage = match($userData['role']) {
                'nurse' => 'Nurse registration successful! Your documents are under review.',
                'caregiver' => 'Caregiver registration successful! Your documents are under review.',
                'patient' => 'Patient registration successful! Welcome to MMHC CRM.',
                default => 'Registration successful!'
            };

            // Add referral success message if applicable
            if ($isReferralRegistration && $referralCode) {
                $roleMessage .= ' Thank you for joining through referral!';
            }

            return redirect()->route('dashboard')
                ->with('success', $roleMessage);
        });
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
    public function manageUsers()
    {
        $users = User::paginate(15);
        
        return view('auth::admin.users', compact('users'));
    }

    /**
     * Store new user (Admin only)
     */
    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|regex:/^[0-9]{10}$/|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,nurse,caregiver,patient',
        ], [
            'email.unique' => 'This email address is already registered.',
            'phone.regex' => 'Phone number must be exactly 10 digits.',
            'phone.unique' => 'This phone number is already registered.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $userData = $request->only(['name', 'email', 'phone', 'password', 'role']);
        $userData['password'] = Hash::make($userData['password']);
        
        $user = $this->userService->createUser($userData);

        return redirect()->route('admin.users')
            ->with('success', "User '{$user->name}' created successfully with ID: {$user->unique_id}");
    }
}

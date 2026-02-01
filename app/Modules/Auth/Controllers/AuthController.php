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
     * Show Nursing Warrior welcome page (after nurse/caregiver registration)
     */
    public function showWelcomeNursingWarrior(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('auth.login');
        }
        $user = Auth::user();
        if (!in_array($user->role, ['nurse', 'caregiver'])) {
            return redirect()->route('dashboard');
        }
        if (!$request->session()->pull('nursing_warrior_just_registered', false)) {
            return redirect()->route('dashboard');
        }
        return view('auth::welcome-nursing-warrior');
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
            'role' => 'required|in:admin,nurse,caregiver,patient',
            'address' => 'nullable|string|max:500',
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
        
        $user = $this->userService->createUser($userData);

        return redirect()->route('admin.users')
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
            ]
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
            ]
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
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
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
            'role' => 'required|in:admin,nurse,caregiver,patient',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'password' => 'nullable|string|min:6|confirmed',
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

        $updateData = $request->only(['name', 'email', 'role', 'address', 'date_of_birth', 'pincode']);
        
        // Normalize and store phone number
        $updateData['phone'] = $normalizedPhone;
        
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

        return redirect()->route('admin.users')
            ->with('success', "User '{$user->name}' updated successfully!");
    }

    /**
     * Toggle user active status (Admin only)
     */
    public function toggleUserStatus(User $user)
    {
        $user->update([
            'is_active' => !$user->is_active
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
        $nonAdminCount = User::where('role', '!=', 'admin')->count();
        $adminCount = User::where('role', 'admin')->count();
        
        if ($nonAdminCount === 0) {
            return redirect()->route('admin.users')
                ->with('error', 'No non-admin users found to delete.');
        }
        
        $deleted = $this->userService->deleteAllNonAdminUsers();
        
        return redirect()->route('admin.users')
            ->with('success', "Successfully deleted {$deleted} non-admin user(s). {$adminCount} admin user(s) remain protected.");
    }
}

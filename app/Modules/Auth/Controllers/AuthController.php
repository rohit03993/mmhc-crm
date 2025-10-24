<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Auth\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth::register-tabbed');
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
            'role' => 'required|in:caregiver,patient',
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

        $userData = $request->only(['name', 'email', 'phone', 'password', 'role', 'date_of_birth', 'address']);
        $userData['password'] = Hash::make($userData['password']);
        
        // Generate unique ID based on role
        $userData['unique_id'] = $this->userService->generateUniqueId($userData['role']);

        $user = User::create($userData);

        // Handle caregiver-specific data
        if ($userData['role'] === 'caregiver') {
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
            
            // Store caregiver data in user's meta field or create a separate profile
            $user->update([
                'qualification' => $caregiverData['qualification'] ?? null,
                'experience' => $caregiverData['experience'] ?? null,
                'documents' => json_encode($caregiverData['documents'] ?? []),
            ]);
        }

        Auth::login($user);

        $roleMessage = $userData['role'] === 'caregiver' 
            ? 'Caregiver registration successful! Your documents are under review.' 
            : 'Patient registration successful! Welcome to MMHC CRM.';

        return redirect()->route('dashboard')
            ->with('success', $roleMessage);
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
            'role' => 'required|in:admin,caregiver,patient',
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

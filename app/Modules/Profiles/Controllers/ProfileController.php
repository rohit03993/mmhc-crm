<?php

namespace App\Modules\Profiles\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Profiles\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Show user profile
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $profile = $this->profileService->getProfile($user);
            
            return view('profiles::profile.index', compact('user', 'profile'));
        } catch (\Exception $e) {
            // Fallback if profile service fails
            $user = Auth::user();
            $profile = null;
            
            return view('profiles::profile.index', compact('user', 'profile'));
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
            
            return view('profiles::profile.edit', compact('user', 'profile'));
        } catch (\Exception $e) {
            // Fallback if profile service fails
            $user = Auth::user();
            $profile = null;
            
            return view('profiles::profile.edit', compact('user', 'profile'));
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
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
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
            'name', 'phone', 'address', 'date_of_birth', 'bio', 
            'experience_years', 'specialization', 'availability_status'
        ]);

        $this->profileService->updateProfile($user, $profileData);

        return redirect()->route('profile.index')
            ->with('success', 'Profile updated successfully!');
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
                'message' => 'Invalid image file. Please upload a valid image (max 2MB).'
            ]);
        }

        $user = Auth::user();
        $avatarPath = $this->profileService->uploadAvatar($user, $request->file('avatar'));

        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully!',
            'avatar_url' => Storage::url($avatarPath)
        ]);
    }

    /**
     * Admin: View all profiles
     */
    public function adminIndex()
    {
        $users = User::with('profile')->paginate(15);
        
        return view('profiles::admin.index', compact('users'));
    }

    /**
     * Admin: View specific user profile
     */
    public function adminView(User $user)
    {
        $profile = $this->profileService->getProfile($user);
        
        return view('profiles::admin.view', compact('user', 'profile'));
    }
}

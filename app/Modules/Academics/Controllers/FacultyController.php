<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Institution Admin: list and add faculty for their institution.
 * Faculty are then assigned to batches in Batches → Edit batch.
 */
class FacultyController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->role !== 'institution_admin' || !$user->academic_institution_id) {
            abort(403, 'Only institution admins can manage faculty.');
        }
        $faculty = User::where('role', 'faculty')
            ->where('academic_institution_id', $user->academic_institution_id)
            ->orderBy('name')
            ->paginate(20);
        return view('academics::faculty.index', compact('faculty'));
    }

    public function create()
    {
        $user = auth()->user();
        if ($user->role !== 'institution_admin' || !$user->academic_institution_id) {
            abort(403, 'Only institution admins can add faculty.');
        }
        return view('academics::faculty.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'institution_admin' || !$user->academic_institution_id) {
            abort(403, 'Only institution admins can add faculty.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'unique_id' => 'nullable|string|max:50',
        ]);
        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'faculty';
        $validated['academic_institution_id'] = $user->academic_institution_id;
        $validated['is_active'] = true;
        $validated['email_verified_at'] = now();

        $newUser = User::create($validated);
        if (\Schema::hasColumn('users', 'location')) {
            \DB::table('users')->where('id', $newUser->id)->update([
                'location' => \DB::raw("ST_GeomFromText('POINT(0 0)', 4326)"),
            ]);
        }
        return redirect()->route('academics.faculty.index')->with('success', 'Faculty added successfully. Assign them to batches via Batches → Edit batch.');
    }
}

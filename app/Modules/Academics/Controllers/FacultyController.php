<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Academics\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * List and add faculty. Institution admins scope to their college; platform admins may pick any institution.
 */
class FacultyController extends Controller
{
    protected function assertCanManageFaculty(): User
    {
        $user = auth()->user();
        if (! in_array($user->role, ['institution_admin', 'super_admin', 'admin'], true)) {
            abort(403, 'You cannot manage faculty.');
        }

        return $user;
    }

    public function index(Request $request)
    {
        $user = $this->assertCanManageFaculty();

        $query = User::query()->with('academicInstitution')->where('role', 'faculty');

        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $query->where('academic_institution_id', $user->academic_institution_id);
        } elseif (in_array($user->role, ['super_admin', 'admin'], true) && $request->filled('institution_id')) {
            $query->where('academic_institution_id', (int) $request->get('institution_id'));
        }

        $faculty = $query->orderBy('name')->paginate(10)->withQueryString();
        $institutions = in_array($user->role, ['super_admin', 'admin'], true)
            ? Institution::query()->active()->orderBy('name')->get()
            : collect();

        return view('academics::faculty.index', compact('faculty', 'institutions'));
    }

    public function create()
    {
        $user = $this->assertCanManageFaculty();
        $institutions = in_array($user->role, ['super_admin', 'admin'], true)
            ? Institution::query()->active()->orderBy('name')->get()
            : collect();

        return view('academics::faculty.create', compact('institutions'));
    }

    public function store(Request $request)
    {
        $user = $this->assertCanManageFaculty();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'unique_id' => 'nullable|string|max:50',
            'institution_id' => ['nullable', 'exists:academic_institutions,id'],
        ]);

        if ($user->role === 'institution_admin') {
            if (! $user->academic_institution_id) {
                abort(403, 'Your account is not linked to an institution.');
            }
            $institutionId = (int) $user->academic_institution_id;
        } else {
            if (empty($validated['institution_id'])) {
                return redirect()->back()->withInput()->withErrors(['institution_id' => 'Select an institution.']);
            }
            $institutionId = (int) $validated['institution_id'];
            $allowed = Institution::query()->active()->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (! in_array($institutionId, $allowed, true)) {
                abort(403, 'Invalid institution.');
            }
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'faculty';
        $validated['academic_institution_id'] = $institutionId;
        $validated['is_active'] = true;
        $validated['email_verified_at'] = now();
        unset($validated['institution_id']);

        $newUser = User::create($validated);
        if (\Schema::hasColumn('users', 'location')) {
            \DB::table('users')->where('id', $newUser->id)->update([
                'location' => \DB::raw("ST_GeomFromText('POINT(0 0)', 4326)"),
            ]);
        }

        return redirect()->route('academics.faculty.index')->with('success', 'Faculty added successfully. Assign them to batches via Batches → Edit batch.');
    }
}

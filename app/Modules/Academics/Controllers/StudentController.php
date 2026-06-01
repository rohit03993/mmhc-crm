<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\EnrollmentApplication;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function __construct(protected EnrollmentService $enrollmentService) {}

    protected function assertCanManageStudents(): User
    {
        $user = auth()->user();
        if ($user->role !== 'institution_admin') {
            abort(403, 'You cannot manage students.');
        }

        return $user;
    }

    public function index(Request $request)
    {
        $user = $this->assertCanManageStudents();

        $query = User::query()
            ->with(['academicInstitution', 'academicBatches'])
            ->where('role', 'student');

        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $query->where('academic_institution_id', $user->academic_institution_id);
        }

        $students = $query->orderBy('name')->paginate(15)->withQueryString();
        $institutions = collect();

        return view('academics::students.index', compact('students', 'institutions'));
    }

    public function create(Request $request)
    {
        $user = $this->assertCanManageStudents();
        $institutions = collect();

        $institutionId = $user->role === 'institution_admin'
            ? (int) $user->academic_institution_id
            : (int) ($request->query('institution_id') ?: old('institution_id', 0));

        $batches = $institutionId > 0
            ? Batch::query()->where('institution_id', $institutionId)->orderBy('name')->get()
            : collect();

        return view('academics::students.create', compact('institutions', 'batches', 'institutionId'));
    }

    public function store(Request $request)
    {
        $user = $this->assertCanManageStudents();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'unique_id' => 'nullable|string|max:50',
            'institution_id' => ['nullable', 'exists:academic_institutions,id'],
            'batch_ids' => ['required', 'array', 'min:1'],
            'batch_ids.*' => ['integer', 'exists:academic_batches,id'],
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
        $validated['role'] = 'student';
        $validated['academic_institution_id'] = $institutionId;
        $validated['academic_enrollment_status'] = EnrollmentApplication::STATUS_APPROVED;
        $validated['is_active'] = true;
        $validated['email_verified_at'] = now();
        unset($validated['institution_id'], $validated['batch_ids']);

        $newUser = User::create($validated);
        if (\Schema::hasColumn('users', 'location')) {
            \DB::table('users')->where('id', $newUser->id)->update([
                'location' => \DB::raw("ST_GeomFromText('POINT(0 0)', 4326)"),
            ]);
        }

        $this->enrollmentService->syncStudentBatches($newUser, $institutionId, $request->input('batch_ids', []));

        return redirect()->route('academics.students.index')
            ->with('success', 'Student added and assigned to batch(es). Assign subject faculty via Subjects and Batches.');
    }
}

<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Services\AcademicScoreService;
use App\Modules\Academics\Support\AcademicsAccess;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InstitutionController extends Controller
{
    public function index()
    {
        $institutions = Institution::orderBy('name')->paginate(10);

        return view('academics::institutions.index', compact('institutions'));
    }

    /**
     * Read-only overview: batches, counts, links to reports and people.
     */
    public function show(Institution $institution): View
    {
        $user = auth()->user();
        if (AcademicsAccess::isPlatformProvisioner($user)) {
            // read-only platform overview
        } elseif ($user->role === 'institution_admin' && (int) $user->academic_institution_id === (int) $institution->id) {
            // own college
        } elseif ($user->role === 'faculty' && (int) $user->academic_institution_id === (int) $institution->id) {
            // teaching staff
        } else {
            abort(403, 'You cannot view this institution.');
        }

        $pivot = 'academic_batch_users';
        $batchTable = 'academic_batches';

        $allBatches = Batch::query()
            ->where('institution_id', $institution->id)
            ->orderBy('name')
            ->get();
        foreach ($allBatches as $batch) {
            $batch->setAttribute('students_count', (int) DB::table($pivot)
                ->where('batch_id', $batch->id)
                ->where('type', 'student')
                ->count());
            $batch->setAttribute('faculty_count', (int) DB::table($pivot)
                ->where('batch_id', $batch->id)
                ->where('type', 'faculty')
                ->count());
        }

        $batchPage = max(1, (int) request()->get('batch_page', 1));
        $perBatchPage = 10;
        $batchesPaginator = new LengthAwarePaginator(
            $allBatches->forPage($batchPage, $perBatchPage)->values()->all(),
            $allBatches->count(),
            $perBatchPage,
            $batchPage,
            ['path' => request()->url(), 'pageName' => 'batch_page']
        );
        $batchesPaginator->withQueryString();

        $studentCount = (int) DB::table($pivot)
            ->join($batchTable, "{$batchTable}.id", '=', "{$pivot}.batch_id")
            ->where("{$batchTable}.institution_id", $institution->id)
            ->where("{$pivot}.type", 'student')
            ->selectRaw('COUNT(DISTINCT '.$pivot.'.user_id) as c')
            ->value('c');

        // Faculty linked via batch pivot (same source as batch table rows); do not rely on users.academic_institution_id alone.
        $facultyCount = (int) DB::table($pivot)
            ->join($batchTable, "{$batchTable}.id", '=', "{$pivot}.batch_id")
            ->where("{$batchTable}.institution_id", $institution->id)
            ->where("{$pivot}.type", 'faculty')
            ->selectRaw('COUNT(DISTINCT '.$pivot.'.user_id) as c')
            ->value('c');

        $icr = AcademicScoreService::getIcr($institution);

        // People must match batch membership: pivot is the source of truth for who teaches/studies here.
        $peopleUserIds = DB::table($pivot)
            ->join($batchTable, "{$batchTable}.id", '=', "{$pivot}.batch_id")
            ->where("{$batchTable}.institution_id", $institution->id)
            ->whereIn("{$pivot}.type", ['student', 'faculty'])
            ->distinct()
            ->pluck("{$pivot}.user_id");

        $adminIds = User::query()
            ->where('academic_institution_id', $institution->id)
            ->where('role', 'institution_admin')
            ->pluck('id');

        $peopleIds = $peopleUserIds->merge($adminIds)->unique()->values();

        $peopleQuery = User::query()
            ->whereIn('role', ['student', 'faculty', 'institution_admin']);
        if ($peopleIds->isEmpty()) {
            $peopleQuery->whereRaw('0 = 1');
        } else {
            $peopleQuery->whereIn('id', $peopleIds);
        }

        $peoplePaginator = $peopleQuery
            ->orderByDesc('id')
            ->paginate(12, ['id', 'name', 'email', 'role', 'unique_id'], 'people_page')
            ->withQueryString();

        return view('academics::institutions.show', compact(
            'institution',
            'studentCount',
            'facultyCount',
            'icr',
            'batchesPaginator',
            'peoplePaginator',
        ));
    }

    public function create()
    {
        return view('academics::institutions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:academic_institutions,code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        Institution::create($validated);

        return redirect()->route('academics.institutions.index')
            ->with('success', 'Institution created successfully.');
    }

    public function edit(Institution $institution)
    {
        return view('academics::institutions.edit', compact('institution'));
    }

    public function update(Request $request, Institution $institution)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:academic_institutions,code,'.$institution->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        $institution->update($validated);

        return redirect()->route('academics.institutions.index')
            ->with('success', 'Institution updated successfully.');
    }

    public function destroy(Institution $institution)
    {
        $institution->delete();

        return redirect()->route('academics.institutions.index')
            ->with('success', 'Institution deleted successfully.');
    }
}

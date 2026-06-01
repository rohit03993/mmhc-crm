<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\EnrollmentApplication;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Services\EnrollmentService;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function __construct(protected EnrollmentService $enrollmentService) {}

    protected function assertCanReview(): User
    {
        $user = auth()->user();
        if ($user->role !== 'institution_admin') {
            abort(403, 'You cannot review enrollments.');
        }

        return $user;
    }

    public function index(Request $request)
    {
        $user = $this->assertCanReview();

        $query = EnrollmentApplication::query()
            ->with(['user', 'institution'])
            ->where('status', EnrollmentApplication::STATUS_PENDING)
            ->orderByDesc('created_at');

        if ($user->role === 'institution_admin') {
            if (! $user->academic_institution_id) {
                abort(403, 'Your account is not linked to an institution.');
            }
            $query->where('institution_id', $user->academic_institution_id);
        }

        $applications = $query->paginate(15)->withQueryString();
        $institutions = collect();

        return view('academics::enrollments.index', compact('applications', 'institutions'));
    }

    public function show(EnrollmentApplication $application)
    {
        $user = $this->assertCanReview();
        $this->authorizeApplication($user, $application);

        $application->load(['user', 'institution']);
        $batches = Batch::query()
            ->where('institution_id', $application->institution_id)
            ->orderBy('name')
            ->get();

        return view('academics::enrollments.show', compact('application', 'batches'));
    }

    public function approve(Request $request, EnrollmentApplication $application)
    {
        $user = $this->assertCanReview();
        $this->authorizeApplication($user, $application);

        if (! $application->isPending()) {
            return redirect()->route('academics.enrollments.index')->with('error', 'This application has already been reviewed.');
        }

        $validated = $request->validate([
            'batch_ids' => ['required', 'array', 'min:1'],
            'batch_ids.*' => ['integer', 'exists:academic_batches,id'],
            'reviewer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->enrollmentService->approve(
            $application,
            $user,
            $validated['batch_ids'],
            $validated['reviewer_notes'] ?? null
        );

        return redirect()->route('academics.enrollments.index')
            ->with('success', 'Student enrollment approved and batch(es) assigned.');
    }

    public function reject(Request $request, EnrollmentApplication $application)
    {
        $user = $this->assertCanReview();
        $this->authorizeApplication($user, $application);

        if (! $application->isPending()) {
            return redirect()->route('academics.enrollments.index')->with('error', 'This application has already been reviewed.');
        }

        $validated = $request->validate([
            'reviewer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->enrollmentService->reject($application, $user, $validated['reviewer_notes'] ?? null);

        return redirect()->route('academics.enrollments.index')
            ->with('success', 'Enrollment request rejected.');
    }

    protected function authorizeApplication(User $viewer, EnrollmentApplication $application): void
    {
        if ($viewer->role === 'institution_admin') {
            if ((int) $viewer->academic_institution_id !== (int) $application->institution_id) {
                abort(403, 'You can only review applications for your institution.');
            }
        }
    }
}

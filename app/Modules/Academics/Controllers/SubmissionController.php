<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Submission;
use App\Modules\Academics\Services\ChecklistScoreService;
use App\Modules\Academics\Services\ExamAccessService;
use App\Modules\Academics\Models\SubmissionMentorShare;
use App\Modules\Academics\Models\Mentorship;
use App\Modules\Academics\Services\MentorVerificationService;
use App\Modules\Academics\Services\MentorshipService;
use App\Modules\Academics\Services\TopicCompletionService;
use App\Modules\Academics\Services\AcademicScoreService;
use App\Modules\Academics\Support\StudentAssignmentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    /** Assignments the current student is eligible for (in batch for that subject). */
    protected function myAssignmentsQuery()
    {
        $userId = auth()->id();

        return Assignment::with([
            'topic.subject.batch',
            'submissions' => fn ($q) => $q->where('user_id', $userId),
            'exams' => fn ($q) => $q->orderByDesc('id'),
        ])
            ->whereHas('topic.subject.batch.students', fn ($q) => $q->where('users.id', $userId));
    }

    public function index(ExamAccessService $examAccess)
    {
        $assignments = $this->myAssignmentsQuery()
            ->orderBy('due_date')
            ->orderBy('title')
            ->paginate(10);

        $spiBreakdown = AcademicScoreService::getSpiBreakdown(auth()->user());
        $mentorVerification = app(MentorVerificationService::class);

        return view('academics::submissions.my-assignments', compact('assignments', 'examAccess', 'spiBreakdown', 'mentorVerification'));
    }

    public function show(Assignment $assignment, ExamAccessService $examAccess, MentorVerificationService $mentorVerification)
    {
        $eligibleIds = $assignment->eligibleStudentIds();
        if (! in_array(auth()->id(), $eligibleIds)) {
            abort(403, 'You are not eligible to view this assignment.');
        }

        $assignment->load([
            'topic.subject.batch',
            'topic.resources',
            'exams' => fn ($q) => $q->orderByDesc('id'),
        ]);

        $submission = Submission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', auth()->id())
            ->first();

        $status = StudentAssignmentStatus::for($assignment, $submission, $mentorVerification);
        $spiBreakdown = AcademicScoreService::getSpiBreakdown(auth()->user());

        return view('academics::submissions.assignment-detail', compact(
            'assignment',
            'submission',
            'examAccess',
            'status',
            'spiBreakdown',
            'mentorVerification'
        ));
    }

    public function create(Assignment $assignment, ExamAccessService $examAccess, MentorshipService $mentorshipService)
    {
        $eligibleIds = $assignment->eligibleStudentIds();
        if (! in_array(auth()->id(), $eligibleIds)) {
            abort(403, 'You are not eligible to submit this assignment.');
        }
        $assignment->load([
            'topic.subject.batch',
            'topic.resources',
            'exams' => fn ($q) => $q->orderByDesc('id'),
        ]);
        $existing = Submission::where('assignment_id', $assignment->id)->where('user_id', auth()->id())->first();

        $activeMentors = collect();
        $sharedMentorIds = [];
        if (auth()->user()->role === 'student') {
            $mentorIds = $mentorshipService->activeMentorIdsFor(auth()->user());
            if ($mentorIds !== []) {
                $activeMentors = \App\Models\Core\User::query()
                    ->with('academicInstitution')
                    ->whereIn('id', $mentorIds)
                    ->orderBy('name')
                    ->get();
            }
            if ($existing) {
                $sharedMentorIds = SubmissionMentorShare::query()
                    ->where('submission_id', $existing->id)
                    ->pluck('mentor_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }
        }

        return view('academics::submissions.submit', compact('assignment', 'existing', 'examAccess', 'activeMentors', 'sharedMentorIds'));
    }

    public function store(Request $request, Assignment $assignment, ChecklistScoreService $checklistScore, MentorshipService $mentorshipService, MentorVerificationService $mentorVerification)
    {
        $eligibleIds = $assignment->eligibleStudentIds();
        if (! in_array(auth()->id(), $eligibleIds)) {
            abort(403, 'You are not eligible to submit this assignment.');
        }
        $needsChecklist = $assignment->studentMustCompleteChecklist();
        $fileRules = $assignment->assignment_type === Assignment::TYPE_FILE_UPLOAD
            ? ['required', 'file', 'max:10240']
            : ['nullable', 'file', 'max:10240'];
        $rules = [
            'file' => $fileRules,
            'notes' => 'nullable|string|max:1000',
        ];
        if ($needsChecklist) {
            $rules['checklist'] = ['nullable', 'array'];
        }
        if (auth()->user()->role === 'student') {
            $rules['mentor_ids'] = ['nullable', 'array'];
            $rules['mentor_ids.*'] = ['integer', 'exists:users,id'];
        }
        $request->validate($rules);
        $file = $request->file('file');
        $existing = Submission::where('assignment_id', $assignment->id)->where('user_id', auth()->id())->first();
        $dir = 'academic/submissions/'.$assignment->id;
        $path = $existing?->file_path;
        $originalName = $existing?->original_name;
        if ($file && $file->isValid()) {
            if ($existing && $existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
                Storage::disk('public')->delete($existing->file_path);
            }
            $path = $file->storeAs($dir, auth()->id().'_'.time().'_'.$file->getClientOriginalName(), 'public');
            $originalName = $file->getClientOriginalName();
        }
        if ($assignment->assignment_type === Assignment::TYPE_FILE_UPLOAD && ! $path) {
            return redirect()->back()->withInput()->withErrors(['file' => 'A file is required for this assignment type.']);
        }
        $checklistAnswers = null;
        $earned = null;
        $possible = null;
        if ($needsChecklist) {
            $scored = $checklistScore->score($assignment, $request->input('checklist', []));
            $checklistAnswers = $scored['normalized_answers'];
            $earned = $scored['earned'];
            $possible = $scored['possible'];
        }
        $payload = [
            'file_path' => $path,
            'original_name' => $originalName,
            'submitted_at' => now(),
            'notes' => $request->input('notes'),
        ];
        if ($needsChecklist) {
            $payload['checklist_answers'] = $checklistAnswers;
            $payload['checklist_points_earned'] = $earned;
            $payload['checklist_points_possible'] = $possible;
        }
        if ($existing) {
            $existing->update($payload);
            $submission = $existing;
        } else {
            $submission = Submission::create(array_merge($payload, [
                'assignment_id' => $assignment->id,
                'user_id' => auth()->id(),
            ]));
        }

        if (auth()->user()->role === 'student') {
            $allowedMentorIds = $mentorshipService->activeMentorIdsFor(auth()->user());
            $selected = array_values(array_intersect(
                array_map('intval', (array) $request->input('mentor_ids', [])),
                $allowedMentorIds
            ));
            SubmissionMentorShare::query()->where('submission_id', $submission->id)->delete();
            foreach ($selected as $mentorId) {
                $mentorshipId = Mentorship::query()
                    ->where('mentee_id', auth()->id())
                    ->where('mentor_id', $mentorId)
                    ->where('status', Mentorship::STATUS_ACTIVE)
                    ->value('id');
                SubmissionMentorShare::create([
                    'submission_id' => $submission->id,
                    'mentor_id' => $mentorId,
                    'mentorship_id' => $mentorshipId,
                ]);
            }
            $mentorVerification->syncVerificationTimestamp($submission->fresh());
        }

        TopicCompletionService::checkAndCompleteTopic($assignment->fresh());

        return redirect()->route('academics.my-assignments.show', $assignment)->with('success', 'Submission saved successfully.');
    }

    public function download(Submission $submission)
    {
        $user = auth()->user();
        if ($user->id === $submission->user_id) {
            // Student downloading own file
        } elseif (in_array($user->role, ['institution_admin', 'faculty'], true)) {
            $this->authorizeFacultyOrAdminForAssignment($submission->assignment_id);
        } else {
            abort(403);
        }
        if (! $submission->file_path || ! Storage::disk('public')->exists($submission->file_path)) {
            abort(404, 'No file on this submission (notes-only or quiz completion).');
        }

        return response()->download(
            Storage::disk('public')->path($submission->file_path),
            $submission->original_name ?? 'submission'
        );
    }

    /** Submissions list for an assignment (faculty/admin). */
    public function forAssignment(Assignment $assignment)
    {
        $this->authorizeFacultyOrAdminForAssignment($assignment->id);
        $assignment->load('topic.subject.batch');
        $eligibleIds = $assignment->eligibleStudentIds();
        $submissions = Submission::with('user')->where('assignment_id', $assignment->id)->get()->keyBy('user_id');
        $students = \App\Models\Core\User::whereIn('id', $eligibleIds)->orderBy('name')->get();

        return view('academics::submissions.for-assignment', compact('assignment', 'submissions', 'students'));
    }

    protected function authorizeFacultyOrAdminForAssignment(int $assignmentId): void
    {
        $assignment = Assignment::with('topic.subject')->findOrFail($assignmentId);
        $user = auth()->user();
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            if ((int) $assignment->topic->subject->batch->institution_id !== (int) $user->academic_institution_id) {
                abort(403);
            }

            return;
        }
        if ($user->role === 'faculty') {
            $isFaculty = $assignment->topic->subject->faculty()->where('user_id', $user->id)->exists();
            if (! $isFaculty) {
                abort(403);
            }

            return;
        }
        abort(403);
    }
}

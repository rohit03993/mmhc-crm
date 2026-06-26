<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Academics\Models\OpenClassroom;
use App\Modules\Academics\Models\OpenClassroomAssignment;
use App\Modules\Academics\Models\OpenClassroomSubmission;
use App\Modules\Academics\Services\OpenClassroomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OpenClassroomAssignmentController extends Controller
{
    public function __construct(protected OpenClassroomService $classroomService) {}

    public function store(Request $request, OpenClassroom $openClassroom)
    {
        $this->authorizeManage($openClassroom);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'due_date' => 'nullable|date',
            'checklist_items_raw' => 'nullable|string|max:10000',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $checklist = $this->classroomService->parseChecklistFromRaw($request->input('checklist_items_raw'));
        $assignment = OpenClassroomAssignment::create([
            'open_classroom_id' => $openClassroom->id,
            'created_by' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'checklist_items' => $checklist,
            'is_published' => true,
        ]);

        $this->storeAttachments($assignment, $request->file('attachments'));

        return back()->with('success', 'Assignment published to your classroom.');
    }

    public function show(OpenClassroom $openClassroom, OpenClassroomAssignment $assignment)
    {
        if ((int) $assignment->open_classroom_id !== (int) $openClassroom->id) {
            abort(404);
        }

        $user = auth()->user();
        $isOwner = $this->classroomService->canManage($user, $openClassroom);
        $isMember = $this->classroomService->isMember($user, $openClassroom);

        if (! $isOwner && ! $isMember) {
            abort(403, 'Join this classroom to view assignments.');
        }

        $submission = OpenClassroomSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->first();

        return view('academics::open-classrooms.assignment-show', compact(
            'openClassroom',
            'assignment',
            'submission',
            'isOwner',
            'isMember'
        ));
    }

    public function submissions(OpenClassroom $openClassroom, OpenClassroomAssignment $assignment)
    {
        $this->authorizeManage($openClassroom);
        if ((int) $assignment->open_classroom_id !== (int) $openClassroom->id) {
            abort(404);
        }

        $eligibleIds = $assignment->eligibleMemberIds();
        $submissions = OpenClassroomSubmission::with('user')
            ->where('assignment_id', $assignment->id)
            ->get()
            ->keyBy('user_id');
        $members = User::whereIn('id', $eligibleIds)->orderBy('name')->get();

        return view('academics::open-classrooms.assignment-submissions', compact(
            'openClassroom',
            'assignment',
            'submissions',
            'members'
        ));
    }

    public function submitForm(OpenClassroom $openClassroom, OpenClassroomAssignment $assignment)
    {
        if ((int) $assignment->open_classroom_id !== (int) $openClassroom->id) {
            abort(404);
        }

        $user = auth()->user();
        if (! $this->classroomService->isMember($user, $openClassroom)) {
            abort(403, 'Join this classroom before submitting.');
        }

        $existing = OpenClassroomSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->first();

        return view('academics::open-classrooms.assignment-submit', compact(
            'openClassroom',
            'assignment',
            'existing'
        ));
    }

    public function submitStore(Request $request, OpenClassroom $openClassroom, OpenClassroomAssignment $assignment)
    {
        if ((int) $assignment->open_classroom_id !== (int) $openClassroom->id) {
            abort(404);
        }

        $user = auth()->user();
        if (! $this->classroomService->isMember($user, $openClassroom)) {
            abort(403);
        }

        $request->validate([
            'file' => 'nullable|file|max:10240',
            'notes' => 'nullable|string|max:1000',
            'checklist' => 'nullable|array',
        ]);

        $existing = OpenClassroomSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->first();

        $file = $request->file('file');
        $path = $existing?->file_path;
        $originalName = $existing?->original_name;

        if ($file && $file->isValid()) {
            if ($existing?->file_path && Storage::disk('public')->exists($existing->file_path)) {
                Storage::disk('public')->delete($existing->file_path);
            }
            $path = $file->storeAs(
                'academic/open-classrooms/'.$openClassroom->id.'/submissions',
                $user->id.'_'.time().'_'.$file->getClientOriginalName(),
                'public'
            );
            $originalName = $file->getClientOriginalName();
        }

        $checklistAnswers = null;
        $earned = null;
        $possible = null;
        if ($assignment->hasChecklist()) {
            $items = $assignment->normalizedChecklistItems();
            $possible = array_sum(array_map(fn ($i) => $i['points'], $items));
            $earned = 0.0;
            $normalized = [];
            $answers = (array) $request->input('checklist', []);
            foreach ($items as $idx => $item) {
                $key = (string) $idx;
                $checked = ! empty($answers[$key]) || ! empty($answers[$idx]);
                $normalized[$key] = $checked;
                if ($checked) {
                    $earned += $item['points'];
                }
            }
            $checklistAnswers = $normalized;
        }

        $payload = [
            'file_path' => $path,
            'original_name' => $originalName,
            'submitted_at' => now(),
            'notes' => $request->input('notes'),
            'checklist_answers' => $checklistAnswers,
            'checklist_points_earned' => $earned,
            'checklist_points_possible' => $possible,
        ];

        if ($existing) {
            $existing->update($payload);
        } else {
            OpenClassroomSubmission::create(array_merge($payload, [
                'assignment_id' => $assignment->id,
                'user_id' => $user->id,
            ]));
        }

        return redirect()
            ->route('academics.open-classrooms.assignments.show', [$openClassroom, $assignment])
            ->with('success', 'Submission saved.');
    }

    public function downloadSubmission(OpenClassroom $openClassroom, OpenClassroomSubmission $submission)
    {
        $assignment = $submission->assignment;
        if ((int) $assignment->open_classroom_id !== (int) $openClassroom->id) {
            abort(404);
        }

        $user = auth()->user();
        if ($user->id !== $submission->user_id && ! $this->classroomService->canManage($user, $openClassroom)) {
            abort(403);
        }

        if (! $submission->file_path || ! Storage::disk('public')->exists($submission->file_path)) {
            abort(404);
        }

        return response()->download(
            Storage::disk('public')->path($submission->file_path),
            $submission->original_name ?? 'submission'
        );
    }

    public function downloadAttachment(OpenClassroom $openClassroom, OpenClassroomAssignment $assignment, int $index)
    {
        if ((int) $assignment->open_classroom_id !== (int) $openClassroom->id) {
            abort(404);
        }

        $user = auth()->user();
        $isOwner = $this->classroomService->canManage($user, $openClassroom);
        $isMember = $this->classroomService->isMember($user, $openClassroom);
        if (! $isOwner && ! $isMember) {
            abort(403);
        }

        $attachments = $assignment->attachments ?? [];
        $file = $attachments[$index] ?? null;
        if (! $file || ! Storage::disk('public')->exists($file['path'] ?? '')) {
            abort(404);
        }

        return response()->download(
            Storage::disk('public')->path($file['path']),
            $file['name'] ?? 'attachment'
        );
    }

    protected function storeAttachments(OpenClassroomAssignment $assignment, ?array $files): void
    {
        if (! $files) {
            return;
        }
        $dir = 'academic/open-classrooms/'.$assignment->open_classroom_id.'/assignments/'.$assignment->id;
        $current = $assignment->attachments ?? [];
        foreach ($files as $file) {
            if (! $file->isValid()) {
                continue;
            }
            $stored = $file->storeAs($dir, $file->getClientOriginalName(), 'public');
            $current[] = ['path' => $stored, 'name' => $file->getClientOriginalName()];
        }
        $assignment->update(['attachments' => $current]);
    }

    protected function authorizeManage(OpenClassroom $openClassroom): void
    {
        if (! $this->classroomService->canManage(auth()->user(), $openClassroom)) {
            abort(403);
        }
    }
}

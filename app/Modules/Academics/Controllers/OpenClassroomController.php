<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academics\Models\OpenClassroom;
use App\Modules\Academics\Models\OpenClassroomResource;
use App\Modules\Academics\Services\OpenClassroomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OpenClassroomController extends Controller
{
    public function __construct(protected OpenClassroomService $classroomService) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $tab = $request->get('tab', 'browse');

        $browse = OpenClassroom::query()
            ->with('owner')
            ->browsable()
            ->orderByDesc('members_count')
            ->orderBy('title')
            ->paginate(12, ['*'], 'browse_page');

        $joinedIds = [];
        $mine = collect();
        $joined = collect();

        if (in_array($user->role, ['student', 'nurse', 'caregiver'], true)) {
            $joinedIds = $user->openClassrooms()->pluck('academic_open_classrooms.id')->all();
            $joined = OpenClassroom::query()
                ->with('owner')
                ->whereIn('id', $joinedIds)
                ->where('is_active', true)
                ->orderBy('title')
                ->get();
        }

        if ($user->role === 'faculty') {
            $mine = OpenClassroom::query()
                ->where('owner_id', $user->id)
                ->orderByDesc('updated_at')
                ->get();
        }

        return view('academics::open-classrooms.index', compact('browse', 'joined', 'joinedIds', 'mine', 'tab'));
    }

    public function create()
    {
        $this->assertCanCreateClassroom();

        return view('academics::open-classrooms.create');
    }

    public function store(Request $request)
    {
        $this->assertCanCreateClassroom();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'subject_area' => 'nullable|string|max:120',
            'visibility' => ['required', Rule::in([OpenClassroom::VISIBILITY_PUBLIC, OpenClassroom::VISIBILITY_UNLISTED])],
        ]);

        $classroom = OpenClassroom::create([
            ...$validated,
            'owner_id' => auth()->id(),
            'is_active' => true,
            'members_count' => 0,
        ]);

        return redirect()
            ->route('academics.open-classrooms.show', $classroom)
            ->with('success', 'Open classroom created. Add notes and assignments for your students.');
    }

    public function show(OpenClassroom $openClassroom)
    {
        $user = auth()->user();
        if (! $this->classroomService->canView($user, $openClassroom)) {
            abort(404);
        }

        $openClassroom->load(['owner', 'resources', 'assignments' => fn ($q) => $q->where('is_published', true)]);
        $isMember = $this->classroomService->isMember($user, $openClassroom);
        $isOwner = $this->classroomService->canManage($user, $openClassroom);
        $canJoin = in_array($user->role, ['student', 'nurse', 'caregiver'], true)
            && ! $isMember
            && ! $isOwner
            && $openClassroom->is_active;

        $mySubmissions = collect();
        if ($isMember && in_array($user->role, ['student', 'nurse', 'caregiver'], true)) {
            $assignmentIds = $openClassroom->assignments->pluck('id');
            $mySubmissions = \App\Modules\Academics\Models\OpenClassroomSubmission::query()
                ->where('user_id', $user->id)
                ->whereIn('assignment_id', $assignmentIds)
                ->get()
                ->keyBy('assignment_id');
        }

        return view('academics::open-classrooms.show', compact(
            'openClassroom',
            'isMember',
            'isOwner',
            'canJoin',
            'mySubmissions'
        ));
    }

    public function edit(OpenClassroom $openClassroom)
    {
        $this->authorizeManage($openClassroom);

        return view('academics::open-classrooms.edit', compact('openClassroom'));
    }

    public function update(Request $request, OpenClassroom $openClassroom)
    {
        $this->authorizeManage($openClassroom);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'subject_area' => 'nullable|string|max:120',
            'visibility' => ['required', Rule::in([OpenClassroom::VISIBILITY_PUBLIC, OpenClassroom::VISIBILITY_UNLISTED])],
            'is_active' => 'boolean',
        ]);

        $openClassroom->update([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('academics.open-classrooms.show', $openClassroom)
            ->with('success', 'Classroom updated.');
    }

    public function join(OpenClassroom $openClassroom)
    {
        $this->classroomService->join(auth()->user(), $openClassroom);

        return redirect()
            ->route('academics.open-classrooms.show', $openClassroom)
            ->with('success', 'You joined this classroom. Notes and assignments are now available.');
    }

    public function leave(OpenClassroom $openClassroom)
    {
        $this->classroomService->leave(auth()->user(), $openClassroom);

        return redirect()
            ->route('academics.open-classrooms.index', ['tab' => 'joined'])
            ->with('success', 'You left the classroom.');
    }

    public function storeResource(Request $request, OpenClassroom $openClassroom)
    {
        $this->authorizeManage($openClassroom);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'resource_type' => ['required', Rule::in([
                OpenClassroomResource::TYPE_FILE,
                OpenClassroomResource::TYPE_VIDEO_LINK,
                OpenClassroomResource::TYPE_NOTE,
            ])],
            'video_url' => 'nullable|url|max:500',
            'file' => 'nullable|file|max:10240',
        ]);

        $filePath = null;
        if ($request->file('file')?->isValid()) {
            $filePath = $request->file('file')->store('academic/open-classrooms/'.$openClassroom->id.'/resources', 'public');
        }

        if ($validated['resource_type'] === OpenClassroomResource::TYPE_FILE && ! $filePath) {
            return back()->withInput()->withErrors(['file' => 'Upload a file for this resource type.']);
        }

        if ($validated['resource_type'] === OpenClassroomResource::TYPE_VIDEO_LINK && empty($validated['video_url'])) {
            return back()->withInput()->withErrors(['video_url' => 'Video URL is required.']);
        }

        OpenClassroomResource::create([
            'open_classroom_id' => $openClassroom->id,
            'created_by' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'resource_type' => $validated['resource_type'],
            'video_url' => $validated['video_url'] ?? null,
            'file_path' => $filePath,
            'sort_order' => (int) $openClassroom->resources()->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Note / resource added.');
    }

    public function destroyResource(OpenClassroom $openClassroom, OpenClassroomResource $resource)
    {
        $this->authorizeManage($openClassroom);
        if ((int) $resource->open_classroom_id !== (int) $openClassroom->id) {
            abort(404);
        }

        if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
            Storage::disk('public')->delete($resource->file_path);
        }
        $resource->delete();

        return back()->with('success', 'Resource removed.');
    }

    public function downloadResource(OpenClassroom $openClassroom, OpenClassroomResource $resource)
    {
        $user = auth()->user();
        if (! $this->classroomService->canManage($user, $openClassroom)
            && ! $this->classroomService->isMember($user, $openClassroom)) {
            abort(403);
        }
        if ((int) $resource->open_classroom_id !== (int) $openClassroom->id) {
            abort(404);
        }
        if (! $resource->file_path || ! Storage::disk('public')->exists($resource->file_path)) {
            abort(404);
        }

        return response()->download(
            Storage::disk('public')->path($resource->file_path),
            basename($resource->file_path)
        );
    }

    protected function assertCanCreateClassroom(): void
    {
        if (auth()->user()->role !== 'faculty') {
            abort(403, 'Only teachers can create open classrooms.');
        }
    }

    protected function authorizeManage(OpenClassroom $openClassroom): void
    {
        if (! $this->classroomService->canManage(auth()->user(), $openClassroom)) {
            abort(403);
        }
    }
}

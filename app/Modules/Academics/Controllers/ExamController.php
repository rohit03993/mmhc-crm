<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Academics\Models\AcademicExam;
use App\Modules\Academics\Models\AcademicExamAttempt;
use App\Modules\Academics\Models\AcademicExamAttemptAnswer;
use App\Modules\Academics\Models\AcademicExamQuestion;
use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Services\ExamAccessService;
use App\Modules\Academics\Services\ExamScoringService;
use App\Modules\Academics\Services\TopicCompletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExamController extends Controller
{
    public function index(Request $request, ExamAccessService $examAccess): View
    {
        $user = Auth::user();
        $query = AcademicExam::query()->with(['subject.batch.institution', 'batch.institution', 'institution', 'creator']);

        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $query->where('institution_id', $user->academic_institution_id);
        } elseif ($user->role === 'faculty') {
            $instIds = $this->institutionIdsForAcademicUser($user);
            if ($instIds === []) {
                $query->whereRaw('1 = 0');
            } else {
                $this->applyFacultyManageableExamIndexScope($query, $user, $instIds);
            }
        } elseif ($user->role === 'student') {
            $instIds = $this->institutionIdsForAcademicUser($user);
            if ($instIds === []) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where(function ($q) use ($instIds) {
                    $q->whereIn('institution_id', $instIds)
                        ->orWhere(function ($q2) {
                            $q2->where('audience_type', AcademicExam::AUDIENCE_COMMUNITY)
                                ->where('allows_cross_institution', true);
                        });
                });
            }
        } else {
            $query->whereRaw('1 = 0');
        }

        if ($user->role === 'student') {
            $query->where('is_published', true);
        }

        $this->applyExamIndexFilters($request, $query, $user);

        $perPage = min(100, max(5, (int) $request->get('per_page', 20)));
        $useDbPagination = in_array($user->role, ['institution_admin', 'faculty'], true);

        if ($useDbPagination) {
            $exams = $query->orderByDesc('id')->paginate($perPage)->withQueryString();
        } else {
            $all = $query->orderByDesc('id')->limit(1000)->get();
            if ($user->role === 'student') {
                $filtered = $all->filter(fn (AcademicExam $exam) => $examAccess->studentCanViewPublishedExam($user, $exam))->values();
            } else {
                $filtered = $all->filter(fn (AcademicExam $exam) => $examAccess->canManage($user, $exam))->values();
            }
            $page = max(1, (int) $request->get('page', 1));
            $total = $filtered->count();
            $slice = $filtered->slice(($page - 1) * $perPage, $perPage)->values();
            $exams = new LengthAwarePaginator($slice, $total, $perPage, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        $viewerCanCreate = in_array($user->role, ['institution_admin', 'faculty'], true);

        $filterInstitutions = collect();

        return view('academics::exams.index', [
            'exams' => $exams,
            'viewerRole' => $user->role,
            'viewerCanCreate' => $viewerCanCreate,
            'filterInstitutions' => $filterInstitutions,
            'filters' => [
                'q' => $request->get('q', ''),
                'institution_id' => $request->get('institution_id', ''),
                'publish' => $request->get('publish', 'all'),
                'window' => $request->get('window', 'all'),
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $user = Auth::user();
        $this->authorizeCreate($user);

        $institutions = $this->institutionsForUser($user);
        $subjects = $this->subjectsForUser($user);
        $batches = $this->batchesForUser($user);
        $assignments = $this->assignmentsForExamWizard($user, $institutions);

        return view('academics::exams.create', compact('institutions', 'subjects', 'batches', 'assignments', 'user'));
    }

    public function store(Request $request, ExamAccessService $examAccess): RedirectResponse
    {
        $user = Auth::user();
        $this->authorizeCreate($user);

        $data = $this->validatedExamPayload($request, $user);
        $data['created_by'] = $user->id;
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published'] ? now() : null;

        $exam = new AcademicExam($data);
        $this->applyAudienceDefaults($exam);
        $exam->save();

        if (! $examAccess->canManage($user, $exam)) {
            $exam->delete();
            abort(403, 'You cannot manage an exam for this scope.');
        }

        return redirect()->route('academics.exams.edit', $exam)
            ->with('success', 'Exam created. Add questions below.');
    }

    public function show(AcademicExam $exam, ExamAccessService $examAccess, ExamScoringService $scoring): View
    {
        $user = Auth::user();
        $exam->load(['subject', 'batch', 'institution', 'assignment.topic', 'questions.options', 'creator']);

        $canManage = $examAccess->canManage($user, $exam);
        $canViewAsStudent = $user->role === 'student' && $examAccess->studentCanViewPublishedExam($user, $exam);
        if ($user->role === 'student' && ! $canViewAsStudent) {
            abort(403, 'This exam is not available for your account.');
        }
        $canTake = $user->role === 'student' && $examAccess->canTake($user, $exam);

        $attemptCount = 0;
        $inProgress = null;
        $lastSubmitted = null;
        if ($user->role === 'student') {
            $attemptCount = AcademicExamAttempt::where('exam_id', $exam->id)->where('user_id', $user->id)->count();
            $inProgress = AcademicExamAttempt::where('exam_id', $exam->id)
                ->where('user_id', $user->id)
                ->where('status', AcademicExamAttempt::STATUS_IN_PROGRESS)
                ->first();
            $lastSubmitted = AcademicExamAttempt::where('exam_id', $exam->id)
                ->where('user_id', $user->id)
                ->where('status', AcademicExamAttempt::STATUS_SUBMITTED)
                ->orderByDesc('submitted_at')
                ->first();
        }

        $maxPoints = $scoring->maxPointsForExam($exam);

        $manageAttempts = null;
        if ($canManage) {
            $manageAttempts = [
                'submitted_count' => AcademicExamAttempt::query()
                    ->where('exam_id', $exam->id)
                    ->where('status', AcademicExamAttempt::STATUS_SUBMITTED)
                    ->count(),
                'in_progress_count' => AcademicExamAttempt::query()
                    ->where('exam_id', $exam->id)
                    ->where('status', AcademicExamAttempt::STATUS_IN_PROGRESS)
                    ->count(),
                'recent' => AcademicExamAttempt::query()
                    ->where('exam_id', $exam->id)
                    ->whereIn('status', [
                        AcademicExamAttempt::STATUS_SUBMITTED,
                        AcademicExamAttempt::STATUS_IN_PROGRESS,
                    ])
                    ->with('user')
                    ->orderByDesc('id')
                    ->limit(75)
                    ->get(),
            ];
        }

        return view('academics::exams.show', compact(
            'exam',
            'canManage',
            'canTake',
            'attemptCount',
            'inProgress',
            'lastSubmitted',
            'maxPoints',
            'manageAttempts'
        ));
    }

    public function edit(AcademicExam $exam, ExamAccessService $examAccess): View
    {
        $user = Auth::user();
        if (! $examAccess->canManage($user, $exam)) {
            abort(403);
        }

        $exam->load(['questions.options']);
        $institutions = $this->institutionsForUser($user);
        $subjects = $this->subjectsForUser($user);
        $batches = $this->batchesForUser($user);
        $assignments = $this->assignmentsForInstitution((int) $exam->institution_id);

        return view('academics::exams.edit', compact('exam', 'institutions', 'subjects', 'batches', 'assignments', 'user'));
    }

    public function update(Request $request, AcademicExam $exam, ExamAccessService $examAccess): RedirectResponse
    {
        $user = Auth::user();
        if (! $examAccess->canManage($user, $exam)) {
            abort(403);
        }

        $data = $this->validatedExamPayload($request, $user, $exam);
        $exam->fill($data);
        $exam->is_published = $request->boolean('is_published');
        $exam->shuffle_questions = $request->boolean('shuffle_questions');
        $exam->shuffle_options = $request->boolean('shuffle_options');
        $exam->allows_cross_institution = $request->boolean('allows_cross_institution');
        if ($exam->is_published && ! $exam->published_at) {
            $exam->published_at = now();
        }
        if (! $exam->is_published) {
            $exam->published_at = null;
        }
        $this->applyAudienceDefaults($exam);
        $exam->save();

        if (! $examAccess->canManage($user, $exam)) {
            abort(403, 'Invalid scope after update.');
        }

        return redirect()->route('academics.exams.edit', $exam)
            ->with('success', 'Exam updated.');
    }

    public function destroy(AcademicExam $exam, ExamAccessService $examAccess): RedirectResponse
    {
        $user = Auth::user();
        if (! $examAccess->canManage($user, $exam)) {
            abort(403);
        }
        $exam->delete();

        return redirect()->route('academics.exams.index')
            ->with('success', 'Exam deleted.');
    }

    public function storeQuestion(Request $request, AcademicExam $exam, ExamAccessService $examAccess): RedirectResponse
    {
        $user = Auth::user();
        if (! $examAccess->canManage($user, $exam)) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:20000'],
            'explanation' => ['nullable', 'string', 'max:20000'],
            'points' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'question_type' => ['required', Rule::in([AcademicExamQuestion::TYPE_MCQ_SINGLE, AcademicExamQuestion::TYPE_MCQ_MULTI])],
            'options' => ['required', 'array', 'min:2', 'max:12'],
            'options.*' => ['required', 'string', 'max:5000'],
            'correct_option' => ['nullable', 'integer', 'min:0'],
            'correct_indices' => ['nullable', 'array'],
            'correct_indices.*' => ['integer', 'min:0'],
        ]);

        $n = count($validated['options']);
        $correctIndices = [];
        if ($validated['question_type'] === AcademicExamQuestion::TYPE_MCQ_SINGLE) {
            $idx = (int) ($validated['correct_option'] ?? -1);
            if ($idx < 0 || $idx >= $n) {
                throw ValidationException::withMessages(['correct_option' => 'Pick a valid correct option.']);
            }
            $correctIndices = [$idx];
        } else {
            $raw = array_unique($validated['correct_indices'] ?? []);
            if (count($raw) < 1) {
                throw ValidationException::withMessages(['correct_indices' => 'Select at least one correct answer for multi-select.']);
            }
            foreach ($raw as $idx) {
                if ($idx < 0 || $idx >= $n) {
                    throw ValidationException::withMessages(['correct_indices' => 'Invalid correct option index.']);
                }
            }
            $correctIndices = array_values(array_map('intval', $raw));
        }

        $nextOrder = (int) $exam->questions()->max('sort_order') + 1;

        DB::transaction(function () use ($exam, $validated, $correctIndices, $nextOrder) {
            $q = $exam->questions()->create([
                'body' => $validated['body'],
                'explanation' => $validated['explanation'] ?? null,
                'question_type' => $validated['question_type'],
                'sort_order' => $nextOrder,
                'points' => $validated['points'] ?? 1,
            ]);
            foreach ($validated['options'] as $i => $body) {
                $q->options()->create([
                    'label' => chr(65 + $i),
                    'body' => $body,
                    'is_correct' => in_array($i, $correctIndices, true),
                    'sort_order' => $i,
                ]);
            }
        });

        return redirect()->route('academics.exams.edit', $exam)
            ->with('success', 'Question added.');
    }

    public function destroyQuestion(AcademicExam $exam, AcademicExamQuestion $question, ExamAccessService $examAccess): RedirectResponse
    {
        $user = Auth::user();
        if (! $examAccess->canManage($user, $exam) || (int) $question->exam_id !== (int) $exam->id) {
            abort(403);
        }
        $question->delete();

        return redirect()->route('academics.exams.edit', $exam)
            ->with('success', 'Question removed.');
    }

    public function reorderQuestion(Request $request, AcademicExam $exam, AcademicExamQuestion $question, ExamAccessService $examAccess): RedirectResponse
    {
        $user = Auth::user();
        if (! $examAccess->canManage($user, $exam) || (int) $question->exam_id !== (int) $exam->id) {
            abort(403);
        }

        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];

        $ordered = $exam->questions()->orderBy('sort_order')->orderBy('id')->get();
        $idx = $ordered->search(fn (AcademicExamQuestion $q) => (int) $q->id === (int) $question->id);
        if ($idx === false) {
            abort(404);
        }

        $swapWith = null;
        if ($direction === 'up' && $idx > 0) {
            $swapWith = $ordered[$idx - 1];
        } elseif ($direction === 'down' && $idx < $ordered->count() - 1) {
            $swapWith = $ordered[$idx + 1];
        }

        if ($swapWith) {
            DB::transaction(function () use ($question, $swapWith) {
                $a = $question->sort_order;
                $b = $swapWith->sort_order;
                $question->update(['sort_order' => $b]);
                $swapWith->update(['sort_order' => $a]);
            });
        }

        return redirect()->route('academics.exams.edit', $exam);
    }

    public function attempts(AcademicExam $exam, Request $request, ExamAccessService $examAccess, ExamScoringService $scoring): View
    {
        $user = Auth::user();
        if (! $examAccess->canManage($user, $exam)) {
            abort(403);
        }

        $exam->load('questions');
        $maxPoints = $scoring->maxPointsForExam($exam);

        $attempts = AcademicExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('status', AcademicExamAttempt::STATUS_SUBMITTED)
            ->with(['user', 'answers'])
            ->orderByDesc('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('academics::exams.attempts', compact('exam', 'attempts', 'maxPoints'));
    }

    public function exportAttempts(AcademicExam $exam, ExamAccessService $examAccess, ExamScoringService $scoring): StreamedResponse
    {
        $user = Auth::user();
        if (! $examAccess->canManage($user, $exam)) {
            abort(403);
        }

        $maxPoints = $scoring->maxPointsForExam($exam->loadMissing('questions'));
        $filename = 'exam-'.$exam->id.'-results-'.date('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($exam, $maxPoints) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Attempt ID', 'Student name', 'Email', 'User ID', 'Score', 'Max score', 'Percent', 'Submitted at']);
            AcademicExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->where('status', AcademicExamAttempt::STATUS_SUBMITTED)
                ->with('user')
                ->orderByDesc('submitted_at')
                ->chunk(200, function ($chunk) use ($out, $maxPoints) {
                    foreach ($chunk as $a) {
                        $pct = $maxPoints > 0 ? round(((float) $a->score / $maxPoints) * 100, 2) : '';
                        fputcsv($out, [
                            $a->id,
                            $a->studentLabel(),
                            $a->user?->email ?? '',
                            $a->user_id,
                            $a->score,
                            $maxPoints,
                            $pct,
                            $a->submitted_at?->format('Y-m-d H:i:s') ?? '',
                        ]);
                    }
                });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function startAttempt(AcademicExam $exam, ExamAccessService $examAccess): RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== 'student' || ! $examAccess->canTake($user, $exam)) {
            abort(403);
        }

        $existing = AcademicExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->where('status', AcademicExamAttempt::STATUS_IN_PROGRESS)
            ->first();
        if ($existing) {
            return redirect()->route('academics.exams.take', [$exam, $existing]);
        }

        $count = AcademicExamAttempt::where('exam_id', $exam->id)->where('user_id', $user->id)->count();
        if ($count >= $exam->max_attempts) {
            return redirect()->route('academics.exams.show', $exam)
                ->with('error', 'Maximum attempts reached for this quiz.');
        }

        if ($exam->questions()->count() === 0) {
            return redirect()->route('academics.exams.show', $exam)
                ->with('error', 'This quiz has no questions yet.');
        }

        $attempt = AcademicExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
            'status' => AcademicExamAttempt::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        return redirect()->route('academics.exams.take', [$exam, $attempt]);
    }

    public function take(AcademicExam $exam, AcademicExamAttempt $attempt, ExamAccessService $examAccess, ExamScoringService $scoring): View
    {
        $user = Auth::user();
        if ((int) $attempt->exam_id !== (int) $exam->id) {
            abort(404);
        }
        if ((int) $attempt->user_id !== (int) $user->id || $user->role !== 'student') {
            abort(403);
        }
        if ($attempt->status !== AcademicExamAttempt::STATUS_IN_PROGRESS) {
            return redirect()->route('academics.exams.result', [$exam, $attempt]);
        }
        if (! $examAccess->canTake($user, $exam)) {
            abort(403);
        }

        if ($this->attemptHasTimeExpired($exam, $attempt)) {
            return $this->finalizeAttemptWithoutPostedAnswers($exam, $attempt, $scoring);
        }

        $questions = $this->orderedQuestionsWithOptions($exam, $attempt);
        $maxPoints = $scoring->maxPointsForExam($exam);
        $attemptExpiresAt = $this->attemptExpiresAt($exam, $attempt);

        return view('academics::exams.take', compact('exam', 'attempt', 'questions', 'maxPoints', 'attemptExpiresAt'));
    }

    public function submitAttempt(Request $request, AcademicExam $exam, AcademicExamAttempt $attempt, ExamAccessService $examAccess, ExamScoringService $scoring): RedirectResponse
    {
        $user = Auth::user();
        if ((int) $attempt->exam_id !== (int) $exam->id
            || (int) $attempt->user_id !== (int) $user->id
            || $user->role !== 'student') {
            abort(403);
        }
        if ($attempt->status !== AcademicExamAttempt::STATUS_IN_PROGRESS) {
            return redirect()->route('academics.exams.result', [$exam, $attempt]);
        }

        $exam->load(['questions.options']);
        $rules = [];
        foreach ($exam->questions as $q) {
            if ($q->question_type === AcademicExamQuestion::TYPE_MCQ_MULTI) {
                $allowed = $q->options->pluck('id')->all();
                $rules['answers.'.$q->id] = ['required', 'array', 'min:1'];
                $rules['answers.'.$q->id.'.*'] = ['integer', Rule::in($allowed)];
            } else {
                $rules['answers.'.$q->id] = ['required', 'integer', Rule::exists('academic_exam_options', 'id')->where('question_id', $q->id)];
            }
        }
        $request->validate($rules);

        DB::transaction(function () use ($request, $attempt, $exam, $scoring) {
            foreach ($exam->questions as $q) {
                if ($q->question_type === AcademicExamQuestion::TYPE_MCQ_MULTI) {
                    $ids = collect($request->input('answers.'.$q->id, []))->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
                    AcademicExamAttemptAnswer::updateOrCreate(
                        [
                            'attempt_id' => $attempt->id,
                            'question_id' => $q->id,
                        ],
                        ['option_id' => null, 'selected_option_ids' => $ids]
                    );
                } else {
                    $optionId = (int) $request->input('answers.'.$q->id);
                    AcademicExamAttemptAnswer::updateOrCreate(
                        [
                            'attempt_id' => $attempt->id,
                            'question_id' => $q->id,
                        ],
                        ['option_id' => $optionId, 'selected_option_ids' => null]
                    );
                }
            }
            $attempt->refresh();
            $attempt->load(['answers', 'exam.questions.options']);
            $score = $scoring->scoreSubmittedAttempt($attempt);
            $attempt->update([
                'status' => AcademicExamAttempt::STATUS_SUBMITTED,
                'submitted_at' => now(),
                'score' => $score,
            ]);
        });

        $exam->load('assignment');
        if ($exam->assignment_id && $exam->assignment) {
            TopicCompletionService::checkAndCompleteTopic($exam->assignment);
        }

        return redirect()->route('academics.exams.result', [$exam, $attempt->fresh()])
            ->with('success', 'Quiz submitted.');
    }

    public function result(AcademicExam $exam, AcademicExamAttempt $attempt, ExamAccessService $examAccess, ExamScoringService $scoring): View
    {
        $user = Auth::user();
        if ((int) $attempt->exam_id !== (int) $exam->id) {
            abort(404);
        }

        $isOwnerStudent = $user->role === 'student' && (int) $attempt->user_id === (int) $user->id;
        $canStaffView = $examAccess->canManage($user, $exam);

        if (! $isOwnerStudent && ! $canStaffView) {
            abort(403);
        }

        if ($attempt->status !== AcademicExamAttempt::STATUS_SUBMITTED) {
            abort(404, 'Attempt not submitted.');
        }

        $attempt->load(['user', 'answers.option', 'exam.questions.options']);
        $maxPoints = $scoring->maxPointsForExam($exam);
        $examScoring = $scoring;

        return view('academics::exams.result', compact('exam', 'attempt', 'maxPoints', 'canStaffView', 'examScoring'));
    }

    protected function authorizeCreate(User $user): void
    {
        if (! in_array($user->role, ['institution_admin', 'faculty'], true)) {
            abort(403);
        }
    }

    protected function institutionsForUser(User $user)
    {
        if ($user->academic_institution_id) {
            return Institution::where('id', $user->academic_institution_id)->get();
        }

        return collect();
    }

    protected function subjectsForUser(User $user)
    {
        $q = Subject::query()->with('batch.institution')->orderBy('name');
        if ($user->academic_institution_id) {
            $q->whereHas('batch', fn ($b) => $b->where('institution_id', $user->academic_institution_id));
        }

        return $q->get();
    }

    protected function batchesForUser(User $user)
    {
        $q = Batch::query()->with('institution')->orderBy('name');
        if ($user->academic_institution_id) {
            $q->where('institution_id', $user->academic_institution_id);
        }

        return $q->get();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedExamPayload(Request $request, User $user, ?AcademicExam $existing = null): array
    {
        $institutionRule = ['required', 'exists:academic_institutions,id'];
        $institutionRule[] = Rule::in([(int) $user->academic_institution_id]);

        $data = $request->validate([
            'institution_id' => $institutionRule,
            'audience_type' => ['required', Rule::in(AcademicExam::audienceTypes())],
            'subject_id' => ['nullable', 'exists:academic_subjects,id'],
            'batch_id' => ['nullable', 'exists:academic_batches,id'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:20000'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:20'],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after_or_equal:opens_at'],
            'assignment_id' => ['nullable', 'integer', 'exists:academic_assignments,id'],
        ]);

        $data['shuffle_questions'] = $request->boolean('shuffle_questions');
        $data['shuffle_options'] = $request->boolean('shuffle_options');
        $data['allows_cross_institution'] = $request->boolean('allows_cross_institution');

        if ($data['audience_type'] === AcademicExam::AUDIENCE_SUBJECT_COHORT && empty($data['subject_id'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['subject_id' => 'Subject is required for this audience.']);
        }
        if ($data['audience_type'] === AcademicExam::AUDIENCE_BATCH && empty($data['batch_id'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['batch_id' => 'Batch is required for this audience.']);
        }

        if (! empty($data['subject_id'])) {
            $subject = Subject::with('batch')->find($data['subject_id']);
            if ($subject && $subject->batch && (int) $subject->batch->institution_id !== (int) $data['institution_id']) {
                throw \Illuminate\Validation\ValidationException::withMessages(['subject_id' => 'Subject must belong to the selected institution.']);
            }
        }
        if (! empty($data['batch_id'])) {
            $batch = Batch::find($data['batch_id']);
            if ($batch && (int) $batch->institution_id !== (int) $data['institution_id']) {
                throw \Illuminate\Validation\ValidationException::withMessages(['batch_id' => 'Batch must belong to the selected institution.']);
            }
        }

        $data['assignment_id'] = ! empty($data['assignment_id']) ? (int) $data['assignment_id'] : null;
        if ($data['assignment_id']) {
            $assignment = Assignment::with('topic.subject.batch')->find($data['assignment_id']);
            if (! $assignment || ! $assignment->topic?->subject?->batch) {
                throw ValidationException::withMessages(['assignment_id' => 'Invalid assignment.']);
            }
            if ((int) $assignment->topic->subject->batch->institution_id !== (int) $data['institution_id']) {
                throw ValidationException::withMessages(['assignment_id' => 'Assignment must belong to the same institution as this exam.']);
            }
            if ($data['audience_type'] === AcademicExam::AUDIENCE_SUBJECT_COHORT && ! empty($data['subject_id'])) {
                if ((int) $assignment->topic->subject_id !== (int) $data['subject_id']) {
                    throw ValidationException::withMessages(['assignment_id' => 'Choose an assignment from the same subject, or clear this field.']);
                }
            }
        }

        return $data;
    }

    protected function applyAudienceDefaults(AcademicExam $exam): void
    {
        if ($exam->audience_type !== AcademicExam::AUDIENCE_SUBJECT_COHORT) {
            $exam->subject_id = null;
        }
        if ($exam->audience_type !== AcademicExam::AUDIENCE_BATCH) {
            $exam->batch_id = null;
        }
        if ($exam->audience_type !== AcademicExam::AUDIENCE_COMMUNITY) {
            $exam->allows_cross_institution = false;
        }
    }

    protected function attemptExpiresAt(AcademicExam $exam, AcademicExamAttempt $attempt): ?\Illuminate\Support\Carbon
    {
        if (! $exam->duration_minutes || ! $attempt->started_at) {
            return null;
        }

        return $attempt->started_at->copy()->addMinutes((int) $exam->duration_minutes);
    }

    protected function attemptHasTimeExpired(AcademicExam $exam, AcademicExamAttempt $attempt): bool
    {
        $at = $this->attemptExpiresAt($exam, $attempt);

        return $at !== null && now()->gt($at);
    }

    protected function finalizeAttemptWithoutPostedAnswers(AcademicExam $exam, AcademicExamAttempt $attempt, ExamScoringService $scoring): RedirectResponse
    {
        DB::transaction(function () use ($attempt, $scoring) {
            $attempt->load(['answers', 'exam.questions.options']);
            $score = $scoring->scoreSubmittedAttempt($attempt);
            $attempt->update([
                'status' => AcademicExamAttempt::STATUS_SUBMITTED,
                'submitted_at' => now(),
                'score' => $score,
            ]);
        });

        return redirect()->route('academics.exams.result', [$exam, $attempt->fresh()])
            ->with('warning', 'The time limit for this quiz had already ended. Only answers already saved on the server were scored.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, Assignment>
     */
    protected function assignmentsForInstitution(int $institutionId)
    {
        if ($institutionId <= 0) {
            return collect();
        }

        return Assignment::query()
            ->with(['topic.subject'])
            ->whereHas('topic.subject.batch', fn ($q) => $q->where('institution_id', $institutionId))
            ->orderBy('title')
            ->limit(400)
            ->get();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Institution>  $institutions
     * @return \Illuminate\Support\Collection<int, Assignment>
     */
    protected function assignmentsForExamWizard(User $user, $institutions)
    {
        return $this->assignmentsForInstitution((int) ($user->academic_institution_id ?? 0));
    }

    /**
     * @return \Illuminate\Support\Collection<int, AcademicExamQuestion>
     */
    protected function orderedQuestionsWithOptions(AcademicExam $exam, AcademicExamAttempt $attempt)
    {
        $questions = $exam->questions()->with('options')->orderBy('sort_order')->get();
        if ($exam->shuffle_questions) {
            $questions = $questions->sortBy(fn ($q) => crc32($attempt->id.':'.$q->id))->values();
        }
        foreach ($questions as $q) {
            if ($exam->shuffle_options) {
                $sortedOpts = $q->options->sortBy(fn ($o) => crc32($attempt->id.':'.$q->id.':'.$o->id))->values();
                $q->setRelation('options', $sortedOpts);
            }
        }

        return $questions;
    }

    /**
     * SQL equivalent of {@see ExamAccessService::canManage()} for faculty (index listing + DB pagination).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AcademicExam>  $query
     * @param  list<int>  $instIds
     */
    protected function applyFacultyManageableExamIndexScope($query, User $user, array $instIds): void
    {
        $uid = (int) $user->id;
        $t = (new AcademicExam)->getTable();

        $query->where(function ($outer) use ($uid, $instIds, $t) {
            $outer->where(function ($a) use ($uid, $instIds, $t) {
                $a->whereIn("{$t}.institution_id", $instIds)
                    ->where(function ($inner) use ($uid, $t) {
                        $inner->where(function ($sc) use ($uid, $t) {
                            $sc->where("{$t}.audience_type", AcademicExam::AUDIENCE_SUBJECT_COHORT)
                                ->whereNotNull("{$t}.subject_id")
                                ->whereExists(function ($sub) use ($uid, $t) {
                                    $sub->selectRaw('1')
                                        ->from('academic_subject_faculty')
                                        ->whereColumn('academic_subject_faculty.subject_id', "{$t}.subject_id")
                                        ->where('academic_subject_faculty.user_id', $uid);
                                });
                        })
                            ->orWhere(function ($bt) use ($uid, $t) {
                                $bt->where("{$t}.audience_type", AcademicExam::AUDIENCE_BATCH)
                                    ->whereNotNull("{$t}.batch_id")
                                    ->whereExists(function ($sub) use ($uid, $t) {
                                        $sub->selectRaw('1')
                                            ->from('academic_batch_users')
                                            ->whereColumn('academic_batch_users.batch_id', "{$t}.batch_id")
                                            ->where('academic_batch_users.user_id', $uid)
                                            ->where('academic_batch_users.type', 'faculty');
                                    });
                            })
                            ->orWhereIn("{$t}.audience_type", [
                                AcademicExam::AUDIENCE_INSTITUTION_OPEN,
                                AcademicExam::AUDIENCE_COMMUNITY,
                            ]);
                    });
            })
                ->orWhere(function ($b) use ($uid, $t) {
                    $b->where("{$t}.audience_type", AcademicExam::AUDIENCE_COMMUNITY)
                        ->where("{$t}.allows_cross_institution", true)
                        ->whereExists(function ($sub) use ($uid, $t) {
                            $sub->selectRaw('1')
                                ->from('academic_batches')
                                ->join('academic_batch_users', 'academic_batch_users.batch_id', '=', 'academic_batches.id')
                                ->whereColumn('academic_batches.institution_id', "{$t}.institution_id")
                                ->where('academic_batch_users.user_id', $uid)
                                ->where('academic_batch_users.type', 'faculty');
                        });
                });
        });
    }

    protected function applyExamIndexFilters(Request $request, $query, User $user): void
    {
        $search = trim((string) $request->get('q', ''));
        if ($search !== '') {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->where('title', 'like', $like);
        }

        $publish = $user->role === 'student'
            ? 'published'
            : $request->get('publish', 'all');
        if (! in_array($publish, ['all', 'published', 'draft'], true)) {
            $publish = 'all';
        }

        $window = $request->get('window', 'all');
        if (! in_array($window, ['all', 'upcoming', 'open', 'ended'], true)) {
            $window = 'all';
        }

        if ($publish === 'draft') {
            $window = 'all';
        }

        if ($publish === 'published') {
            $query->where('is_published', true);
            if ($window !== 'all') {
                $this->applyScheduleWindowToBuilder($query, $window);
            }
        } elseif ($publish === 'draft') {
            $query->where('is_published', false);
        } elseif ($window !== 'all') {
            $query->where(function ($outer) use ($window) {
                $outer->where('is_published', false)
                    ->orWhere(function ($inner) use ($window) {
                        $inner->where('is_published', true);
                        $this->applyScheduleWindowToBuilder($inner, $window);
                    });
            });
        }
    }

    protected function applyScheduleWindowToBuilder($query, string $window): void
    {
        if ($window === 'upcoming') {
            $query->whereNotNull('opens_at')->where('opens_at', '>', now());
        } elseif ($window === 'open') {
            $query->where(function ($w) {
                $w->whereNull('opens_at')->orWhere('opens_at', '<=', now());
            })->where(function ($w) {
                $w->whereNull('closes_at')->orWhere('closes_at', '>=', now());
            });
        } elseif ($window === 'ended') {
            $query->whereNotNull('closes_at')->where('closes_at', '<', now());
        }
    }

    /**
     * For students/faculty: use academic_institution_id when set, else derive from batch membership.
     *
     * @return list<int>
     */
    protected function institutionIdsForAcademicUser(User $user): array
    {
        if ((int) ($user->academic_institution_id ?? 0) > 0) {
            return [(int) $user->academic_institution_id];
        }

        $ids = DB::table('academic_batch_users')
            ->join('academic_batches', 'academic_batches.id', '=', 'academic_batch_users.batch_id')
            ->where('academic_batch_users.user_id', $user->id)
            ->whereIn('academic_batch_users.type', ['student', 'faculty'])
            ->pluck('academic_batches.institution_id')
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return array_values(array_filter($ids, fn (int $id) => $id > 0));
    }
}

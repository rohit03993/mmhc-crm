<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Academics\Models\Mentorship;
use App\Modules\Academics\Models\Submission;
use App\Modules\Academics\Models\SubmissionMentorReview;
use App\Modules\Academics\Models\SubmissionMentorShare;
use App\Modules\Academics\Services\AcademicScoreService;
use App\Modules\Academics\Services\MentorVerificationService;
use App\Modules\Academics\Services\MentorshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MentorshipController extends Controller
{
    public function __construct(protected MentorshipService $mentorshipService) {}

    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'faculty') {
            $pendingRequests = Mentorship::query()
                ->with('mentee')
                ->where('mentor_id', $user->id)
                ->where('status', Mentorship::STATUS_PENDING)
                ->orderByDesc('created_at')
                ->get();

            $activeMentees = Mentorship::query()
                ->with('mentee')
                ->where('mentor_id', $user->id)
                ->where('status', Mentorship::STATUS_ACTIVE)
                ->orderBy('created_at')
                ->get();

            $reviewedIds = SubmissionMentorReview::query()
                ->where('mentor_id', $user->id)
                ->pluck('submission_id');

            $pendingReviews = SubmissionMentorShare::query()
                ->with(['submission.assignment', 'submission.user'])
                ->where('mentor_id', $user->id)
                ->whereNotIn('submission_id', $reviewedIds)
                ->orderByDesc('created_at')
                ->get();

            $fpiBreakdown = AcademicScoreService::getFpiBreakdown($user);

            return view('academics::mentorship.faculty-index', [
                'pendingRequests' => $pendingRequests,
                'activeMentees' => $activeMentees,
                'menteeCount' => $this->mentorshipService->activeMenteeCountFor($user),
                'pendingReviews' => $pendingReviews,
                'fpiBreakdown' => $fpiBreakdown,
            ]);
        }

        if (! in_array($user->role, MentorshipService::menteeRoleSlugs(), true)) {
            abort(403);
        }

        $myMentors = Mentorship::query()
            ->with(['mentor.academicInstitution'])
            ->where('mentee_id', $user->id)
            ->whereIn('status', [Mentorship::STATUS_PENDING, Mentorship::STATUS_ACTIVE])
            ->orderByDesc('created_at')
            ->get();

        return view('academics::mentorship.mentee-index', [
            'myMentors' => $myMentors,
            'mentorCount' => $this->mentorshipService->activeMentorCountFor($user),
        ]);
    }

    public function browse(Request $request)
    {
        $user = auth()->user();
        $this->mentorshipService->assertMenteeEligible($user);

        $query = User::query()
            ->with('academicInstitution')
            ->where('role', 'faculty')
            ->where('is_active', true);

        if ($request->filled('q')) {
            $q = trim((string) $request->get('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%'.$q.'%')
                    ->orWhere('qualification', 'like', '%'.$q.'%')
                    ->orWhere('unique_id', 'like', '%'.$q.'%');
            });
        }

        if ($request->filled('institution_id')) {
            $query->where('academic_institution_id', (int) $request->get('institution_id'));
        }

        $existingMentorIds = Mentorship::query()
            ->where('mentee_id', $user->id)
            ->whereIn('status', [Mentorship::STATUS_PENDING, Mentorship::STATUS_ACTIVE])
            ->pluck('mentor_id');

        $faculty = $query->whereNotIn('id', $existingMentorIds)->orderBy('name')->paginate(12)->withQueryString();

        return view('academics::mentorship.browse', compact('faculty'));
    }

    public function request(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'mentor_id' => ['required', 'integer', 'exists:users,id'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $mentor = User::query()->findOrFail((int) $validated['mentor_id']);
        $this->mentorshipService->request($user, $mentor, $validated['message'] ?? null);

        return redirect()->route('academics.mentorship.index')
            ->with('success', 'Mentorship request sent to '.$mentor->name.'.');
    }

    public function respond(Request $request, Mentorship $mentorship)
    {
        $user = auth()->user();
        if ($user->role !== 'faculty') {
            abort(403);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:accept,decline'],
            'response_message' => ['nullable', 'string', 'max:500'],
        ]);

        $accept = $validated['action'] === 'accept';
        $this->mentorshipService->respond($mentorship, $user, $accept, $validated['response_message'] ?? null);

        $msg = $accept ? 'Mentorship request accepted.' : 'Mentorship request declined.';

        return redirect()->route('academics.mentorship.index')->with('success', $msg);
    }

    public function reviewForm(SubmissionMentorShare $share)
    {
        $user = auth()->user();
        if ($user->role !== 'faculty' || (int) $share->mentor_id !== (int) $user->id) {
            abort(403);
        }

        $share->load(['submission.assignment.topic.subject', 'submission.user']);
        $existing = SubmissionMentorReview::query()
            ->where('submission_id', $share->submission_id)
            ->where('mentor_id', $user->id)
            ->first();

        return view('academics::mentorship.review', compact('share', 'existing'));
    }

    public function profile(User $user)
    {
        $viewer = auth()->user();

        if (in_array($viewer->role, ['super_admin', 'admin', 'institution_admin'], true)) {
            return redirect()->route('academics.people.show', $user);
        }

        if ($viewer->id === $user->id) {
            return redirect()->route('profile.index');
        }

        if (! $this->mentorshipService->canViewLimitedProfile($viewer, $user)) {
            abort(403, 'You cannot view this profile.');
        }

        $profileData = $this->mentorshipService->buildLimitedProfile($user);

        return view('academics::mentorship.profile', $profileData);
    }

    public function reviewStore(Request $request, SubmissionMentorShare $share, MentorVerificationService $mentorVerification)
    {
        $user = auth()->user();
        if ($user->role !== 'faculty' || (int) $share->mentor_id !== (int) $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        SubmissionMentorReview::updateOrCreate(
            [
                'submission_id' => $share->submission_id,
                'mentor_id' => $user->id,
            ],
            [
                'rating' => (int) $validated['rating'],
                'feedback' => $validated['feedback'] ?? null,
                'reviewed_at' => now(),
            ]
        );

        $share->load('submission');
        if ($share->submission) {
            $mentorVerification->syncVerificationTimestamp($share->submission);
        }

        return redirect()->route('academics.mentorship.index')
            ->with('success', 'Submission rated successfully.');
    }
}

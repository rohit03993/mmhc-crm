<?php

namespace App\Modules\Profiles\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Profiles\Services\StaffIdCardService;
use Illuminate\Support\Facades\Auth;

class StaffIdCardController extends Controller
{
    public function __construct(
        protected StaffIdCardService $idCardService
    ) {}

    /**
     * Staff prints their own ID card (nurse / caregiver).
     */
    public function showOwn()
    {
        $user = Auth::user();

        abort_unless($user->isStaff(), 403, 'ID cards are only available for nurses and caregivers.');

        return $this->renderCard($user);
    }

    /**
     * Admin prints a staff member's ID card.
     */
    public function showForUser(User $user)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        abort_unless($user->isStaff(), 404, 'ID cards are only available for nurses and caregivers.');

        return $this->renderCard($user);
    }

    /**
     * Public verification page (QR code target).
     */
    public function verify(string $uniqueId)
    {
        $user = User::query()
            ->with('profile')
            ->where('unique_id', $uniqueId)
            ->whereIn('role', ['nurse', 'caregiver'])
            ->firstOrFail();

        $card = $this->idCardService->buildCardData($user);

        return view('profiles::id-cards.verify', [
            'card' => $card,
            'verified' => $user->is_active && $user->hasVerifiedPhone(),
            'phoneVerified' => $user->hasVerifiedPhone(),
        ]);
    }

    protected function renderCard(User $user)
    {
        if (! $user->isStaff()) {
            abort(403, 'ID cards are only available for nurses and caregivers.');
        }

        abort_unless(
            $this->idCardService->canIssueIdCard($user),
            403,
            $this->idCardService->idCardUnavailableMessage($user)
                ?? 'ID card cannot be issued for this account.'
        );

        return view('profiles::id-cards.staff', [
            'card' => $this->idCardService->buildCardData($user),
        ]);
    }
}

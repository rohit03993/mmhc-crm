<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Models\UserNotification;
use App\Modules\Auth\Services\NotificationInboxService;
use Illuminate\Support\Facades\Auth;

class NotificationInboxController extends Controller
{
    public function open(UserNotification $userNotification)
    {
        if ((int) $userNotification->user_id !== (int) Auth::id()) {
            abort(403, 'You are not allowed to access this notification.');
        }

        $userNotification->markRead();

        $url = $userNotification->action_url ?: route('dashboard');

        return redirect()->to($url);
    }

    public function markAllRead(NotificationInboxService $inbox)
    {
        $inbox->markAllReadForUser((int) Auth::id());

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}

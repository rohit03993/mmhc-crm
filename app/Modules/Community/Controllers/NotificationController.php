<?php

namespace App\Modules\Community\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Community\Models\CommunityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function open(Request $request, CommunityNotification $notification)
    {
        if ((int) $notification->recipient_user_id !== (int) Auth::id()) {
            abort(403, 'You are not allowed to access this notification.');
        }

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return redirect()->route('community.index', ['page' => $request->get('page', 1)])
            ->withFragment('post-' . $notification->post_id);
    }

    public function markAllRead()
    {
        app(\App\Modules\Auth\Services\NotificationInboxService::class)
            ->markAllReadForUser((int) Auth::id());

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}


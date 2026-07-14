<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\UserNotification;
use App\Modules\Community\Models\CommunityNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class NotificationInboxService
{
    /**
     * Share merged bell data with the top navbar.
     */
    public function shareNavbarData($view): void
    {
        $unread = 0;
        $items = collect();

        if (! Auth::check()) {
            $view->with([
                'communityUnreadNotificationsCount' => 0,
                'communityRecentNotifications' => collect(),
                'mmhcUnreadNotificationsCount' => 0,
                'mmhcRecentNotifications' => collect(),
            ]);

            return;
        }

        $userId = (int) Auth::id();
        $crmUnread = 0;
        $communityUnread = 0;
        $crmItems = collect();
        $communityItems = collect();

        if (Schema::hasTable('user_notifications')) {
            $crmUnread = UserNotification::query()
                ->where('user_id', $userId)
                ->whereNull('read_at')
                ->count();

            $crmItems = UserNotification::query()
                ->where('user_id', $userId)
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (UserNotification $n) => $this->mapCrm($n));
        }

        if (Schema::hasTable('community_notifications')) {
            $communityUnread = CommunityNotification::query()
                ->where('recipient_user_id', $userId)
                ->whereNull('read_at')
                ->count();

            $communityItems = CommunityNotification::query()
                ->with(['actor:id,name', 'post:id'])
                ->where('recipient_user_id', $userId)
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (CommunityNotification $n) => $this->mapCommunity($n));
        }

        $items = $crmItems
            ->concat($communityItems)
            ->sortByDesc(fn ($row) => $row['created_at_ts'])
            ->values()
            ->take(10);

        $unread = $crmUnread + $communityUnread;

        $view->with([
            // Keep legacy vars for any older includes
            'communityUnreadNotificationsCount' => $unread,
            'communityRecentNotifications' => collect(),
            'mmhcUnreadNotificationsCount' => $unread,
            'mmhcRecentNotifications' => $items,
            'mmhcCrmUnreadNotificationsCount' => $crmUnread,
            'mmhcCommunityUnreadNotificationsCount' => $communityUnread,
        ]);
    }

    public function markAllReadForUser(int $userId): void
    {
        if (Schema::hasTable('user_notifications')) {
            UserNotification::query()
                ->where('user_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        if (Schema::hasTable('community_notifications')) {
            CommunityNotification::query()
                ->where('recipient_user_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
    }

    private function mapCrm(UserNotification $n): array
    {
        return [
            'source' => 'crm',
            'id' => $n->id,
            'icon' => 'fa-calendar-check',
            'title' => $n->title,
            'body' => $n->body,
            'unread' => $n->isUnread(),
            'created_at' => $n->created_at,
            'created_at_ts' => optional($n->created_at)->timestamp ?? 0,
            'open_route' => 'notifications.open',
            'open_params' => ['userNotification' => $n->id],
        ];
    }

    private function mapCommunity(CommunityNotification $n): array
    {
        $actor = $n->actor->name ?? 'Member';
        if ($n->type === 'comment') {
            $title = "{$actor} commented on your post";
        } elseif ($n->type === 'event_interest') {
            $title = "{$actor} responded on your event";
        } else {
            $title = "{$actor} reacted on your post";
        }

        return [
            'source' => 'community',
            'id' => $n->id,
            'icon' => 'fa-users',
            'title' => $title,
            'body' => null,
            'unread' => $n->read_at === null,
            'created_at' => $n->created_at,
            'created_at_ts' => optional($n->created_at)->timestamp ?? 0,
            'open_route' => 'community.notifications.open',
            'open_params' => ['notification' => $n->id],
        ];
    }
}

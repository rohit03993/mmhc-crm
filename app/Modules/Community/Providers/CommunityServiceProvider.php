<?php

namespace App\Modules\Community\Providers;

use App\Modules\Community\Models\CommunityNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class CommunityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        view()->composer('auth::components.navbar', function ($view) {
            $unreadCount = 0;
            $recentNotifications = collect();

            if (Auth::check()) {
                $unreadCount = CommunityNotification::query()
                    ->where('recipient_user_id', Auth::id())
                    ->whereNull('read_at')
                    ->count();

                $recentNotifications = CommunityNotification::query()
                    ->with(['actor:id,name', 'post:id'])
                    ->where('recipient_user_id', Auth::id())
                    ->latest()
                    ->limit(5)
                    ->get();
            }

            $view->with('communityUnreadNotificationsCount', $unreadCount);
            $view->with('communityRecentNotifications', $recentNotifications);
        });
    }
}


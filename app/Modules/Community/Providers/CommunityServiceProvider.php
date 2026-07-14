<?php

namespace App\Modules\Community\Providers;

use Illuminate\Support\ServiceProvider;

class CommunityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Navbar notification data is provided by Auth NotificationInboxService (CRM + community).
    }
}

<?php

namespace App\Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViews();
        $this->publishAssets();

        view()->composer('auth::components.navbar', function ($view) {
            app(\App\Modules\Auth\Services\NotificationInboxService::class)->shareNavbarData($view);
        });
    }

    /**
     * Load module views
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Views', 'auth');
    }

    /**
     * Publish module assets
     */
    protected function publishAssets(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Assets' => public_path('modules/auth'),
            ], 'auth-assets');
        }
    }
}

<?php

namespace App\Modules\Profiles\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ProfilesServiceProvider extends ServiceProvider
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
    }

    /**
     * Load module views
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Views', 'profiles');
    }

    /**
     * Publish module assets
     */
    protected function publishAssets(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Assets' => public_path('modules/profiles'),
            ], 'profiles-assets');
        }
    }
}

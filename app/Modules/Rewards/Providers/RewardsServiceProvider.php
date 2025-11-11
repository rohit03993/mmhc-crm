<?php

namespace App\Modules\Rewards\Providers;

use Illuminate\Support\ServiceProvider;

class RewardsServiceProvider extends ServiceProvider
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
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Views', 'rewards');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}


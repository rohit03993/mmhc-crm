<?php

namespace App\Modules\Incentives\Providers;

use Illuminate\Support\ServiceProvider;

class IncentivesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            \App\Modules\Incentives\Services\IncentiveCalculatorService::class,
            fn () => new \App\Modules\Incentives\Services\IncentiveCalculatorService
        );
    }

    public function boot(): void {}
}

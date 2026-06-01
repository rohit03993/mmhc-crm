<?php

namespace App\Modules\Academics\Providers;

use App\Modules\Academics\Support\AcademicsMobileUi;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AcademicsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('academics::*', function ($view) {
            $user = auth()->user();
            $view->with('academicsMobileUi', AcademicsMobileUi::enabledFor($user));
        });
    }
}

<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('site_settings')) {
            $logoPath = SiteSetting::get('logo_path');
            View::share('siteLogoUrl', ($logoPath && storage_asset($logoPath)) ? storage_asset($logoPath) : asset('images/med-logo.png'));
            View::share('siteCompanyName', SiteSetting::get('company_name') ?: 'MeD Miracle Health Care');
            View::share('siteTagline', SiteSetting::get('tagline') ?: 'Miracle Health Care');
        } else {
            View::share('siteLogoUrl', asset('images/med-logo.png'));
            View::share('siteCompanyName', 'MeD Miracle Health Care');
            View::share('siteTagline', 'Miracle Health Care');
        }
    }
}

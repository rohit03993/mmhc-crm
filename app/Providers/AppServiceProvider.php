<?php

namespace App\Providers;

use App\Models\SiteSetting;
use App\Modules\Payments\Services\StaffPayoutService;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Rewards\Models\CaregiverReward;
use App\Modules\Services\Models\ServiceRequest;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
        // Keep pagination consistent across CRM.
        Paginator::defaultView('pagination.modern');
        Paginator::defaultSimpleView('pagination.modern-simple');

        $fallbackLogo = asset('images/med-logo.png');
        if (Schema::hasTable('site_settings')) {
            $logoPath = trim((string) (SiteSetting::get('logo_path') ?? ''), '/');
            $logoUrl = $fallbackLogo;
            if ($logoPath !== '' && Storage::disk('public')->exists($logoPath)) {
                $logoUrl = storage_asset($logoPath) ?: $fallbackLogo;
            }
            View::share('siteLogoUrl', $logoUrl);
            View::share('siteCompanyName', SiteSetting::get('company_name') ?: 'MeD Miracle Health Care');
            View::share('siteTagline', SiteSetting::get('tagline') ?: 'Miracle Health Care');
        } else {
            View::share('siteLogoUrl', $fallbackLogo);
            View::share('siteCompanyName', 'MeD Miracle Health Care');
            View::share('siteTagline', 'Miracle Health Care');
        }

        View::composer('auth::layout', function ($view): void {
            $pendingReferralOtpBanner = null;
            $pendingReferralOtpContacts = null;
            $pendingRewardOtpBanner = null;
            $pendingServiceCompletionBanner = null;
        $serviceCompletionSkipsPatientOtp = false;
            $hasPendingContactUpdate = false;
            $heldEarningsDueToUnverifiedMobile = null;
            $staffNeedsMobileVerification = false;
            $needsPhoneVerification = false;

            if (Auth::check()) {
                $user = Auth::user();
                $user->syncStalePhoneVerificationState();
                $user = $user->fresh();
                $hasPendingContactUpdate = $user->hasPendingMobileContactVerification();
                $needsPhoneVerification = $user->mustVerifyPhoneBeforeAppAccess();
                $staffNeedsMobileVerification = $needsPhoneVerification && $user->isStaff();
            }

            if (Auth::check() && Auth::user()->isStaff()) {
                $user = Auth::user();
                if (! $user->hasVerifiedPhone()) {
                    $heldEarningsDueToUnverifiedMobile = app(StaffPayoutService::class)
                        ->calculateHeldDueToUnverifiedMobile($user);
                }
                $pendingReferralOtpBanner = Referral::query()
                    ->select(['id', 'referred_id', 'status', 'verification_status', 'created_at'])
                    ->where('referred_id', $user->id)
                    ->where('status', 'pending')
                    ->where('verification_status', 'pending')
                    ->latest('id')
                    ->first();

                $mobileMasked = null;
                $digits = preg_replace('/\D+/', '', (string) ($user->pending_phone ?: $user->phone ?? ''));
                if (strlen($digits) >= 10) {
                    $tail = substr($digits, -10);
                    $mobileMasked = str_repeat('*', 6).substr($tail, -4);
                }
                $email = trim((string) ($user->pending_email ?: $user->email ?? ''));
                $emailMasked = null;
                if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $parts = explode('@', $email);
                    if (count($parts) === 2) {
                        $name = $parts[0];
                        $domain = $parts[1];
                        $emailMasked = (strlen($name) <= 2 ? str_repeat('*', strlen($name)) : substr($name, 0, 2).str_repeat('*', max(0, strlen($name) - 2))).'@'.$domain;
                    }
                }
                $pendingReferralOtpContacts = [
                    'mobile' => $mobileMasked,
                    'email' => $emailMasked,
                ];

                $pendingRewardOtpBanner = CaregiverReward::query()
                    ->select(['id', 'user_id', 'verification_status', 'created_at'])
                    ->where('user_id', $user->id)
                    ->where('verification_status', 'pending')
                    ->latest('id')
                    ->first();

                $pendingServiceCompletionBanner = ServiceRequest::query()
                    ->select(['id', 'assigned_staff_id', 'status', 'completion_verified_at', 'patient_id', 'contact_phone', 'end_date', 'created_at'])
                    ->with('patient:id,name,phone')
                    ->where('assigned_staff_id', $user->id)
                    ->where('status', 'in_progress')
                    ->whereNull('completion_verified_at')
                    ->whereDate('end_date', '<=', now()->toDateString())
                    ->latest('id')
                    ->first();

                if ($pendingServiceCompletionBanner && $user->hasVerifiedPhone()) {
                    $serviceCompletionSkipsPatientOtp = $user->accountPhonesMatch(
                        $user->phone,
                        $pendingServiceCompletionBanner->patientContactPhone()
                    );
                }
            }

            $view->with('pendingReferralOtpBanner', $pendingReferralOtpBanner);
            $view->with('pendingReferralOtpContacts', $pendingReferralOtpContacts);
            $view->with('pendingRewardOtpBanner', $pendingRewardOtpBanner);
            $view->with('pendingServiceCompletionBanner', $pendingServiceCompletionBanner);
            $view->with('serviceCompletionSkipsPatientOtp', $serviceCompletionSkipsPatientOtp);
            $view->with('hasPendingContactUpdate', $hasPendingContactUpdate);
            $view->with('heldEarningsDueToUnverifiedMobile', $heldEarningsDueToUnverifiedMobile);
            $view->with('staffNeedsMobileVerification', $staffNeedsMobileVerification);
            $view->with('needsPhoneVerification', $needsPhoneVerification);
        });
    }
}

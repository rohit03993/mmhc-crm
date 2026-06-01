<?php

use App\Http\Controllers\Admin\AchievementMediaController;
use App\Http\Controllers\Admin\FeaturedTeamController;
use App\Http\Controllers\Admin\PageContentController;
use App\Http\Controllers\Admin\SiteBackupController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\StorageController;
use App\Modules\Profiles\Controllers\DocumentController;
use App\Modules\Profiles\Controllers\ProfileController;
use App\Modules\Profiles\Controllers\StaffIdCardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

// Serve storage/app/public via Laravel using a non-static path (avoids conflict with public/storage symlink directory)
Route::get('/media-file', [StorageController::class, 'show'])->name('storage.serve');

// Shared landing page data builder
$buildLandingData = function (): array {
    $pageContent = \App\Models\PageContent::getAllSections();
    $healthcarePlans = \App\Modules\Plans\Models\Plan::active()->ordered()->get();
    $achievementMedia = \App\Models\AchievementMedia::ordered()->get();
    $featuredTeam = \App\Models\FeaturedTeam::ordered()->get();
    $testimonials = \App\Models\Testimonial::ordered()->get();

    $latestCommunityPosts = collect();
    $communityPostsCount = 0;

    if (Schema::hasTable('community_posts')) {
        $latestCommunityPosts = \App\Modules\Community\Models\CommunityPost::query()
            ->with('user:id,name,role')
            ->latest()
            ->take(3)
            ->get();
        $communityPostsCount = \App\Modules\Community\Models\CommunityPost::count();
    }

    return compact(
        'pageContent',
        'healthcarePlans',
        'achievementMedia',
        'featuredTeam',
        'testimonials',
        'latestCommunityPosts',
        'communityPostsCount'
    );
};

Route::get('/', function () use ($buildLandingData) {
    return view('welcome', $buildLandingData());
});

Route::get('/landing', function () use ($buildLandingData) {
    return view('welcome', $buildLandingData());
})->name('landing');

Route::post('/webhooks/razorpay', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'razorpayWebhook'])
    ->name('webhooks.razorpay');

// Public staff ID verification (QR on physical cards)
Route::get('/verify/staff/{uniqueId}', [StaffIdCardController::class, 'verify'])
    ->name('staff.verify');

// Services module routes
Route::middleware(['auth'])->group(function () {
    // Service Routes
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [\App\Modules\Services\Controllers\ServiceController::class, 'index'])->name('index');
        Route::get('/request', [\App\Modules\Services\Controllers\ServiceController::class, 'create'])->name('create');
        Route::post('/request', [\App\Modules\Services\Controllers\ServiceController::class, 'store'])->name('store');
        Route::get('/my-requests', [\App\Modules\Services\Controllers\ServiceController::class, 'myRequests'])->name('my-requests');
        Route::get('/{serviceRequest}', [\App\Modules\Services\Controllers\ServiceController::class, 'show'])->name('show');
    });

    // Direct Booking Routes (One-Way Booking System)
    Route::prefix('book')->name('book.')->group(function () {
        Route::get('/{staff}', [\App\Modules\Services\Controllers\ServiceController::class, 'bookStaff'])->name('staff');
        Route::post('/{staff}', [\App\Modules\Services\Controllers\ServiceController::class, 'storeDirectBooking'])->name('store');
    });

    // Staff Listing - Only for Patients
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [\App\Modules\Services\Controllers\StaffController::class, 'index'])->middleware('role:patient')->name('index');
        Route::post('/resolve-location', [\App\Modules\Services\Controllers\StaffController::class, 'resolveLocation'])
            ->middleware(['role:patient', 'throttle:30,1'])
            ->name('resolve-location');

        // Staff Dashboard Routes - Only for Nurses and Caregivers
        Route::middleware('role:nurse,caregiver')->group(function () {
            Route::get('/dashboard', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'index'])->name('dashboard');
            Route::get('/service/{serviceRequest}', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'show'])->name('service-details');

            // Service action routes
            Route::post('/service/{serviceRequest}/start', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'startService'])->name('service.start');
            Route::post('/service/{serviceRequest}/completion-otp', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'sendCompletionOtp'])->name('service.completion-otp');
            Route::post('/service/{serviceRequest}/complete', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'completeService'])->name('service.complete');
            Route::post('/service/{serviceRequest}/completion-otp-banner', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'sendCompletionOtpFromBanner'])->name('service.completion-otp-banner');
            Route::post('/service/{serviceRequest}/complete-banner', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'completeServiceFromBanner'])->name('service.complete-banner');

            // Staff booking acceptance/rejection (One-Way Booking)
            Route::post('/booking/{serviceRequest}/accept', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'acceptBooking'])->name('booking.accept');
            Route::post('/booking/{serviceRequest}/reject', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'rejectBooking'])->name('booking.reject');
            Route::post('/referrals/verify-otp', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'verifyReferralOtp'])->name('referrals.verify-otp');
            Route::post('/referrals/resend-otp', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'resendReferralOtp'])->name('referrals.resend-otp');

            // Staff Earnings & Referral Routes (these will be under staff.* prefix from parent)
            Route::get('/rewards', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'rewards'])->name('rewards.index');
            Route::get('/staff-referrals', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'staffReferrals'])->name('staff-referrals.index');
            Route::get('/subscription-referrals', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'subscriptionReferrals'])->name('subscription-referrals.index');
            Route::get('/incentives', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'incentiveDetails'])->name('incentives.index');

            // Staff Payment Routes
            Route::prefix('payments')->name('payments.')->group(function () {
                Route::get('/settings', [\App\Modules\Payments\Controllers\StaffPaymentController::class, 'settings'])->name('settings');
                Route::post('/settings', [\App\Modules\Payments\Controllers\StaffPaymentController::class, 'updateSettings'])->name('settings.update');
                Route::get('/history', [\App\Modules\Payments\Controllers\StaffPaymentController::class, 'history'])->name('history');
            });
        });
    });
});

// Temporary fix - manually register Profiles module routes
Route::middleware(['auth'])->group(function () {
    // Dashboard Route - redirects staff/admin to appropriate dashboards
    Route::get('/dashboard', [\App\Modules\Auth\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Profile Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/verify-phone', [ProfileController::class, 'verifyPhone'])->name('verify-phone');
        Route::post('/verify-phone/send', [ProfileController::class, 'sendVerifyPhoneOtp'])->name('verify-phone.send');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/verify-contact-otp', [ProfileController::class, 'verifyContactUpdateOtp'])->name('verify-contact-otp');
        Route::post('/resend-contact-otp', [ProfileController::class, 'resendContactUpdateOtp'])->name('resend-contact-otp');
        Route::post('/upload-avatar', [ProfileController::class, 'uploadAvatar'])->name('upload-avatar');
        Route::get('/id-card', [StaffIdCardController::class, 'showOwn'])
            ->middleware('role:nurse,caregiver')
            ->name('id-card');
    });

    // Document Routes
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::post('/upload', [DocumentController::class, 'upload'])->name('upload');
        Route::delete('/{document}', [DocumentController::class, 'delete'])->name('delete');
        Route::get('/view/{id}', [DocumentController::class, 'view'])->name('view');
        Route::get('/download/{id}', [DocumentController::class, 'download'])->name('download');
    });

    // Plans & Subscriptions Routes (Manual registration to ensure they work)
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [\App\Modules\Plans\Controllers\PlanController::class, 'index'])->name('index');
        Route::get('/{plan}', [\App\Modules\Plans\Controllers\PlanController::class, 'show'])->name('show');
    });

    // Student journey membership (after phone verification)
    Route::prefix('student-subscription')->name('student-subscription.')->group(function () {
        Route::get('/offer', [\App\Modules\Plans\Controllers\StudentSubscriptionController::class, 'offer'])->name('offer');
        Route::post('/subscribe', [\App\Modules\Plans\Controllers\StudentSubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::post('/validate-coupon', [\App\Modules\Plans\Controllers\StudentSubscriptionController::class, 'validateCoupon'])->name('validate-coupon');
    });

    // MANUALLY REGISTER SUBSCRIPTIONS ROUTES TO ENSURE THEY WORK
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'index'])->name('index');
        Route::post('/subscribe', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::get('/payment-screenshot/{id}', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'viewPaymentScreenshot'])->name('payment-screenshot')->where('id', '[0-9]+');
        Route::get('/{subscription}/payment-confirmation', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'showPaymentConfirmation'])->name('payment-confirmation');
        Route::get('/{subscription}/invoice', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'invoice'])->name('invoice');
        Route::post('/{subscription}/razorpay/order', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'createRazorpayOrder'])->name('razorpay.order');
        Route::post('/{subscription}/razorpay/verify', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'verifyRazorpayPayment'])->name('razorpay.verify');
        Route::post('/{subscription}/apply-coupon', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'applyCoupon'])->name('apply-coupon');
        Route::post('/{subscription}/remove-coupon', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'removeCoupon'])->name('remove-coupon');
        Route::post('/{subscription}/submit-payment', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'submitPayment'])->name('submit-payment');
        Route::delete('/{subscription}', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'destroy'])->name('destroy');
        Route::post('/{subscription}/cancel', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/{subscription}/renew', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'renew'])->name('renew');
        Route::get('/{subscriptionId}', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'show'])->name('show');
    });

    // Note: Other subscription routes are loaded from app/Modules/Plans/Routes/web.php
    // The routes above are manually registered to ensure they work

    // Admin subscription management
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/subscriptions', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'adminIndex'])->name('subscriptions');
        Route::get('/subscriptions/subscriber/{user}', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'adminSubscriberDetail'])->name('subscriptions.subscriber');
        Route::get('/subscriptions/{subscription}', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'adminView'])->name('subscriptions.view');
        Route::post('/subscriptions/{subscription}/approve', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'approve'])->name('subscriptions.approve');
        Route::post('/subscriptions/{subscription}/reject', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'reject'])->name('subscriptions.reject');
        Route::post('/subscriptions/{subscription}/verify-payment', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'verifyPayment'])->name('subscriptions.verify-payment');
        Route::post('/subscriptions/{subscription}/reject-payment', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'rejectPayment'])->name('subscriptions.reject-payment');
        Route::post('/subscriptions/{subscription}/reconcile-demo-catalogue', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'adminReconcileDemoFromCatalogue'])->name('subscriptions.reconcile-demo-catalogue');
    });

    // Admin Routes for Profile Management
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Modules\Auth\Controllers\DashboardController::class, 'adminDashboard'])->name('dashboard');
        Route::get('/financial/earning/{type}', [\App\Modules\Auth\Controllers\DashboardController::class, 'earningDetail'])
            ->where('type', 'student-subscriptions|patient-subscriptions|services|services-due')
            ->name('financial.earning-detail');
        Route::get('/financial/payout/{type}', [\App\Modules\Auth\Controllers\DashboardController::class, 'payoutDetail'])
            ->where('type', 'service_request|patient_reward|staff_referral|subscription_referral')
            ->name('financial.payout-detail');
        Route::get('/pending-payments', [\App\Modules\Auth\Controllers\DashboardController::class, 'pendingPayments'])->name('pending-payments');
        Route::get('/profiles', [ProfileController::class, 'adminIndex'])->name('profiles');
        Route::get('/profiles/{user}', [ProfileController::class, 'adminView'])->name('profiles.view');
        Route::get('/staff/{user}/id-card', [StaffIdCardController::class, 'showForUser'])->name('staff.id-card');

        // Full site backup (DB + local uploads)
        Route::get('/backups', [SiteBackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [SiteBackupController::class, 'store'])->middleware('throttle:6,1')->name('backups.store');
        Route::get('/backups/{filename}/download', [SiteBackupController::class, 'download'])
            ->where('filename', 'mmhc-backup-.+\.zip')
            ->name('backups.download');
        Route::delete('/backups/{filename}', [SiteBackupController::class, 'destroy'])
            ->where('filename', 'mmhc-backup-.+\.zip')
            ->name('backups.destroy');

        // System Reset Routes (Danger Zone)
        Route::get('/system/reset', [\App\Modules\Auth\Controllers\SystemController::class, 'showResetPage'])->name('system.reset');
        Route::post('/system/reset', [\App\Modules\Auth\Controllers\SystemController::class, 'resetSystem'])->name('system.reset.store');

        // Service Management Routes
        Route::get('/service-requests', [\App\Modules\Services\Controllers\ServiceController::class, 'adminIndex'])->name('service-requests');
        Route::get('/service-requests/{serviceRequest}/assign', [\App\Modules\Services\Controllers\ServiceController::class, 'assignForm'])->name('service-requests.assign');
        Route::post('/service-requests/{serviceRequest}/assign', [\App\Modules\Services\Controllers\ServiceController::class, 'assign'])->name('service-requests.assign.post');
        Route::post('/service-requests/{serviceRequest}/record-collection', [\App\Modules\Services\Controllers\ServiceController::class, 'recordPatientCollection'])->name('service-requests.record-collection');
        Route::post('/service-requests/{serviceRequest}/approve-payment', [\App\Modules\Services\Controllers\ServiceController::class, 'approvePayment'])->name('service-requests.approve-payment');

        // Achievement & Media Coverage (landing carousel)
        Route::get('/achievement-media', [AchievementMediaController::class, 'index'])->name('achievement-media.index');
        Route::get('/achievement-media/{achievementMedia}/edit', [AchievementMediaController::class, 'edit'])->name('achievement-media.edit');
        Route::post('/achievement-media', [AchievementMediaController::class, 'store'])->name('achievement-media.store');
        Route::put('/achievement-media/{achievementMedia}', [AchievementMediaController::class, 'update'])->name('achievement-media.update');
        Route::post('/achievement-media/order', [AchievementMediaController::class, 'updateOrder'])->name('achievement-media.update-order');
        Route::post('/achievement-media/{achievementMedia}/move-up', [AchievementMediaController::class, 'moveUp'])->name('achievement-media.move-up');
        Route::post('/achievement-media/{achievementMedia}/move-down', [AchievementMediaController::class, 'moveDown'])->name('achievement-media.move-down');
        Route::delete('/achievement-media/{achievementMedia}', [AchievementMediaController::class, 'destroy'])->name('achievement-media.destroy');

        // Site settings (logo, company name, tagline, founder image)
        Route::get('/site-settings', [SiteSettingsController::class, 'index'])->name('site-settings.index');
        Route::put('/site-settings', [SiteSettingsController::class, 'update'])->name('site-settings.update');

        // Featured Team (Meet Our Expert Nursing Team)
        Route::get('/featured-team', [FeaturedTeamController::class, 'index'])->name('featured-team.index');
        Route::get('/featured-team/create', [FeaturedTeamController::class, 'create'])->name('featured-team.create');
        Route::post('/featured-team', [FeaturedTeamController::class, 'store'])->name('featured-team.store');
        Route::get('/featured-team/{featuredTeam}/edit', [FeaturedTeamController::class, 'edit'])->name('featured-team.edit');
        Route::put('/featured-team/{featuredTeam}', [FeaturedTeamController::class, 'update'])->name('featured-team.update');
        Route::post('/featured-team/{featuredTeam}/move-up', [FeaturedTeamController::class, 'moveUp'])->name('featured-team.move-up');
        Route::post('/featured-team/{featuredTeam}/move-down', [FeaturedTeamController::class, 'moveDown'])->name('featured-team.move-down');
        Route::delete('/featured-team/{featuredTeam}', [FeaturedTeamController::class, 'destroy'])->name('featured-team.destroy');

        // Testimonials (What Our Patients Say)
        Route::get('/testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
        Route::get('/testimonials/create', [TestimonialController::class, 'create'])->name('testimonials.create');
        Route::post('/testimonials', [TestimonialController::class, 'store'])->name('testimonials.store');
        Route::get('/testimonials/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('testimonials.edit');
        Route::put('/testimonials/{testimonial}', [TestimonialController::class, 'update'])->name('testimonials.update');
        Route::post('/testimonials/{testimonial}/move-up', [TestimonialController::class, 'moveUp'])->name('testimonials.move-up');
        Route::post('/testimonials/{testimonial}/move-down', [TestimonialController::class, 'moveDown'])->name('testimonials.move-down');
        Route::delete('/testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');

        // Page Content Management Routes
        Route::get('/page-content', [PageContentController::class, 'index'])->name('page-content.index');
        Route::get('/page-content/{id}/edit', [PageContentController::class, 'edit'])->name('page-content.edit');
        Route::put('/page-content/{id}', [PageContentController::class, 'update'])->name('page-content.update');

        // Healthcare Plans Management Routes (integrated with page content)
        Route::get('/page-content/plans/create', [PageContentController::class, 'createPlan'])->name('page-content.plans.create');
        Route::post('/page-content/plans', [PageContentController::class, 'storePlan'])->name('page-content.plans.store');
        Route::get('/page-content/plans/{healthcarePlan}/edit', [PageContentController::class, 'editPlan'])->name('page-content.plans.edit');
        Route::put('/page-content/plans/{healthcarePlan}', [PageContentController::class, 'updatePlan'])->name('page-content.plans.update');
        Route::delete('/page-content/plans/{healthcarePlan}', [PageContentController::class, 'deletePlan'])->name('page-content.plans.delete');

        // Referral Management Routes
        Route::prefix('referrals')->name('referrals.')->group(function () {
            Route::get('/', [\App\Modules\Referrals\Controllers\AdminReferralController::class, 'index'])->name('index');
            Route::get('/staff/{staff}', [\App\Modules\Referrals\Controllers\AdminReferralController::class, 'showStaffReferrals'])->name('staff');
        });

        // Plans Management Routes
        Route::get('/plans', [\App\Modules\Plans\Controllers\PlanController::class, 'adminIndex'])->name('plans');
        Route::get('/plans/create', [\App\Modules\Plans\Controllers\PlanController::class, 'create'])->name('plans.create');
        Route::post('/plans', [\App\Modules\Plans\Controllers\PlanController::class, 'store'])->name('plans.store');
        Route::get('/plans/{plan}/edit', [\App\Modules\Plans\Controllers\PlanController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [\App\Modules\Plans\Controllers\PlanController::class, 'update'])->name('plans.update');
        Route::delete('/plans/{plan}', [\App\Modules\Plans\Controllers\PlanController::class, 'destroy'])->name('plans.destroy');

        // Subscription Settings Routes
        Route::get('/subscription-settings', [\App\Modules\Plans\Controllers\SubscriptionSettingsController::class, 'index'])->name('subscription-settings');
        Route::put('/subscription-settings', [\App\Modules\Plans\Controllers\SubscriptionSettingsController::class, 'update'])->name('subscription-settings.update');

        Route::get('/subscription-coupons', [\App\Modules\Plans\Controllers\AdminSubscriptionCouponController::class, 'index'])->name('subscription-coupons.index');
        Route::get('/subscription-coupons/create', [\App\Modules\Plans\Controllers\AdminSubscriptionCouponController::class, 'create'])->name('subscription-coupons.create');
        Route::post('/subscription-coupons', [\App\Modules\Plans\Controllers\AdminSubscriptionCouponController::class, 'store'])->name('subscription-coupons.store');
        Route::get('/subscription-coupons/{coupon}/edit', [\App\Modules\Plans\Controllers\AdminSubscriptionCouponController::class, 'edit'])->name('subscription-coupons.edit');
        Route::put('/subscription-coupons/{coupon}', [\App\Modules\Plans\Controllers\AdminSubscriptionCouponController::class, 'update'])->name('subscription-coupons.update');
        Route::delete('/subscription-coupons/{coupon}', [\App\Modules\Plans\Controllers\AdminSubscriptionCouponController::class, 'destroy'])->name('subscription-coupons.destroy');

        // Staff Payment Management Routes
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [\App\Modules\Payments\Controllers\AdminPaymentController::class, 'index'])->name('index');
            Route::get('/history', [\App\Modules\Payments\Controllers\AdminPaymentController::class, 'history'])->name('history');
            Route::get('/staff/{staff}/form', [\App\Modules\Payments\Controllers\AdminPaymentController::class, 'showPaymentForm'])->name('form');
            Route::post('/staff/{staff}/process', [\App\Modules\Payments\Controllers\AdminPaymentController::class, 'processPayment'])->name('process');
            Route::post('/staff/{staff}/upi', [\App\Modules\Payments\Controllers\AdminPaymentController::class, 'updateStaffUpi'])->name('staff.upi.update');
        });

        // Staff incentive drill-down for admin
        Route::get('/staff/{staff}/incentives', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'incentiveDetails'])
            ->name('staff.incentives');
    });
});

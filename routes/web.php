<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Profiles\Controllers\DocumentController;
use App\Modules\Profiles\Controllers\ProfileController;
use App\Http\Controllers\Admin\PageContentController;

Route::get('/', function () {
    // Get page content for dynamic rendering
    $pageContent = \App\Models\PageContent::getAllSections();
    // Use new Plan model for subscription plans
    $healthcarePlans = \App\Modules\Plans\Models\Plan::active()->ordered()->get();
    return view('welcome', compact('pageContent', 'healthcarePlans'));
});

// Landing page route (always accessible)
Route::get('/landing', function () {
    $pageContent = \App\Models\PageContent::getAllSections();
    // Use new Plan model for subscription plans
    $healthcarePlans = \App\Modules\Plans\Models\Plan::active()->ordered()->get();
    return view('welcome', compact('pageContent', 'healthcarePlans'));
})->name('landing');

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
        
        // Staff Dashboard Routes - Only for Nurses and Caregivers
        Route::middleware('role:nurse,caregiver')->group(function () {
            Route::get('/dashboard', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'index'])->name('dashboard');
            Route::get('/service/{serviceRequest}', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'show'])->name('service-details');
            
            // Service action routes
            Route::post('/service/{serviceRequest}/start', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'startService'])->name('service.start');
            Route::post('/service/{serviceRequest}/complete', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'completeService'])->name('service.complete');
            
            // Staff booking acceptance/rejection (One-Way Booking)
            Route::post('/booking/{serviceRequest}/accept', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'acceptBooking'])->name('booking.accept');
            Route::post('/booking/{serviceRequest}/reject', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'rejectBooking'])->name('booking.reject');
            
            // Staff Earnings & Referral Routes (these will be under staff.* prefix from parent)
            Route::get('/rewards', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'rewards'])->name('rewards.index');
            Route::get('/staff-referrals', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'staffReferrals'])->name('staff-referrals.index');
            Route::get('/subscription-referrals', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'subscriptionReferrals'])->name('subscription-referrals.index');
            
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
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/upload-avatar', [ProfileController::class, 'uploadAvatar'])->name('upload-avatar');
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
    
    // MANUALLY REGISTER SUBSCRIPTIONS ROUTES TO ENSURE THEY WORK
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'index'])->name('index');
        Route::post('/subscribe', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::get('/payment-screenshot/{id}', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'viewPaymentScreenshot'])->name('payment-screenshot')->where('id', '[0-9]+');
        Route::get('/{subscription}/payment-confirmation', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'showPaymentConfirmation'])->name('payment-confirmation');
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
        Route::get('/subscriptions/{subscription}', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'adminView'])->name('subscriptions.view');
        Route::post('/subscriptions/{subscription}/approve', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'approve'])->name('subscriptions.approve');
        Route::post('/subscriptions/{subscription}/reject', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'reject'])->name('subscriptions.reject');
        Route::post('/subscriptions/{subscription}/verify-payment', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'verifyPayment'])->name('subscriptions.verify-payment');
        Route::post('/subscriptions/{subscription}/reject-payment', [\App\Modules\Plans\Controllers\SubscriptionController::class, 'rejectPayment'])->name('subscriptions.reject-payment');
    });
    
    // Admin Routes for Profile Management
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Modules\Auth\Controllers\DashboardController::class, 'adminDashboard'])->name('dashboard');
        Route::get('/pending-payments', [\App\Modules\Auth\Controllers\DashboardController::class, 'pendingPayments'])->name('pending-payments');
        Route::get('/profiles', [ProfileController::class, 'adminIndex'])->name('profiles');
        Route::get('/profiles/{user}', [ProfileController::class, 'adminView'])->name('profiles.view');
        
        // Service Management Routes
        Route::get('/service-requests', [\App\Modules\Services\Controllers\ServiceController::class, 'adminIndex'])->name('service-requests');
        Route::get('/service-requests/{serviceRequest}/assign', [\App\Modules\Services\Controllers\ServiceController::class, 'assignForm'])->name('service-requests.assign');
        Route::post('/service-requests/{serviceRequest}/assign', [\App\Modules\Services\Controllers\ServiceController::class, 'assign'])->name('service-requests.assign.post');
        Route::post('/service-requests/{serviceRequest}/approve-payment', [\App\Modules\Services\Controllers\ServiceController::class, 'approvePayment'])->name('service-requests.approve-payment');
        
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
        
        // Staff Payment Management Routes
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [\App\Modules\Payments\Controllers\AdminPaymentController::class, 'index'])->name('index');
            Route::get('/history', [\App\Modules\Payments\Controllers\AdminPaymentController::class, 'history'])->name('history');
            Route::get('/staff/{staff}/form', [\App\Modules\Payments\Controllers\AdminPaymentController::class, 'showPaymentForm'])->name('form');
            Route::post('/staff/{staff}/process', [\App\Modules\Payments\Controllers\AdminPaymentController::class, 'processPayment'])->name('process');
        });
    });
});

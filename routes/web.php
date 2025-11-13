<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Profiles\Controllers\DocumentController;
use App\Modules\Profiles\Controllers\ProfileController;
use App\Http\Controllers\Admin\PageContentController;

Route::get('/', function () {
    // Get page content for dynamic rendering
    $pageContent = \App\Models\PageContent::getAllSections();
    $healthcarePlans = \App\Models\HealthcarePlan::getActivePlans();
    return view('welcome', compact('pageContent', 'healthcarePlans'));
});

// Landing page route (always accessible)
Route::get('/landing', function () {
    $pageContent = \App\Models\PageContent::getAllSections();
    $healthcarePlans = \App\Models\HealthcarePlan::getActivePlans();
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
    
    // Staff Routes
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [\App\Modules\Services\Controllers\StaffController::class, 'index'])->name('index');
        Route::get('/dashboard', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'index'])->name('dashboard');
        Route::get('/service/{serviceRequest}', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'show'])->name('service-details');
        
        // Service action routes
        Route::post('/service/{serviceRequest}/start', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'startService'])->name('service.start');
        Route::post('/service/{serviceRequest}/complete', [\App\Modules\Services\Controllers\StaffDashboardController::class, 'completeService'])->name('service.complete');
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
        Route::get('/download/{document}', [DocumentController::class, 'download'])->name('download');
    });
    
    // Admin Routes for Profile Management
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Modules\Auth\Controllers\DashboardController::class, 'adminDashboard'])->name('dashboard');
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
    });
});

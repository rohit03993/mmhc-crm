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

// Temporary fix - manually register Profiles module routes
Route::middleware(['auth'])->group(function () {
    
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
        Route::get('/profiles', [ProfileController::class, 'adminIndex'])->name('profiles');
        Route::get('/profiles/{user}', [ProfileController::class, 'adminView'])->name('profiles.view');
        
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
    });
});

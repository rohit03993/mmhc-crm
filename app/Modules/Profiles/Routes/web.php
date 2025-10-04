<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Profiles\Controllers\ProfileController;
use App\Modules\Profiles\Controllers\DocumentController;

/*
|--------------------------------------------------------------------------
| Profiles Module Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Profiles module. These routes are
| loaded by the ModuleServiceProvider and are isolated to this module.
|
*/

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
    });
});

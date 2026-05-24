<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Profiles\Controllers\ProfileController;
use App\Modules\Profiles\Controllers\DocumentController;
use App\Modules\Profiles\Controllers\StaffIdCardController;

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
    
    // Admin Routes for Profile Management
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/profiles', [ProfileController::class, 'adminIndex'])->name('profiles');
        Route::get('/profiles/{user}', [ProfileController::class, 'adminView'])->name('profiles.view');
        Route::get('/staff/{user}/id-card', [StaffIdCardController::class, 'showForUser'])->name('staff.id-card');
    });
});

<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Services\Controllers\ServiceController;

/*
|--------------------------------------------------------------------------
| Services Module Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Services module. These routes handle
| service requests, assignments, and daily service tracking.
|
*/

Route::middleware(['auth'])->group(function () {
    
    // Service Routes (Patient access)
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index');
        Route::get('/request', [ServiceController::class, 'create'])->name('create');
        Route::post('/request', [ServiceController::class, 'store'])->name('store');
        Route::get('/my-requests', [ServiceController::class, 'myRequests'])->name('my-requests');
        Route::get('/{serviceRequest}', [ServiceController::class, 'show'])->name('show');
    });
    
    // Admin Routes (Admin access)
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/service-requests', [ServiceController::class, 'adminIndex'])->name('service-requests');
        Route::get('/service-requests/{serviceRequest}/assign', [ServiceController::class, 'assignForm'])->name('service-requests.assign');
        Route::post('/service-requests/{serviceRequest}/assign', [ServiceController::class, 'assign'])->name('service-requests.assign.post');
    });
});

<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Plans\Controllers\PlanController;
use App\Modules\Plans\Controllers\SubscriptionController;
use App\Modules\Plans\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Plans Module Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Plans, Subscriptions, and Payments module.
| These routes are loaded by the ModuleServiceProvider and are isolated.
|
*/

Route::middleware(['auth'])->group(function () {
    
    // Plans Routes (Public viewing for patients)
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [PlanController::class, 'index'])->name('index');
        Route::get('/{plan}', [PlanController::class, 'show'])->name('show');
    });
    
    // Subscription Routes
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::get('/{subscription}', [SubscriptionController::class, 'show'])->name('show');
        Route::post('/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('renew');
    });
    
    // Payment Routes
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::post('/process', [PaymentController::class, 'process'])->name('process');
        Route::get('/success', [PaymentController::class, 'success'])->name('success');
        Route::get('/failure', [PaymentController::class, 'failure'])->name('failure');
        Route::get('/{payment}/invoice', [PaymentController::class, 'invoice'])->name('invoice');
        Route::get('/{payment}/receipt', [PaymentController::class, 'receipt'])->name('receipt');
    });
    
    // Admin Routes for Plans Management
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Plans Management
        Route::get('/plans', [PlanController::class, 'adminIndex'])->name('plans');
        Route::get('/plans/create', [PlanController::class, 'create'])->name('plans.create');
        Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
        Route::get('/plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
        Route::delete('/plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');
        
        // Subscriptions Management
        Route::get('/subscriptions', [SubscriptionController::class, 'adminIndex'])->name('subscriptions');
        Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'adminView'])->name('subscriptions.view');
        Route::post('/subscriptions/{subscription}/approve', [SubscriptionController::class, 'approve'])->name('subscriptions.approve');
        Route::post('/subscriptions/{subscription}/reject', [SubscriptionController::class, 'reject'])->name('subscriptions.reject');
        
        // Payments Management
        Route::get('/payments', [PaymentController::class, 'adminIndex'])->name('payments');
        Route::get('/payments/{payment}', [PaymentController::class, 'adminView'])->name('payments.view');
        Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
    });
});

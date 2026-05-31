<?php

use App\Modules\Plans\Controllers\PaymentController;
use App\Modules\Plans\Controllers\PlanController;
use App\Modules\Plans\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

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

        // Payment screenshot route - separate to avoid conflicts
        Route::get('/payment-screenshot/{id}', [SubscriptionController::class, 'viewPaymentScreenshot'])->name('payment-screenshot')->where('id', '[0-9]+');

        // IMPORTANT: More specific routes MUST come before the generic {subscription} route
        Route::get('/{subscription}/payment-confirmation', [SubscriptionController::class, 'showPaymentConfirmation'])->name('payment-confirmation');
        Route::get('/{subscription}/invoice', [SubscriptionController::class, 'invoice'])->name('invoice');
        Route::post('/{subscription}/razorpay/order', [SubscriptionController::class, 'createRazorpayOrder'])->name('razorpay.order');
        Route::post('/{subscription}/razorpay/verify', [SubscriptionController::class, 'verifyRazorpayPayment'])->name('razorpay.verify');
        Route::post('/{subscription}/apply-coupon', [SubscriptionController::class, 'applyCoupon'])->name('apply-coupon');
        Route::post('/{subscription}/remove-coupon', [SubscriptionController::class, 'removeCoupon'])->name('remove-coupon');
        Route::post('/{subscription}/submit-payment', [SubscriptionController::class, 'submitPayment'])->name('submit-payment');
        Route::delete('/{subscription}', [SubscriptionController::class, 'destroy'])->name('destroy');
        Route::post('/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('renew');

        // Generic route MUST come last - otherwise it will catch all requests
        // Note: We use a different parameter name to avoid automatic route model binding
        // The controller will handle finding the subscription by ID
        Route::get('/{subscriptionId}', [SubscriptionController::class, 'show'])->name('show');
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
        Route::post('/subscriptions/{subscription}/verify-payment', [SubscriptionController::class, 'verifyPayment'])->name('subscriptions.verify-payment');
        Route::post('/subscriptions/{subscription}/reject-payment', [SubscriptionController::class, 'rejectPayment'])->name('subscriptions.reject-payment');
        Route::post('/subscriptions/{subscription}/reconcile-demo-catalogue', [SubscriptionController::class, 'adminReconcileDemoFromCatalogue'])->name('subscriptions.reconcile-demo-catalogue');

        // Plan payment records (avoid collision with Staff Payments module routes)
        Route::get('/plan-payments', [PaymentController::class, 'adminIndex'])->name('plan-payments');
        Route::get('/plan-payments/{payment}', [PaymentController::class, 'adminView'])->name('plan-payments.view');
        Route::post('/plan-payments/{payment}/refund', [PaymentController::class, 'refund'])->name('plan-payments.refund');
    });
});

Route::post('/webhooks/razorpay', [SubscriptionController::class, 'razorpayWebhook'])
    ->name('webhooks.razorpay');

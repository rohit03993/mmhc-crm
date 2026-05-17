<?php

use App\Modules\Rewards\Controllers\RewardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'role:caregiver,nurse'])
    ->prefix('rewards')
    ->name('rewards.')
    ->group(function () {
        Route::get('/', [RewardController::class, 'index'])->name('index');
        Route::get('/create', [RewardController::class, 'create'])->name('create');
        Route::post('/', [RewardController::class, 'store'])->name('store');
        Route::post('/{reward}/send-otp', [RewardController::class, 'sendOtp'])->name('send-otp');
        Route::post('/{reward}/verify-otp', [RewardController::class, 'verifyOtp'])->name('verify-otp');
        Route::post('/{reward}/update-patient-phone', [RewardController::class, 'updatePatientPhone'])->name('update-patient-phone');
        Route::post('/{reward}/send-otp-banner', [RewardController::class, 'resendOtpFromBanner'])->name('send-otp-banner');
        Route::post('/{reward}/verify-otp-banner', [RewardController::class, 'verifyOtpFromBanner'])->name('verify-otp-banner');
    });

Route::middleware(['web', 'auth', 'role:admin'])
    ->prefix('admin/rewards')
    ->name('admin.rewards.')
    ->group(function () {
        Route::get('/', [RewardController::class, 'adminIndex'])->name('index');
        Route::get('/staff/{staff}', [RewardController::class, 'adminStaffDetail'])->name('staff');
    });

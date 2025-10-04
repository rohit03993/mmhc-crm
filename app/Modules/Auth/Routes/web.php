<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Auth\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Auth Module Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Authentication module. These routes are
| loaded by the ModuleServiceProvider and are isolated to this module.
|
*/

Route::middleware(['web'])->group(function () {
    
    // Authentication Routes
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.post');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
    
    // Route aliases for seamless authentication
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    
    // Dashboard Routes (Protected)
    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/', function () {
            return redirect()->route('dashboard');
        });
    });
    
    // Admin Routes (Protected)
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        Route::get('/users', [AuthController::class, 'manageUsers'])->name('users');
        Route::post('/users', [AuthController::class, 'storeUser'])->name('users.store');
    });
});

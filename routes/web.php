<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::post('/details', [AccountController::class, 'updateDetails'])->name('update.details');
        Route::post('/password', [AccountController::class, 'updatePassword'])->name('update.password');
    });

    Route::middleware('admin')->group(function () {
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'index'])->name('index');
            Route::post('/details', [ProfileController::class, 'updateDetails'])->name('update.details');
            Route::post('/password', [ProfileController::class, 'updatePassword'])->name('update.password');
            Route::post('/logo', [ProfileController::class, 'updateLogo'])->name('update.logo');
        });

        Route::resource('users', UsersController::class)->except(['show']);
    });
});

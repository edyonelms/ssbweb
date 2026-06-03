<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AnnouncementsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupportController;
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

    // Listing is open to admin (CRUD view) and subadmin (received view).
    Route::get('/announcements', [AnnouncementsController::class, 'index'])->name('announcements.index');

    // Support — open to everyone; admin sees all, subadmin sees own.
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/support', [SupportController::class, 'store'])->name('support.store');
    Route::middleware('admin')->group(function () {
        Route::post('/support/{query}/reply', [SupportController::class, 'reply'])
            ->whereNumber('query')->name('support.reply');
    });

    Route::middleware('admin')->group(function () {
        Route::post('/announcements', [AnnouncementsController::class, 'store'])->name('announcements.store');
        Route::put('/announcements/{announcement}', [AnnouncementsController::class, 'update'])
            ->whereNumber('announcement')->name('announcements.update');
        Route::delete('/announcements/{announcement}', [AnnouncementsController::class, 'destroy'])
            ->whereNumber('announcement')->name('announcements.destroy');
    });

    Route::middleware('admin')->group(function () {
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'index'])->name('index');
            Route::post('/details', [ProfileController::class, 'updateDetails'])->name('update.details');
            Route::post('/password', [ProfileController::class, 'updatePassword'])->name('update.password');
            Route::post('/logo', [ProfileController::class, 'updateLogo'])->name('update.logo');
        });

        // Old links (e.g. /users/create, /users/{id}/edit) now open the slide-in
        // panel on the listing instead of taking the user to a dead URL.
        Route::redirect('/users/create', '/users?panel=create');
        Route::get('/users/{user}/edit', fn ($user) => redirect('/users?panel=edit&id='.$user))
            ->whereNumber('user');

        Route::resource('users', UsersController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->whereNumber('user');
    });
});

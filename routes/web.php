<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AnnouncementsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('welcome');

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

    // Students — admin sees every record, sub-admin only sees their own.
    Route::get('/students/export', [StudentsController::class, 'export'])->name('students.export');
    Route::resource('students', StudentsController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->whereNumber('student');

    // Master Data — admin manages universities/courses/fees, sub-admin reads only.
    Route::prefix('master-data')->name('master.')->group(function () {
        Route::get('/', [MasterDataController::class, 'index'])->name('index');

        Route::middleware('admin')->group(function () {
            Route::post('/universities',                [MasterDataController::class, 'storeUniversity'])->name('universities.store');
            Route::put('/universities/{university}',    [MasterDataController::class, 'updateUniversity'])->whereNumber('university')->name('universities.update');
            Route::delete('/universities/{university}', [MasterDataController::class, 'destroyUniversity'])->whereNumber('university')->name('universities.destroy');

            Route::post('/courses',           [MasterDataController::class, 'storeCourse'])->name('courses.store');
            Route::put('/courses/{course}',   [MasterDataController::class, 'updateCourse'])->whereNumber('course')->name('courses.update');
            Route::delete('/courses/{course}',[MasterDataController::class, 'destroyCourse'])->whereNumber('course')->name('courses.destroy');

            Route::post('/fees',          [MasterDataController::class, 'storeFee'])->name('fees.store');
            Route::put('/fees/{fee}',     [MasterDataController::class, 'updateFee'])->whereNumber('fee')->name('fees.update');
            Route::delete('/fees/{fee}',  [MasterDataController::class, 'destroyFee'])->whereNumber('fee')->name('fees.destroy');
        });
    });

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

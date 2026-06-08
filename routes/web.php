<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AnnouncementsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnquiriesController;
use App\Http\Controllers\FeeCalculatorController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\PayFeeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────
//  Marketing site
//
//  In production the public website lives on the bare domain
//  ssbeducation.in. Locally we expose it under /marketing so devs can
//  still hit it without setting up host aliases. Route names stay the
//  same (`marketing.home`, `marketing.enquiry`) in both environments so
//  view code doesn't have to care.
// ─────────────────────────────────────────────────────────────────────

if (app()->environment('production')) {
    Route::domain('ssbeducation.in')->group(function () {
        Route::get('/',         [MarketingController::class, 'home'])->name('marketing.home');
        Route::post('/enquiry', [MarketingController::class, 'storeEnquiry'])->name('marketing.enquiry');
    });
    Route::domain('www.ssbeducation.in')->group(function () {
        Route::get('/',         [MarketingController::class, 'home']);
        Route::post('/enquiry', [MarketingController::class, 'storeEnquiry']);
    });
} else {
    Route::prefix('marketing')->name('marketing.')->group(function () {
        Route::get('/',         [MarketingController::class, 'home'])->name('home');
        Route::post('/enquiry', [MarketingController::class, 'storeEnquiry'])->name('enquiry');
    });
}

// ─────────────────────────────────────────────────────────────────────
//  App (login + dashboard + everything else)
//
//  App routes share the same apex host as the marketing site, so
//  they're registered without a domain constraint. The apex marketing
//  routes above are evaluated first and win at `ssbeducation.in/`,
//  leaving every other path (e.g. /login, /dashboard) to fall through
//  here and be served on the same domain.
// ─────────────────────────────────────────────────────────────────────

// Non-apex hosts (localhost, www.* before DNS, etc.) don't have a
// marketing handler at `/`, so bounce them onto /login so something
// always renders at the root.
Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    // /login is the splash screen — campus image full-bleed + a
    // "Continue to Login" button. Anything that hits route('login')
    // (direct URL, marketing CTA, the auth redirect for unauthenticated
    // pages) lands here first.
    Route::get('/login', fn () => view('welcome'))->name('login');

    // The actual sign-in form lives at /signin; the splash button
    // takes the user here, and the form posts back to /login.
    Route::get('/signin', [AuthController::class, 'showLogin'])->name('login.form');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Recent Activity bell — mark the topbar feed as read for the
    // current user. Fired from the bell button's click handler.
    Route::post('/activities/seen', [ActivityController::class, 'markSeen'])
        ->name('activities.seen');

    // Per-user dismissal of feed entries (multi-select delete in the
    // bell panel). Uses a soft-hide pivot so other users keep their copy.
    Route::post('/activities/destroy', [ActivityController::class, 'destroy'])
        ->name('activities.destroy');

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::post('/details', [AccountController::class, 'updateDetails'])->name('update.details');
        Route::post('/password', [AccountController::class, 'updatePassword'])->name('update.password');
    });

    // Listing is open to admin (CRUD view) and subadmin (received view).
    Route::get('/announcements', [AnnouncementsController::class, 'index'])->name('announcements.index');

    // Subadmin-side soft delete — hides the announcement from their list
    // only; admin and other subadmins keep seeing it.
    Route::delete('/announcements/{announcement}/hide', [AnnouncementsController::class, 'hide'])
        ->whereNumber('announcement')->name('announcements.hide');

    // Students — admin sees every record, sub-admin only sees their own.
    Route::get('/students/export', [StudentsController::class, 'export'])->name('students.export');
    Route::get('/students/{student}/form', [StudentsController::class, 'form'])
        ->whereNumber('student')->name('students.form');
    Route::resource('students', StudentsController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->whereNumber('student');

    // Fee Calculator — both roles get the same client-side calculator.
    Route::get('/fee-calculator', [FeeCalculatorController::class, 'index'])->name('fee-calculator');

    // Pay Fee — board/university + student picker, with split-by-semester
    // payment posting. Open to admin and sub-admin (sub-admin scoped to
    // their own students inside the controller).
    Route::prefix('pay-fee')->name('pay-fee.')->group(function () {
        Route::get('/', [PayFeeController::class, 'index'])->name('index');
        Route::post('/', [PayFeeController::class, 'store'])->name('store');
        Route::delete('/payments/{feePayment}', [PayFeeController::class, 'destroy'])
            ->whereNumber('feePayment')->name('payments.destroy');
    });

    // Wallet — both roles see the listing; admin alone may update wallets.
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::middleware('admin')->group(function () {
        Route::post('/wallet', [WalletController::class, 'store'])->name('wallet.store');
    });

    // Payment requests ("Ask Payment") — anyone can ask, admin decides.
    Route::post('/wallet/requests', [WalletController::class, 'storeRequest'])
        ->name('wallet.requests.store');
    Route::delete('/wallet/requests/{paymentRequest}', [WalletController::class, 'destroyRequest'])
        ->whereNumber('paymentRequest')->name('wallet.requests.destroy');
    Route::middleware('admin')->group(function () {
        Route::post('/wallet/requests/{paymentRequest}/approve', [WalletController::class, 'approveRequest'])
            ->whereNumber('paymentRequest')->name('wallet.requests.approve');
        Route::post('/wallet/requests/{paymentRequest}/reject', [WalletController::class, 'rejectRequest'])
            ->whereNumber('paymentRequest')->name('wallet.requests.reject');
    });

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

            // Bump the current semester / year for one course (or every
            // course at once) — every enrolled student also moves up,
            // clamped by the course's total period count.
            Route::post('/upgrade-semester', [MasterDataController::class, 'upgradeSemester'])
                ->name('upgrade.semester');
        });
    });

    // Support — open to everyone; admin sees all, subadmin sees own.
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/support', [SupportController::class, 'store'])->name('support.store');
    Route::put('/support/{query}', [SupportController::class, 'update'])
        ->whereNumber('query')->name('support.update');
    Route::delete('/support/{query}', [SupportController::class, 'destroy'])
        ->whereNumber('query')->name('support.destroy');
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

    // Enquiries — admin reviews the leads captured by the marketing form.
    Route::middleware('admin')->group(function () {
        Route::get('/enquiries', [EnquiriesController::class, 'index'])->name('enquiries.index');
        Route::put('/enquiries/{enquiry}', [EnquiriesController::class, 'update'])
            ->whereNumber('enquiry')->name('enquiries.update');
        Route::delete('/enquiries/{enquiry}', [EnquiriesController::class, 'destroy'])
            ->whereNumber('enquiry')->name('enquiries.destroy');
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

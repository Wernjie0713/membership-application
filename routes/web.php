<?php

use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberPortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\RewardReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');

    Route::prefix('member')->name('member.')->middleware('can:complete-member-profile')->group(function () {
        Route::get('/onboarding', [MemberPortalController::class, 'onboarding'])->name('onboarding.create');
        Route::post('/onboarding', [MemberPortalController::class, 'storeOnboarding'])->name('onboarding.store');
        Route::get('/profile', [MemberPortalController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [MemberPortalController::class, 'update'])->name('profile.update');

        Route::middleware(['verified', 'can:access-member-portal'])->group(function () {
            Route::get('/dashboard', [MemberPortalController::class, 'dashboard'])->name('dashboard');
        });
    });
});

Route::middleware(['auth', 'verified', 'can:view-admin-dashboard'])->group(function () {
    Route::get('/admin/dashboard', DashboardController::class)->name('admin.dashboard');
});

Route::middleware(['auth', 'verified', 'can:manage-members'])->group(function () {
    Route::get('/members-export', [MemberController::class, 'export'])->name('members.export');
    Route::resource('members', MemberController::class);
});

Route::middleware(['auth', 'verified', 'can:manage-promotions'])->group(function () {
    Route::resource('promotions', PromotionController::class);
});

Route::middleware(['auth', 'verified', 'can:view-reward-reports'])->group(function () {
    Route::get('/reward-report', [RewardReportController::class, 'index'])->name('rewards.index');
});

Route::middleware(['auth', 'verified', 'can:export-rewards'])->group(function () {
    Route::get('/reward-report/export', [RewardReportController::class, 'export'])->name('rewards.export');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

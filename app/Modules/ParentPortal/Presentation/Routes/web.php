<?php

use App\Modules\ParentPortal\Presentation\Controllers\ParentAuthController;
use App\Modules\ParentPortal\Presentation\Controllers\ParentChildController;
use App\Modules\ParentPortal\Presentation\Controllers\ParentDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('parent')->name('parent.')->group(function () {
    Route::middleware('guest:parent')->group(function () {
        Route::get('/login', [ParentAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [ParentAuthController::class, 'login'])->name('login.submit');
        Route::get('/inscription', [ParentAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/inscription', [ParentAuthController::class, 'register'])->middleware('throttle:10,1')->name('register.submit');
    });

    Route::middleware('auth:parent')->group(function () {
        Route::post('/logout', [ParentAuthController::class, 'logout'])->name('logout');

        Route::get('/ajouter-enfant', [ParentChildController::class, 'showAddChildForm'])->name('children.add-form');
        Route::post('/ajouter-enfant', [ParentChildController::class, 'addChild'])->middleware('throttle:5,1')->name('children.add');

        Route::get('/', [ParentDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/{student}/bulletin', [ParentDashboardController::class, 'bulletin'])->name('bulletin')->whereNumber('student');
        Route::get('/{student}/presence', [ParentDashboardController::class, 'attendance'])->name('attendance')->whereNumber('student');
        Route::get('/{student}/devoirs', [ParentDashboardController::class, 'homework'])->name('homework')->whereNumber('student');
        Route::get('/{student}/frais', [ParentDashboardController::class, 'fees'])->name('fees')->whereNumber('student');
        Route::get('/{student}/cantine', [ParentDashboardController::class, 'canteen'])->name('canteen')->whereNumber('student');
        Route::get('/{student}/transport', [ParentDashboardController::class, 'transport'])->name('transport')->whereNumber('student');
    });
});

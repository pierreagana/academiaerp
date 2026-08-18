<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingPageController;

use App\Http\Controllers\Auth\AuthController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('school.dashboard');
    }
    return app(LandingPageController::class)->index();
})->name('landing');

Route::get('/landing', [LandingPageController::class, 'index'])->name('landing.public');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Default fallback for RedirectIfAuthenticated (guest middleware)
Route::get('/home', function () {
    return redirect()->route('school.dashboard');
})->name('home');

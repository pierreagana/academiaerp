<?php

use App\Modules\ParentPortal\Presentation\Controllers\NotificationController;
use App\Modules\ParentPortal\Presentation\Controllers\ParentAuthController;
use App\Modules\ParentPortal\Presentation\Controllers\ParentChildController;
use App\Modules\ParentPortal\Presentation\Controllers\ParentDashboardController;
use App\Modules\ParentPortal\Presentation\Controllers\SchoolTrackWebController;
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
        Route::get('/academique', [ParentDashboardController::class, 'academic'])->name('academic');
        Route::get('/finance', [ParentDashboardController::class, 'finance'])->name('finance');
        Route::get('/services', [ParentDashboardController::class, 'services'])->name('services');
        Route::get('/infirmerie', [ParentDashboardController::class, 'infirmary'])->name('infirmary');
        Route::get('/acces-scolaire', [ParentDashboardController::class, 'schoolAccess'])->name('school-access');
        Route::get('/parametres', [ParentDashboardController::class, 'settings'])->name('settings');
        Route::post('/parametres', [ParentDashboardController::class, 'updateSettings'])->name('settings.update');
        Route::post('/parametres/mot-de-passe', [ParentDashboardController::class, 'updatePassword'])->name('settings.password');
        Route::get('/parametres/adresse/recherche', [ParentDashboardController::class, 'searchAddress'])->middleware('throttle:20,1')->name('settings.address.search');
        Route::post('/parametres/documents-legaux/{legalDocument}/signer', [ParentDashboardController::class, 'signLegalDocument'])->name('legal-documents.sign')->whereNumber('legalDocument');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::post('/notifications/{notification}/lu', [NotificationController::class, 'markRead'])->name('notifications.read')->whereNumber('notification');
        Route::post('/notifications/tout-lire', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        // School Track
        Route::prefix('school-track')->name('school-track.')->group(function () {
            Route::get('/', [SchoolTrackWebController::class, 'index'])->name('index');
            Route::get('/comparateur', [SchoolTrackWebController::class, 'compare'])->name('compare');
            Route::get('/carte', [SchoolTrackWebController::class, 'map'])->name('map');
            Route::post('/comparateur/toggle', [SchoolTrackWebController::class, 'toggleCompare'])->name('compare.toggle');
            Route::post('/souscrire', [SchoolTrackWebController::class, 'subscribe'])->middleware('throttle:10,1')->name('subscribe');
            Route::get('/{id}', [SchoolTrackWebController::class, 'show'])->name('show');
        });
        Route::get('/{student}/carte-scolaire', [ParentDashboardController::class, 'card'])->name('card')->whereNumber('student');
        Route::get('/{student}/bulletin', [ParentDashboardController::class, 'bulletin'])->name('bulletin')->whereNumber('student');
        Route::get('/{student}/diplomes', [ParentDashboardController::class, 'diplomas'])->name('diplomes')->whereNumber('student');
        Route::get('/{student}/diplomes/{award}/imprimer', [ParentDashboardController::class, 'printDiploma'])->name('diplomes.print')->whereNumber('student')->whereNumber('award');
        Route::get('/{student}/presence', [ParentDashboardController::class, 'attendance'])->name('attendance')->whereNumber('student');
        Route::get('/{student}/devoirs', [ParentDashboardController::class, 'homework'])->name('homework')->whereNumber('student');
        Route::get('/{student}/frais', [ParentDashboardController::class, 'fees'])->name('fees')->whereNumber('student');
        Route::get('/{student}/cantine', [ParentDashboardController::class, 'canteen'])->name('canteen')->whereNumber('student');
        Route::post('/{student}/cantine/demande', [ParentDashboardController::class, 'requestCanteenEnrollment'])->name('canteen.request')->whereNumber('student');
        Route::get('/{student}/transport', [ParentDashboardController::class, 'transport'])->name('transport')->whereNumber('student');
        Route::post('/{student}/transport/demande', [ParentDashboardController::class, 'requestTransportEnrollment'])->name('transport.request')->whereNumber('student');
    });
});

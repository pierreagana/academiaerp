<?php

use App\Modules\ParentPortal\Presentation\Controllers\Api\MobileParentController;
use App\Modules\ParentPortal\Presentation\Controllers\Api\ParentAuthController;
use App\Modules\ParentPortal\Presentation\Controllers\Api\ParentController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// The auto-registered `broadcasting/auth` route (from routes/channels.php) only
// carries the `web` middleware, which can't authenticate the Flutter app's
// Sanctum bearer tokens. This mirrors it under `auth:sanctum` for mobile use.
Broadcast::routes(['middleware' => ['auth:sanctum']]);

// Matches the Flutter app's mock endpoints (mock_api_client.dart) field-for-field —
// see MobileParentController's docblock. Same auth:sanctum guard as /parent/*.
Route::prefix('v1')->name('api.v1.')->middleware('auth:sanctum')->group(function () {
    Route::get('/home', [MobileParentController::class, 'home'])->name('home');
    Route::get('/attendance', [MobileParentController::class, 'attendance'])->name('attendance');
    Route::get('/schedule', [MobileParentController::class, 'schedule'])->name('schedule');
    Route::get('/courses/overview', [MobileParentController::class, 'coursesOverview'])->name('courses.overview');
    Route::get('/courses/{courseId}', [MobileParentController::class, 'courseDetail'])->name('courses.detail')->whereNumber('courseId');
    Route::get('/fees', [MobileParentController::class, 'fees'])->name('fees');
    Route::get('/canteen', [MobileParentController::class, 'canteen'])->name('canteen');
    Route::post('/canteen/reservation', [MobileParentController::class, 'confirmCanteenOrder'])->name('canteen.reservation');
    Route::get('/canteen/history', [MobileParentController::class, 'canteenHistory'])->name('canteen.history');
    Route::get('/transport', [MobileParentController::class, 'transport'])->name('transport');
    Route::get('/transport/stops', [MobileParentController::class, 'transportStops'])->name('transport.stops');
    Route::put('/transport/stop', [MobileParentController::class, 'updateTransportStop'])->name('transport.stop.update');
    Route::get('/transport/history', [MobileParentController::class, 'transportHistory'])->name('transport.history');
    Route::get('/infirmary', [MobileParentController::class, 'infirmary'])->name('infirmary');
    Route::get('/access', [MobileParentController::class, 'access'])->name('access');
    Route::get('/access/history', [MobileParentController::class, 'accessHistory'])->name('access.history');
    Route::get('/students/{studentId}', [MobileParentController::class, 'studentProfile'])->name('students.profile')->whereNumber('studentId');
    Route::get('/students/{studentId}/activity', [MobileParentController::class, 'studentActivityHistory'])->name('students.activity')->whereNumber('studentId');
    Route::get('/events', [MobileParentController::class, 'events'])->name('events');
    Route::get('/notifications', [MobileParentController::class, 'notifications'])->name('notifications');
});

Route::prefix('parent')->name('api.parent.')->group(function () {
    Route::post('/register', [ParentAuthController::class, 'register'])->middleware('throttle:10,1')->name('register');
    Route::post('/login', [ParentAuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [ParentAuthController::class, 'logout'])->name('logout');
        Route::post('/children/claim', [ParentController::class, 'addChild'])->middleware('throttle:5,1')->name('children.claim');

        Route::get('/children', [ParentController::class, 'children'])->name('children');
        Route::get('/children/{student}/bulletin', [ParentController::class, 'bulletin'])->name('bulletin')->whereNumber('student');
        Route::get('/children/{student}/attendance', [ParentController::class, 'attendance'])->name('attendance')->whereNumber('student');
        Route::get('/children/{student}/homework', [ParentController::class, 'homework'])->name('homework')->whereNumber('student');
        Route::get('/children/{student}/fees', [ParentController::class, 'fees'])->name('fees')->whereNumber('student');
        Route::get('/children/{student}/canteen', [ParentController::class, 'canteen'])->name('canteen')->whereNumber('student');
        Route::get('/children/{student}/transport', [ParentController::class, 'transport'])->name('transport')->whereNumber('student');
    });
});

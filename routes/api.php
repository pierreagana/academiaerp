<?php

use App\Modules\ParentPortal\Presentation\Controllers\Api\MobileParentController;
use App\Modules\ParentPortal\Presentation\Controllers\Api\ParentAuthController;
use App\Modules\ParentPortal\Presentation\Controllers\Api\ParentController;
use App\Modules\Presence\Presentation\Controllers\Api\AccessDeviceController;
use App\Modules\SchoolTrack\Presentation\Controllers\Api\SchoolTrackSubscriptionController;
use App\Modules\Transport\Presentation\Controllers\Api\DriverAuthController;
use App\Modules\Transport\Presentation\Controllers\Api\DriverController;
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
    Route::post('/canteen/enrollment-request', [MobileParentController::class, 'requestCanteenEnrollment'])->name('canteen.enrollment-request');
    Route::get('/transport', [MobileParentController::class, 'transport'])->name('transport');
    Route::get('/transport/zones', [MobileParentController::class, 'transportZones'])->name('transport.zones');
    Route::get('/transport/stops', [MobileParentController::class, 'transportStops'])->name('transport.stops');
    Route::put('/transport/stop', [MobileParentController::class, 'updateTransportStop'])->name('transport.stop.update');
    Route::get('/transport/history', [MobileParentController::class, 'transportHistory'])->name('transport.history');
    Route::get('/infirmary', [MobileParentController::class, 'infirmary'])->name('infirmary');
    Route::post('/infirmary/vaccines', [MobileParentController::class, 'storeVaccine'])->name('infirmary.vaccines.store');
    Route::post('/infirmary/allergies', [MobileParentController::class, 'storeAllergy'])->name('infirmary.allergies.store');
    Route::post('/infirmary/prescriptions', [MobileParentController::class, 'storePrescription'])->name('infirmary.prescriptions.store');
    Route::get('/library', [MobileParentController::class, 'library'])->name('library');
    Route::get('/access', [MobileParentController::class, 'access'])->name('access');
    Route::get('/access/history', [MobileParentController::class, 'accessHistory'])->name('access.history');
    Route::get('/students/{studentId}', [MobileParentController::class, 'studentProfile'])->name('students.profile')->whereNumber('studentId');
    Route::get('/students/{studentId}/activity', [MobileParentController::class, 'studentActivityHistory'])->name('students.activity')->whereNumber('studentId');
    Route::get('/events', [MobileParentController::class, 'events'])->name('events');
    Route::get('/assignments', [MobileParentController::class, 'assignments'])->name('assignments');
    Route::get('/notifications', [MobileParentController::class, 'notifications'])->name('notifications');
    Route::get('/profile', [MobileParentController::class, 'profile'])->name('profile');
    Route::put('/profile', [MobileParentController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/change-password', [MobileParentController::class, 'changePassword'])->name('profile.change-password');
    Route::post('/device-token', [MobileParentController::class, 'updateDeviceToken'])->name('device-token.update');
});

// School Track: subscription status & self-service subscribe — must stay
// reachable without an active subscription (that's the whole point), but
// still behind auth:sanctum so we know which parent is asking.
Route::prefix('v1')->name('api.v1.')->middleware('auth:sanctum')->group(function () {
    Route::get('/school-track/status', [SchoolTrackSubscriptionController::class, 'status'])->name('school-track.status');
    Route::post('/school-track/subscribe', [SchoolTrackSubscriptionController::class, 'subscribe'])->name('school-track.subscribe');
});

// School Track Discovery & Comparison API — gated behind an active parent
// subscription (see EnsureSchoolTrackAccess). Declared after /status and
// /subscribe above so those aren't swallowed by the /{id} wildcard below.
Route::prefix('v1')->name('api.v1.')->middleware(['auth:sanctum', 'school-track.access'])->group(function () {
    Route::get('/school-track/filters', [MobileParentController::class, 'schoolTrackFilters'])->name('school-track.filters');
    Route::get('/school-track', [MobileParentController::class, 'schoolTrackNearby'])->name('school-track.nearby');
    Route::get('/school-track/nearby', [MobileParentController::class, 'schoolTrackNearby'])->name('school-track.nearby.alias');
    Route::get('/school-track/search', [MobileParentController::class, 'schoolTrackSearch'])->name('school-track.search');
    Route::get('/school-track/compare', [MobileParentController::class, 'schoolTrackCompare'])->name('school-track.compare');
    Route::get('/school-track/{id}', [MobileParentController::class, 'schoolTrackDetail'])->name('school-track.detail');
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

// The "academia_access_scanner" terminal app (school gate / canteen / bus
// devices). Devices authenticate with their own Sanctum token — never a
// staff User or ParentAccount — and their access_type/gate is fixed by the
// school admin at creation (SchoolDashboard AccessDeviceController), never
// editable from the scanner app itself.
Route::prefix('device')->name('api.device.')->group(function () {
    Route::post('/login', [AccessDeviceController::class, 'login'])->middleware('throttle:10,1')->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/scan', [AccessDeviceController::class, 'scan'])->name('scan');
        Route::post('/scan/sync', [AccessDeviceController::class, 'sync'])->name('scan.sync');
        Route::get('/roster', [AccessDeviceController::class, 'roster'])->name('roster');
        Route::get('/history', [AccessDeviceController::class, 'history'])->name('history');
    });
});

// The "academia" driver app module — a real Driver account (own phone+password
// Sanctum login), scoped to their own bus. The bus's position now comes
// exclusively from here (HTTP position push + a Reverb client event) —
// there is no manual dispatcher fallback anymore.
Route::prefix('driver')->name('api.driver.')->group(function () {
    Route::post('/login', [DriverAuthController::class, 'login'])->middleware('throttle:10,1')->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [DriverAuthController::class, 'logout'])->name('logout');
        Route::get('/trips', [DriverController::class, 'trips'])->name('trips');
        Route::get('/trip-history', [DriverController::class, 'tripHistory'])->name('trip-history');
        Route::post('/trips/start', [DriverController::class, 'startTrip'])->name('trips.start');
        Route::post('/trips/end', [DriverController::class, 'endTrip'])->name('trips.end');
        Route::post('/position', [DriverController::class, 'updatePosition'])->name('position');
        Route::post('/boarding-scan', [DriverController::class, 'boardingScan'])->name('boarding-scan');
        Route::post('/stops/{stopId}/arrive', [DriverController::class, 'confirmArrival'])->name('stops.arrive');
        Route::get('/notifications', [DriverController::class, 'notifications'])->name('notifications');
        Route::get('/profile', [DriverController::class, 'profile'])->name('profile');
    });
});

<?php

namespace App\Modules\Transport\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Domain\Models\Country;
use App\Modules\SuperAdmin\Domain\Models\School;
use App\Modules\Transport\Domain\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Driver auth — school_code+phone+password → Sanctum token, scoped to the
 * Driver model. Same "resolve the school first" shape as
 * AccessDeviceController::login (a driver's phone alone is already unique
 * across schools since transport_drivers.phone is a unique column, but the
 * school code is asked anyway so a driver types the same three things
 * school staff give them, matching the scanner app's login and avoiding
 * "wrong number, which school did I mean" ambiguity for the driver).
 * No self-registration: drivers are created by the school admin via
 * SchoolDashboard's TransportController::storeDriver.
 */
class DriverAuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'school_code' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string'],
        ]);

        $school = School::where('code', trim($data['school_code']))->first();

        if (!$school) {
            return response()->json(['message' => 'Code établissement introuvable.'], 401);
        }

        $driver = Country::applyPhoneMatch(Driver::where('school_id', $school->id), 'phone', $data['phone'])->first();

        if (!$driver || !$driver->password || !Hash::check($data['password'], $driver->password)) {
            return response()->json(['message' => 'Identifiants incorrects.'], 401);
        }

        $token = $driver->createToken($data['device_name'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'driver' => [
                'id' => (string) $driver->id,
                'name' => trim($driver->first_name . ' ' . $driver->last_name),
                'phone' => $driver->phone,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }
}

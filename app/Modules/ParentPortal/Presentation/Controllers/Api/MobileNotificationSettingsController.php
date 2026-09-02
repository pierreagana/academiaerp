<?php

namespace App\Modules\ParentPortal\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Transport\Domain\Models\NotificationPreference;
use Illuminate\Http\Request;

class MobileNotificationSettingsController extends Controller
{
    private const NEAR_DISTANCES = [100, 500, 1000, 1500, 2000];

    public function show(Request $request)
    {
        $pref = $request->user()->getOrCreateNotificationPreference();

        return response()->json($this->toJson($pref));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'near_pickup_distance_m' => ['nullable', 'integer', 'in:' . implode(',', self::NEAR_DISTANCES)],
            'next_stop_is_pickup' => ['required', 'boolean'],
            'bus_arrived_pickup' => ['required', 'boolean'],
            'student_picked_up' => ['required', 'boolean'],
            'student_missed_pickup' => ['required', 'boolean'],
            'near_dropoff_enabled' => ['required', 'boolean'],
            'bus_arrived_dropoff' => ['required', 'boolean'],
        ]);

        $pref = $request->user()->getOrCreateNotificationPreference();
        $pref->update($data);

        return response()->json($this->toJson($pref));
    }

    private function toJson(NotificationPreference $pref): array
    {
        return [
            'nearPickupDistanceM' => $pref->near_pickup_distance_m,
            'nextStopIsPickup' => $pref->next_stop_is_pickup,
            'busArrivedPickup' => $pref->bus_arrived_pickup,
            'studentPickedUp' => $pref->student_picked_up,
            'studentMissedPickup' => $pref->student_missed_pickup,
            'nearDropoffEnabled' => $pref->near_dropoff_enabled,
            'busArrivedDropoff' => $pref->bus_arrived_dropoff,
        ];
    }
}

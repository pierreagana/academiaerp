<?php

namespace App\Modules\Transport\Application\Services;

use App\Modules\Academic\Domain\Models\NotificationLog;
use App\Modules\Transport\Domain\Models\Bus;
use App\Modules\Transport\Domain\Models\NotificationPreference;
use App\Modules\Transport\Domain\Models\StopArrival;
use App\Support\Notifications\NotificationDispatcher;
use Illuminate\Support\Carbon;

/**
 * Called on every fresh GPS fix (DriverController::updatePosition) — the
 * only place fresh bus position data enters the system, so this has to run
 * synchronously here rather than on a schedule. Bounded to one Haversine
 * calculation per call: only the single "next stop" (first stop on the
 * active route with no StopArrival yet today) is checked, not every stop.
 */
class BusProximityService
{
    private const EARTH_RADIUS_M = 6371000;

    public function __construct(private NotificationDispatcher $notifications)
    {
    }

    public function checkAndNotify(Bus $bus, float $latitude, float $longitude): void
    {
        if (!$bus->active_route_id || !$bus->active_shift) {
            return;
        }

        $route = $bus->activeRoute;
        if (!$route) {
            return;
        }

        $period = $bus->active_shift;
        $today = Carbon::today();

        $arrivedStopIds = StopArrival::where('route_id', $route->id)
            ->where('period', $period)
            ->whereDate('arrived_at', $today)
            ->pluck('route_stop_id');

        $nextStop = $route->stops
            ->reject(fn ($stop) => $arrivedStopIds->contains($stop->id))
            ->sortBy('sequence')
            ->first();

        if (!$nextStop || $nextStop->latitude === null || $nextStop->longitude === null) {
            return;
        }

        $distanceM = $this->haversineMeters(
            $latitude,
            $longitude,
            (float) $nextStop->latitude,
            (float) $nextStop->longitude
        );

        $isPickup = $period === 'morning';
        $title = $isPickup ? 'Bus proche du point de ramassage' : 'Bus proche du point de dépose';

        $students = $nextStop->students()->wherePivot('period', $period)->get();

        foreach ($students as $student) {
            $parents = $student->guardians->load('parentAccount')
                ->pluck('parentAccount')
                ->filter()
                ->unique('id');

            foreach ($parents as $parent) {
                $pref = $parent->getOrCreateNotificationPreference();
                $threshold = $isPickup
                    ? $pref->near_pickup_distance_m
                    : ($pref->near_dropoff_enabled ? NotificationPreference::DEFAULT_DROPOFF_DISTANCE_M : null);

                if ($threshold === null || $distanceM > $threshold) {
                    continue;
                }

                $alreadySent = NotificationLog::where('parent_id', $parent->id)
                    ->where('student_id', $student->id)
                    ->where('type', 'bus')
                    ->where('title', $title)
                    ->whereDate('created_at', $today)
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $this->notifications->notifyParent(
                    $parent, 'bus', $title,
                    "Le bus est à moins de {$threshold} m de l'arrêt de {$student->first_name} (matricule {$bus->bus_number}).",
                    [], $student->id
                );
            }
        }
    }

    private function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_M * $c;
    }
}

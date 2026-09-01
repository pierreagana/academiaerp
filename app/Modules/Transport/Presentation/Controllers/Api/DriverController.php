<?php

namespace App\Modules\Transport\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Transport\Application\DTOs\LogTripDTO;
use App\Modules\Transport\Application\Services\BusPositionBroadcastService;
use App\Modules\Transport\Application\Services\ReverseGeocodingService;
use App\Modules\Transport\Application\Services\TransportEnrollmentService;
use App\Modules\Transport\Application\UseCases\LogTripUseCase;
use App\Modules\Transport\Domain\Models\Bus;
use App\Modules\Transport\Domain\Models\Driver;
use App\Modules\Transport\Domain\Models\Route;
use App\Modules\Transport\Domain\Models\RouteStop;
use App\Modules\Transport\Domain\Models\StopArrival;
use App\Modules\Transport\Domain\Models\TransportBoardingScan;
use App\Modules\Transport\Domain\Models\TripLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Driver-facing transport API — the counterpart to MobileParentController,
 * but for the person actually driving the bus instead of a parent watching
 * it. Every action is scoped to the authenticated Driver's own bus (a bus
 * has at most one driver, `Bus::driver_type = 'driver'`).
 */
class DriverController extends Controller
{
    /** period (used everywhere else in Transport) <-> TripLog::SHIFTS (French, historical). */
    private const PERIOD_TO_SHIFT = ['morning' => 'matin', 'evening' => 'soir'];

    private function busFor(Driver $driver): ?Bus
    {
        return Bus::where('driver_type', 'driver')->where('driver_id', $driver->id)->first();
    }

    public function trips(Request $request)
    {
        /** @var Driver $driver */
        $driver = $request->user();
        $bus = $this->busFor($driver);

        if (!$bus) {
            return response()->json(['bus' => null, 'trips' => [], 'channel' => null]);
        }

        $channel = "transport.bus.{$bus->id}";
        // Newest-assigned route first (bus_id is set directly on the route,
        // so its own updated_at is the best signal for "when was this given
        // to this bus") — otherwise a route just assigned today lands at
        // the bottom of the list, behind routes the driver has run for
        // months, which reads as broken rather than just unsorted.
        $routes = Route::where('bus_id', $bus->id)->where('status', 'actif')->with('stops.students')->orderByDesc('updated_at')->get();

        if ($routes->isEmpty()) {
            return response()->json(['bus' => ['id' => (string) $bus->id, 'busNumber' => $bus->bus_number], 'trips' => [], 'channel' => $channel]);
        }

        $todayScans = TransportBoardingScan::where('bus_id', $bus->id)
            ->whereDate('scanned_at', Carbon::today())
            ->get()
            ->groupBy('period');

        // A route's stops are shared between its morning and evening trip,
        // but "arrived" is a per-trip fact — grouped by period (like
        // $todayScans above) so confirming arrival in the morning doesn't
        // make the evening trip's stops show as already arrived too.
        $arrivalsByPeriod = StopArrival::whereIn('route_stop_id', $routes->flatMap(fn ($r) => $r->stops->pluck('id')))
            ->whereDate('arrived_at', Carbon::today())
            ->get()
            ->groupBy('period');

        // A route with a null period applies to both (today's implicit
        // behavior for every existing route); a period-specific route only
        // contributes a trip for that one period — this is what lets a bus
        // carry several distinct trips within the same period.
        $trips = [];
        foreach (self::PERIOD_TO_SHIFT as $period => $shift) {
            foreach ($routes as $route) {
                if ($route->period !== null && $route->period !== $period) {
                    continue;
                }
                $arrivalsByStop = $arrivalsByPeriod->get($period, collect())->keyBy('route_stop_id');
                $trips[] = $this->tripPayload($bus, $route, $period, $todayScans->get($period, collect()), $arrivalsByStop);
            }
        }

        return response()->json([
            'bus' => ['id' => (string) $bus->id, 'busNumber' => $bus->bus_number],
            'trips' => $trips,
            'channel' => $channel,
        ]);
    }

    private function tripPayload(Bus $bus, Route $route, string $period, $scansForPeriod, $arrivalsByStop = null): array
    {
        $arrivalsByStop ??= collect();
        $isActive = $bus->active_shift === $period && $bus->trip_started_at !== null && $bus->active_route_id === $route->id;
        $completedToday = TripLog::where('bus_id', $bus->id)
            ->where('route_id', $route->id)
            ->where('shift', self::PERIOD_TO_SHIFT[$period])
            ->whereDate('trip_date', Carbon::today())
            ->exists();

        $boardedIds = $scansForPeriod->where('action', 'board')->pluck('student_id')->all();
        $alightedIds = $scansForPeriod->where('action', 'alight')->pluck('student_id')->all();

        $stops = $route->stops->map(function ($stop) use ($period, $boardedIds, $alightedIds, $arrivalsByStop) {
            $students = $stop->students->where('pivot.period', $period)->values();
            $arrival = $arrivalsByStop->get($stop->id);

            return [
                'id' => (string) $stop->id,
                'sequence' => $stop->sequence,
                'name' => $stop->name,
                'address' => $stop->address,
                'latitude' => $stop->latitude !== null ? (float) $stop->latitude : null,
                'longitude' => $stop->longitude !== null ? (float) $stop->longitude : null,
                'arrivedAt' => $arrival?->arrived_at?->toIso8601String(),
                'students' => $students->map(fn (Student $s) => [
                    'id' => (string) $s->id,
                    'name' => trim($s->first_name . ' ' . $s->last_name),
                    'rollNumber' => $s->roll_number,
                    'boarded' => in_array($s->id, $boardedIds, true),
                    'alighted' => in_array($s->id, $alightedIds, true),
                ])->values(),
            ];
        })->values();

        return [
            'id' => "{$route->id}-{$period}",
            'period' => $period,
            'status' => $isActive ? 'in_progress' : ($completedToday ? 'completed' : 'pending'),
            'route' => ['id' => (string) $route->id, 'name' => $route->name],
            'startedAt' => $isActive ? $bus->trip_started_at->toIso8601String() : null,
            'stops' => $stops,
        ];
    }

    public function startTrip(Request $request)
    {
        $data = $request->validate([
            'period' => ['required', 'in:morning,evening'],
            'route_id' => ['required', 'integer', 'exists:transport_routes,id'],
        ]);

        /** @var Driver $driver */
        $driver = $request->user();
        $bus = $this->busFor($driver);

        if (!$bus) {
            return response()->json(['message' => "Aucun bus assigné à ce chauffeur."], 422);
        }
        if ($bus->trip_started_at !== null) {
            return response()->json(['message' => "Un trajet est déjà en cours."], 422);
        }

        $route = Route::where('id', $data['route_id'])->where('bus_id', $bus->id)->where('status', 'actif')->first();
        if (!$route) {
            return response()->json(['message' => "Cette route n'est pas assignée à ce bus."], 422);
        }
        if ($route->period !== null && $route->period !== $data['period']) {
            return response()->json(['message' => "Ce voyage n'appartient pas à cette période."], 422);
        }

        $bus->update([
            'active_route_id' => $route->id,
            'active_shift' => $data['period'],
            'trip_started_at' => now(),
        ]);

        return response()->json(['message' => 'Trajet démarré.', 'startedAt' => now()->toIso8601String()]);
    }

    public function endTrip(Request $request, LogTripUseCase $useCase)
    {
        /** @var Driver $driver */
        $driver = $request->user();
        $bus = $this->busFor($driver);

        if (!$bus || $bus->trip_started_at === null) {
            return response()->json(['message' => "Aucun trajet en cours."], 422);
        }

        $period = $bus->active_shift;
        $attendanceCount = TransportBoardingScan::where('bus_id', $bus->id)
            ->where('period', $period)
            ->where('action', 'board')
            ->whereDate('scanned_at', Carbon::today())
            ->count();

        $useCase->execute(new LogTripDTO([
            'school_id' => $driver->school_id,
            'route_id' => $bus->active_route_id,
            'bus_id' => $bus->id,
            'shift' => self::PERIOD_TO_SHIFT[$period] ?? 'matin',
            'trip_date' => Carbon::today()->toDateString(),
            'status' => 'complete',
            'attendance_count' => $attendanceCount,
            'distance_km' => $bus->activeRoute?->distance_km,
        ]));

        $bus->update(['active_route_id' => null, 'active_shift' => null, 'trip_started_at' => null]);

        return response()->json(['message' => 'Trajet terminé.']);
    }

    public function updatePosition(Request $request, BusPositionBroadcastService $broadcastService)
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        /** @var Driver $driver */
        $driver = $request->user();
        $bus = $this->busFor($driver);

        if (!$bus) {
            return response()->json(['message' => "Aucun bus assigné à ce chauffeur."], 422);
        }

        $broadcastService->updateAndBroadcast($bus, (float) $data['latitude'], (float) $data['longitude']);

        return response()->json(['message' => 'Position mise à jour.']);
    }

    public function boardingScan(Request $request, TransportEnrollmentService $enrollmentService, ReverseGeocodingService $geocodingService)
    {
        $data = $request->validate([
            'roll_number' => ['required', 'string'],
            'action' => ['required', 'in:board,alight'],
            'period' => ['required', 'in:morning,evening'],
            'client_scan_id' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        /** @var Driver $driver */
        $driver = $request->user();
        $bus = $this->busFor($driver);

        if (!$bus) {
            return response()->json(['message' => "Aucun bus assigné à ce chauffeur."], 422);
        }

        if (!empty($data['client_scan_id'])) {
            $existing = TransportBoardingScan::where('client_scan_id', $data['client_scan_id'])->first();
            if ($existing) {
                return response()->json(['message' => $this->scanMessage($existing->action), 'action' => $existing->action]);
            }
        }

        // Same defensive parse as AccessDeviceController::processOne — a
        // scanned QR can be the bare roll number or a "prefix:rollNumber"
        // code depending on which screen generated it; manual entry is
        // always bare.
        $raw = trim($data['roll_number']);
        $rollNumber = str_contains($raw, ':') ? substr($raw, strrpos($raw, ':') + 1) : $raw;

        $student = Student::where('school_id', $driver->school_id)
            ->where('roll_number', $rollNumber)
            ->where('status', 'active')
            ->first();

        if (!$student) {
            return response()->json(['message' => "Élève introuvable pour ce matricule."], 422);
        }

        if (!$bus->active_route_id || !$enrollmentService->isEnrolledOnRoute($student->id, $bus->active_route_id, $data['period'])) {
            $studentName = trim($student->first_name . ' ' . $student->last_name);
            return response()->json(['message' => "{$studentName} n'a pas d'inscription bus valide pour ce trajet."], 422);
        }

        $address = isset($data['latitude'], $data['longitude'])
            ? $geocodingService->addressFor((float) $data['latitude'], (float) $data['longitude'])
            : null;

        TransportBoardingScan::create([
            'student_id' => $student->id,
            'bus_id' => $bus->id,
            'route_id' => $bus->active_route_id,
            'period' => $data['period'],
            'action' => $data['action'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'address' => $address,
            'client_scan_id' => $data['client_scan_id'] ?? null,
            'scanned_at' => now(),
            'scanned_by_driver_id' => $driver->id,
        ]);

        return response()->json([
            'message' => $this->scanMessage($data['action']),
            'action' => $data['action'],
            'studentName' => trim($student->first_name . ' ' . $student->last_name),
        ]);
    }

    private function scanMessage(string $action): string
    {
        return $action === 'board' ? 'Montée enregistrée' : 'Descente enregistrée';
    }

    /**
     * Marks the bus as having reached a stop — distinct from boardingScan(),
     * which tracks individual students, not the bus's own progress along
     * the route. This is what the driver app uses to advance "next stop".
     * Idempotent: re-confirming an already-arrived stop just returns the
     * existing timestamp instead of creating a duplicate row.
     */
    public function confirmArrival(Request $request, $stopId)
    {
        /** @var Driver $driver */
        $driver = $request->user();
        $bus = $this->busFor($driver);

        if (!$bus || !$bus->active_route_id) {
            return response()->json(['message' => "Aucun trajet en cours."], 422);
        }

        $stop = RouteStop::where('id', $stopId)->where('route_id', $bus->active_route_id)->first();
        if (!$stop) {
            return response()->json(['message' => "Cet arrêt n'appartient pas au trajet en cours."], 422);
        }

        $existing = StopArrival::where('route_stop_id', $stop->id)
            ->where('period', $bus->active_shift)
            ->whereDate('arrived_at', Carbon::today())
            ->first();
        if ($existing) {
            return response()->json(['message' => 'Arrivée déjà confirmée.', 'arrivedAt' => $existing->arrived_at->toIso8601String()]);
        }

        $arrival = StopArrival::create([
            'route_stop_id' => $stop->id,
            'route_id' => $bus->active_route_id,
            'bus_id' => $bus->id,
            'driver_id' => $driver->id,
            'period' => $bus->active_shift,
            'arrived_at' => now(),
        ]);

        return response()->json(['message' => 'Arrivée confirmée.', 'arrivedAt' => $arrival->arrived_at->toIso8601String()]);
    }

    /**
     * Completed trips for the driver's own bus — mirrors
     * MobileParentController::transportHistory's filter/pagination shape
     * for consistency. TripLog has no driver_id column, so this is scoped
     * via the bus currently assigned to the driver (same as trips()); a
     * driver moved to a different bus loses visibility into trips logged
     * under their previous bus, same caveat as the rest of this controller.
     */
    public function tripHistory(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'status' => ['nullable', 'in:complete,incident'],
            'period' => ['nullable', 'in:morning,evening'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        /** @var Driver $driver */
        $driver = $request->user();
        $bus = $this->busFor($driver);

        if (!$bus) {
            return response()->json(['history' => [], 'hasMore' => false]);
        }

        $query = TripLog::where('bus_id', $bus->id)->with('route');

        if ($request->filled('from')) {
            $query->whereDate('trip_date', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('trip_date', '<=', $request->query('to'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        if ($request->filled('period')) {
            $query->where('shift', self::PERIOD_TO_SHIFT[$request->query('period')]);
        }

        $query->orderByDesc('trip_date')->orderByDesc('scheduled_start');

        $page = max(1, $request->integer('page', 1));
        $perPage = 15;
        $trips = $query->skip(($page - 1) * $perPage)->take($perPage + 1)->get();
        $hasMore = $trips->count() > $perPage;
        $trips = $trips->take($perPage);

        $dateLabel = function (Carbon $date) {
            if ($date->isToday()) {
                return "Aujourd'hui";
            }
            if ($date->isYesterday()) {
                return 'Hier';
            }
            return $date->translatedFormat('d M Y');
        };

        return response()->json([
            'history' => $trips->map(fn (TripLog $trip) => [
                'id' => (string) $trip->id,
                'routeName' => $trip->route?->name ?? '—',
                'period' => $trip->shift === 'matin' ? 'morning' : 'evening',
                'date' => $trip->trip_date->toDateString(),
                'dateLabel' => $dateLabel($trip->trip_date),
                'time' => $trip->scheduled_start ? substr($trip->scheduled_start, 0, 5) : '—',
                'status' => $trip->status,
                'statusLabel' => TripLog::STATUSES[$trip->status] ?? $trip->status,
                'attendanceCount' => $trip->attendance_count,
                'distanceKm' => $trip->distance_km !== null ? (float) $trip->distance_km : null,
            ])->values(),
            'hasMore' => $hasMore,
        ]);
    }

    /** No driver-targeted notification source exists yet in this system — an honest empty list, not a placeholder. */
    public function notifications(Request $request)
    {
        return response()->json(['notifications' => []]);
    }

    public function profile(Request $request)
    {
        /** @var Driver $driver */
        $driver = $request->user();
        $bus = $this->busFor($driver);

        return response()->json([
            'id' => (string) $driver->id,
            'name' => trim($driver->first_name . ' ' . $driver->last_name),
            'phone' => $driver->phone,
            'bus' => $bus ? ['id' => (string) $bus->id, 'busNumber' => $bus->bus_number, 'plateNumber' => $bus->plate_number] : null,
        ]);
    }
}

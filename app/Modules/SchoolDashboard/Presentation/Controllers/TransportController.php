<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Repositories\StudentRepositoryInterface;
use App\Modules\Finance\Domain\Models\FeeLevel;
use App\Modules\Transport\Application\DTOs\CreateBusDTO;
use App\Modules\Transport\Application\DTOs\CreateDriverDTO;
use App\Modules\Transport\Application\DTOs\CreateRouteDTO;
use App\Modules\Transport\Application\DTOs\CreateStopDTO;
use App\Modules\Transport\Application\DTOs\LogTripDTO;
use App\Modules\Transport\Application\Services\RouteDistanceService;
use App\Modules\Transport\Application\Services\TransportEnrollmentService;
use App\Modules\Transport\Application\Services\TransportStatsService;
use App\Modules\Transport\Application\UseCases\CreateBusUseCase;
use App\Modules\Transport\Application\UseCases\CreateDriverUseCase;
use App\Modules\Transport\Application\UseCases\CreateRouteUseCase;
use App\Modules\Transport\Application\UseCases\CreateStopUseCase;
use App\Modules\Transport\Application\UseCases\LogTripUseCase;
use App\Modules\SuperAdmin\Application\Services\AIService;
use App\Modules\Transport\Application\UseCases\UpdateBusUseCase;
use App\Modules\Transport\Domain\Models\Bus;
use App\Modules\Transport\Domain\Models\Route as TransportRoute;
use App\Modules\Transport\Domain\Models\TransportBoardingScan;
use App\Modules\Transport\Domain\Models\TransportBusPositionLog;
use App\Modules\Transport\Domain\Models\TransportEnrollmentRequest;
use App\Modules\Transport\Domain\Models\TripLog;
use App\Modules\Transport\Domain\Repositories\BusRepositoryInterface;
use App\Modules\Transport\Domain\Repositories\DriverRepositoryInterface;
use App\Modules\Transport\Domain\Repositories\RouteRepositoryInterface;
use App\Modules\Transport\Domain\Repositories\RouteStopRepositoryInterface;
use App\Modules\Transport\Domain\Repositories\TripLogRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransportController extends Controller
{
    private const ABIDJAN_LAT = 5.3599517;
    private const ABIDJAN_LNG = -4.0082563;

    public function fleet(BusRepositoryInterface $busRepository, DriverRepositoryInterface $driverRepository, TransportStatsService $statsService)
    {
        $buses = $busRepository->all();
        $stats = $statsService->fleetStats();
        $drivers = $driverRepository->all();

        return view('SchoolDashboard::transport.fleet', compact('buses', 'stats', 'drivers'));
    }

    public function storeBus(Request $request, CreateBusUseCase $useCase)
    {
        $data = $this->validateBus($request);
        $useCase->execute(new CreateBusDTO($data));

        return back()->with('success', 'Bus ajouté avec succès.');
    }

    public function updateBus(Request $request, $id, UpdateBusUseCase $useCase)
    {
        $data = $this->validateBus($request);
        $useCase->execute($id, new CreateBusDTO($data));

        return back()->with('success', 'Bus mis à jour avec succès.');
    }

    private function validateBus(Request $request): array
    {
        $data = $request->validate([
            'bus_number' => ['required', 'string', 'max:50'],
            'plate_number' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:' . implode(',', array_keys(Bus::STATUSES))],
            'capacity' => ['required', 'integer', 'min:0'],
            'driver_id' => ['nullable', 'string'],
        ]);

        if (!empty($data['driver_id']) && str_contains($data['driver_id'], ':')) {
            [$type, $id] = explode(':', $data['driver_id'], 2);
            $data['driver_type'] = $type;
            $data['driver_id'] = $id;
        } else {
            $data['driver_type'] = null;
            $data['driver_id'] = null;
        }

        return $data;
    }

    public function routes(RouteRepositoryInterface $routeRepository, BusRepositoryInterface $busRepository, TransportStatsService $statsService)
    {
        $schoolId = auth()->user()->school_id;
        $routes = $routeRepository->all();
        $buses = $busRepository->all();
        $efficiency = $statsService->networkEfficiency();
        $mapStops = $this->stopsForMap($routes);
        $nearbyCrossRoutePair = $this->findNearbyCrossRoutePair($mapStops);
        $zones = TransportRoute::where('school_id', $schoolId)
            ->whereNotNull('zone')
            ->distinct()
            ->orderBy('zone')
            ->pluck('zone');

        $academicYear = now()->month >= 8 ? now()->year . '-' . (now()->year + 1) : (now()->year - 1) . '-' . now()->year;
        $feeLevelsByZone = FeeLevel::where('school_id', $schoolId)
            ->where('type', 'transport')
            ->where('academic_year', $academicYear)
            ->get()
            ->keyBy('level');

        return view('SchoolDashboard::transport.routes', compact('routes', 'buses', 'efficiency', 'mapStops', 'nearbyCrossRoutePair', 'zones', 'feeLevelsByZone'));
    }

    private function stopsForMap($routes): array
    {
        $points = [];

        foreach ($routes as $route) {
            foreach ($route->stops as $stop) {
                if ($stop->latitude !== null && $stop->longitude !== null) {
                    $points[] = [
                        'lat' => (float) $stop->latitude,
                        'lng' => (float) $stop->longitude,
                        'name' => $stop->name,
                        'route' => $route->name,
                        'busId' => $route->bus_id,
                    ];
                }
            }
        }

        return $points;
    }

    /**
     * Real geospatial check (Haversine distance) for stops on different
     * routes within 400m of each other — replaces a generic "envisagez de
     * fusionner les routes proches" suggestion with nothing behind it.
     */
    private function findNearbyCrossRoutePair(array $stops): ?array
    {
        for ($i = 0; $i < count($stops); $i++) {
            for ($j = $i + 1; $j < count($stops); $j++) {
                if ($stops[$i]['route'] === $stops[$j]['route']) {
                    continue;
                }
                // Two routes deliberately split on the same bus (e.g. 2
                // morning routes) are meant to stay separate — nearby stops
                // between them isn't a "consider merging" signal.
                if ($stops[$i]['busId'] !== null && $stops[$i]['busId'] === $stops[$j]['busId']) {
                    continue;
                }
                $distanceKm = $this->haversineKm(
                    $stops[$i]['lat'], $stops[$i]['lng'],
                    $stops[$j]['lat'], $stops[$j]['lng']
                );
                if ($distanceKm <= 0.4) {
                    return [
                        'stop_a' => $stops[$i]['name'], 'route_a' => $stops[$i]['route'],
                        'stop_b' => $stops[$j]['name'], 'route_b' => $stops[$j]['route'],
                        'distance_m' => round($distanceKm * 1000),
                    ];
                }
            }
        }

        return null;
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function storeRoute(Request $request, CreateRouteUseCase $useCase)
    {
        $data = $this->validateRoute($request);
        $useCase->execute(new CreateRouteDTO($data));

        return back()->with('success', 'Route créée avec succès.');
    }

    public function updateRoute(Request $request, $id, RouteRepositoryInterface $routeRepository)
    {
        $data = $this->validateRoute($request);
        $routeRepository->update($id, $data);

        return back()->with('success', 'Route mise à jour avec succès.');
    }

    private function validateRoute(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'zone' => ['nullable', 'string', 'max:255'],
            'bus_id' => ['nullable', 'exists:transport_buses,id'],
            'period' => ['nullable', 'string', 'in:' . implode(',', array_keys(TransportRoute::PERIODS))],
            'status' => ['required', 'string', 'in:' . implode(',', array_keys(TransportRoute::STATUSES))],
            'first_stop_time' => ['nullable', 'date_format:H:i'],
            'stop_interval_minutes' => ['nullable', 'integer', 'min:1'],
            'schedule_type' => ['required', 'string', 'in:' . implode(',', array_keys(TransportRoute::SCHEDULE_TYPES))],
            'recurring_days' => ['nullable', 'array'],
            'recurring_days.*' => ['string', 'in:' . implode(',', array_keys(TransportRoute::DAYS))],
        ]);

        $data['recurring_days'] = $data['schedule_type'] === 'recurring' ? ($data['recurring_days'] ?? []) : null;

        return $data;
    }

    public function stops(
        Request $request,
        RouteRepositoryInterface $routeRepository,
        RouteStopRepositoryInterface $stopRepository,
        StudentRepositoryInterface $studentRepository
    ) {
        $routes = $routeRepository->all();
        $selectedRoute = null;
        $stops = collect();

        if ($request->filled('route')) {
            $selectedRoute = $routes->firstWhere('id', (int) $request->get('route'));
        } elseif ($routes->isNotEmpty()) {
            $selectedRoute = $routes->first();
        }

        if ($selectedRoute) {
            $stops = $stopRepository->forRoute($selectedRoute->id);
        }

        $students = $studentRepository->all();
        $abidjan = ['lat' => self::ABIDJAN_LAT, 'lng' => self::ABIDJAN_LNG];
        $mapStops = [];
        foreach ($stops as $stop) {
            if ($stop->latitude !== null && $stop->longitude !== null) {
                $mapStops[] = ['lat' => (float) $stop->latitude, 'lng' => (float) $stop->longitude, 'name' => $stop->name];
            }
        }

        // Real check for consecutive stops within 400m of each other on
        // this same route — replaces a generic "deux arrêts proches en
        // début de séquence" claim that never actually looked at the data.
        $nearbyConsecutivePair = null;
        for ($i = 0; $i < count($mapStops) - 1; $i++) {
            $distanceKm = $this->haversineKm(
                $mapStops[$i]['lat'], $mapStops[$i]['lng'],
                $mapStops[$i + 1]['lat'], $mapStops[$i + 1]['lng']
            );
            if ($distanceKm <= 0.4) {
                $nearbyConsecutivePair = [
                    'stop_a' => $mapStops[$i]['name'],
                    'stop_b' => $mapStops[$i + 1]['name'],
                    'distance_m' => round($distanceKm * 1000),
                ];
                break;
            }
        }

        return view('SchoolDashboard::transport.stops', compact(
            'routes', 'selectedRoute', 'stops', 'students', 'abidjan', 'mapStops', 'nearbyConsecutivePair'
        ));
    }

    public function storeStop(Request $request, CreateStopUseCase $useCase)
    {
        $data = $request->validate([
            'route_id' => ['required', 'exists:transport_routes,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'arrival_time' => ['nullable', 'date_format:H:i'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $useCase->execute(new CreateStopDTO($data));
        app(RouteDistanceService::class)->recalculate($data['route_id']);

        return back()->with('success', 'Arrêt ajouté avec succès.');
    }

    public function moveStop(Request $request, $id, RouteStopRepositoryInterface $stopRepository)
    {
        $data = $request->validate(['direction' => ['required', 'in:up,down']]);
        $stop = $stopRepository->swapSequence($id, $data['direction']);
        app(RouteDistanceService::class)->recalculate($stop->route_id);

        return back()->with('success', 'Ordre des arrêts mis à jour.');
    }

    public function destroyStop($id, RouteStopRepositoryInterface $stopRepository)
    {
        $routeId = $stopRepository->find($id)->route_id;
        $stopRepository->delete($id);
        app(RouteDistanceService::class)->recalculate($routeId);

        return back()->with('success', 'Arrêt supprimé avec succès.');
    }

    public function assignStudents(Request $request, $id, RouteStopRepositoryInterface $stopRepository, TransportEnrollmentService $enrollmentService)
    {
        $data = $request->validate([
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['integer', 'exists:students,id'],
            'period' => ['nullable', 'in:morning,evening'],
            'same_evening' => ['nullable', 'boolean'],
        ]);

        // syncStudents() would silently replace the whole list without going
        // through the enrollment service — diff it ourselves so every new
        // addition still gets an audited, approved enrollment request.
        $stop = $stopRepository->find($id);
        $period = $data['period'] ?? 'morning';
        $newIds = $data['student_ids'];

        $this->syncStopAssignmentsForPeriod($stop, $newIds, $period, $stopRepository, $enrollmentService, $request->user());

        // "Même arrêt pour le trajet du soir" — most students take the same route
        // home as to school, so this saves re-doing the assignment on the evening
        // tab for the common case. Only offered from the morning tab.
        if ($period === 'morning' && $request->boolean('same_evening')) {
            $this->syncStopAssignmentsForPeriod($stop, $newIds, 'evening', $stopRepository, $enrollmentService, $request->user());
        }

        return back()->with('success', 'Élèves assignés avec succès.');
    }

    private function syncStopAssignmentsForPeriod($stop, array $newIds, string $period, RouteStopRepositoryInterface $stopRepository, TransportEnrollmentService $enrollmentService, $staff): void
    {
        $currentIds = $stop->students()->wherePivot('period', $period)->pluck('students.id')->all();

        $toAdd = array_diff($newIds, $currentIds);
        $toRemove = array_diff($currentIds, $newIds);

        foreach ($toAdd as $studentId) {
            $enrollmentService->directlyEnroll(Student::findOrFail($studentId), $stop, $period, $staff);
        }
        foreach ($toRemove as $studentId) {
            $stopRepository->detachStudentForPeriod($stop->id, $studentId, $period);
        }
    }

    public function unassignStudent($id, $studentId, RouteStopRepositoryInterface $stopRepository)
    {
        $stopRepository->detachStudent($id, $studentId);

        return back()->with('success', 'Élève retiré de l\'arrêt.');
    }

    public function enrollmentRequests(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $status = $request->get('status', 'pending');

        $requests = TransportEnrollmentRequest::with(['student', 'routeStop.route', 'requestedByParent', 'reviewedBy'])
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('SchoolDashboard::transport.requests', compact('requests', 'status'));
    }

    public function approveEnrollment($id, TransportEnrollmentService $enrollmentService)
    {
        $schoolId = auth()->user()->school_id;
        $enrollmentRequest = TransportEnrollmentRequest::whereHas('student', fn ($q) => $q->where('school_id', $schoolId))->findOrFail($id);
        $enrollmentService->approve($enrollmentRequest, auth()->user());

        return back()->with('success', 'Inscription validée.');
    }

    public function rejectEnrollment(Request $request, $id, TransportEnrollmentService $enrollmentService)
    {
        $schoolId = auth()->user()->school_id;
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);
        $enrollmentRequest = TransportEnrollmentRequest::whereHas('student', fn ($q) => $q->where('school_id', $schoolId))->findOrFail($id);
        $enrollmentService->reject($enrollmentRequest, auth()->user(), $data['reason'] ?? null);

        return back()->with('success', 'Demande refusée.');
    }

    public function withdrawEnrollment($id, TransportEnrollmentService $enrollmentService)
    {
        $schoolId = auth()->user()->school_id;
        $enrollmentRequest = TransportEnrollmentRequest::whereHas('student', fn ($q) => $q->where('school_id', $schoolId))->findOrFail($id);
        $enrollmentService->withdraw($enrollmentRequest, auth()->user());

        return back()->with('success', 'Élève retiré du service de transport.');
    }

    public function scanner(BusRepositoryInterface $busRepository, RouteRepositoryInterface $routeRepository)
    {
        $buses = $busRepository->all();
        $routes = $routeRepository->all();
        $recentScans = TransportBoardingScan::with(['student', 'bus'])
            ->whereHas('student', fn ($q) => $q->where('school_id', auth()->user()->school_id))
            ->latest('scanned_at')
            ->limit(15)
            ->get();

        return view('SchoolDashboard::transport.scanner', compact('buses', 'routes', 'recentScans'));
    }

    public function scan(Request $request, TransportEnrollmentService $enrollmentService)
    {
        $data = $request->validate([
            'matricule' => ['required', 'string'],
            'period' => ['required', 'in:morning,evening'],
            'action' => ['required', 'in:board,alight'],
            'bus_id' => ['nullable', 'exists:transport_buses,id'],
            'route_id' => ['nullable', 'exists:transport_routes,id'],
        ]);

        $schoolId = auth()->user()->school_id;
        $student = Student::where('school_id', $schoolId)
            ->where('roll_number', trim($data['matricule']))
            ->where('status', 'active')
            ->first();

        if (!$student) {
            return back()->withErrors(['matricule' => "Élève introuvable pour ce matricule."])->withInput();
        }

        // Route-scoped when a route was picked (needed as soon as a bus can
        // run more than one route in the same period — see
        // TransportEnrollmentService::isEnrolledOnRoute()'s own doc comment,
        // added for the driver app's boarding scan for the same reason);
        // otherwise falls back to the looser period-only check.
        $enrolled = !empty($data['route_id'])
            ? $enrollmentService->isEnrolledOnRoute($student->id, (int) $data['route_id'], $data['period'])
            : $enrollmentService->isEnrolled($student->id, $data['period']);

        if (!$enrolled) {
            return back()->withErrors(['matricule' => "{$student->first_name} {$student->last_name} n'a pas d'inscription bus valide pour ce trajet."])->withInput();
        }

        TransportBoardingScan::create([
            'student_id' => $student->id,
            'bus_id' => $data['bus_id'] ?? null,
            'route_id' => $data['route_id'] ?? null,
            'period' => $data['period'],
            'action' => $data['action'],
            'scanned_at' => now(),
            'scanned_by_user_id' => auth()->id(),
        ]);

        $verb = $data['action'] === 'board' ? 'embarqué' : 'débarqué';
        return back()->with('success', "{$student->first_name} {$student->last_name} a {$verb} avec succès.");
    }

    /**
     * Every real boarding/alighting scan — driver app and admin scanner
     * both write to the same transport_boarding_scans table — with counts
     * and addresses, filterable by bus/route/action/period/date range.
     */
    public function boardingHistory(Request $request, BusRepositoryInterface $busRepository, RouteRepositoryInterface $routeRepository)
    {
        $schoolId = auth()->user()->school_id;
        $filters = $request->only(['bus_id', 'route_id', 'action', 'period', 'date_from', 'date_to']);

        $base = function () use ($schoolId, $filters) {
            $query = TransportBoardingScan::whereHas('student', fn ($q) => $q->where('school_id', $schoolId));
            if (!empty($filters['bus_id'])) {
                $query->where('bus_id', $filters['bus_id']);
            }
            if (!empty($filters['route_id'])) {
                $query->where('route_id', $filters['route_id']);
            }
            if (!empty($filters['period'])) {
                $query->where('period', $filters['period']);
            }
            if (!empty($filters['date_from'])) {
                $query->whereDate('scanned_at', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->whereDate('scanned_at', '<=', $filters['date_to']);
            }
            return $query;
        };

        // Counts always reflect every filter EXCEPT action, so the two
        // summary cards stay meaningful regardless of which one (if any)
        // the admin is currently filtering the list by.
        $boardedCount = $base()->where('action', 'board')->count();
        $alightedCount = $base()->where('action', 'alight')->count();

        $scans = $base()
            ->when(!empty($filters['action']), fn ($q) => $q->where('action', $filters['action']))
            ->with(['student', 'bus', 'route'])
            ->latest('scanned_at')
            ->paginate(20)
            ->withQueryString();

        $buses = $busRepository->all();
        $routes = $routeRepository->all();

        return view('SchoolDashboard::transport.history', compact('scans', 'buses', 'routes', 'filters', 'boardedCount', 'alightedCount'));
    }

    /**
     * Real incident-by-weekday pattern from actual trip logs — the old
     * text claimed a Monday/rain pattern with zero weather data or
     * per-weekday analysis behind it anywhere in the app.
     */
    public function aiTripAnalysis(AIService $aiService)
    {
        $schoolId = auth()->user()->school_id;

        $logs = TripLog::where('school_id', $schoolId)
            ->where('trip_date', '>=', now()->subWeeks(8))
            ->get();

        if ($logs->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => "Pas encore assez de tournées enregistrées pour une analyse.",
                'stats' => [],
            ]);
        }

        $dayLabels = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];
        $byDay = $logs->groupBy(fn ($log) => $log->trip_date->dayOfWeekIso);

        $stats = [];
        foreach ($dayLabels as $iso => $label) {
            $dayLogs = $byDay->get($iso, collect());
            if ($dayLogs->isEmpty()) {
                continue;
            }
            $stats[] = [
                'jour' => $label,
                'total_tournees' => $dayLogs->count(),
                'incidents' => $dayLogs->where('status', 'incident')->count(),
                'taux_incident_pct' => round(($dayLogs->where('status', 'incident')->count() / $dayLogs->count()) * 100, 1),
            ];
        }

        $systemPrompt = "Tu es un assistant transport scolaire pour AcademiaERP. Tu commentes de vraies statistiques d'incidents par jour de la semaine, calculées sur les 8 dernières semaines — jamais de facteur externe inventé (météo, etc.) qui n'est pas dans les données.";
        $userPrompt = "Voici les statistiques réelles des tournées par jour de la semaine :\n"
            . json_encode($stats, JSON_UNESCAPED_UNICODE)
            . "\n\nRédige un commentaire court (2-3 phrases) en français : signale le ou les jours avec le plus d'incidents s'il y a un vrai écart, sinon dis que les incidents sont répartis de façon homogène.";

        $result = $aiService->generateText($systemPrompt, $userPrompt, 220);

        return response()->json([
            'success' => $result['success'],
            'analysis' => $result['text'],
            'error' => $result['error'],
            'stats' => $stats,
        ]);
    }

    public function trips(
        Request $request,
        TripLogRepositoryInterface $tripLogRepository,
        BusRepositoryInterface $busRepository,
        RouteRepositoryInterface $routeRepository,
        TransportStatsService $statsService
    ) {
        $filters = $request->only(['bus_id', 'date']);
        $trips = $tripLogRepository->paginate(10, $filters);
        $buses = $busRepository->all();
        $routes = $routeRepository->all();
        $summary = $statsService->sevenDaySummary();

        return view('SchoolDashboard::transport.trips', compact('trips', 'buses', 'routes', 'summary', 'filters'));
    }

    public function storeTrip(Request $request, LogTripUseCase $useCase)
    {
        $data = $request->validate([
            'route_id' => ['nullable', 'exists:transport_routes,id'],
            'bus_id' => ['nullable', 'exists:transport_buses,id'],
            'shift' => ['required', 'string', 'in:' . implode(',', array_keys(TripLog::SHIFTS))],
            'trip_date' => ['required', 'date'],
            'scheduled_start' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'string', 'in:' . implode(',', array_keys(TripLog::STATUSES))],
            'attendance_count' => ['nullable', 'integer', 'min:0'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'incident_notes' => ['nullable', 'string'],
        ]);

        $data['created_by'] = auth()->id();
        $useCase->execute(new LogTripDTO($data));

        return back()->with('success', 'Trajet journalisé avec succès.');
    }

    public function exportTrips(Request $request, TripLogRepositoryInterface $tripLogRepository)
    {
        $trips = $tripLogRepository->forRange(
            $request->get('start', Carbon::today()->subDays(30)->toDateString()),
            $request->get('end', Carbon::today()->toDateString())
        )->load(['route', 'bus']);

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=journal_trajets_' . date('Y-m-d') . '.csv',
        ];

        $callback = function () use ($trips) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Tournée', 'Route', 'Bus', 'Statut', 'Présence', 'Attendus', 'Distance (km)', 'Incident'], ',', '"', '\\');

            foreach ($trips as $trip) {
                fputcsv($file, [
                    $trip->trip_date->format('d/m/Y'),
                    TripLog::SHIFTS[$trip->shift] ?? $trip->shift,
                    $trip->route->name ?? '-',
                    $trip->bus->bus_number ?? '-',
                    TripLog::STATUSES[$trip->status] ?? $trip->status,
                    $trip->attendance_count ?? '-',
                    $trip->expected_count ?? '-',
                    $trip->distance_km ?? '-',
                    $trip->incident_notes ?? '-',
                ], ',', '"', '\\');
            }

            fclose($file);
        };

        return response()->streamDownload($callback, 'journal_trajets_' . date('Y-m-d') . '.csv', $headers);
    }

    public function map(RouteRepositoryInterface $routeRepository, BusRepositoryInterface $busRepository, TransportStatsService $statsService)
    {
        $activeRoutes = $routeRepository->active();
        $buses = $busRepository->all()->map(function ($bus) use ($statsService) {
            $bus->daily_status = $statsService->busDailyStatus($bus);
            return $bus;
        });
        $abidjan = ['lat' => self::ABIDJAN_LAT, 'lng' => self::ABIDJAN_LNG];
        $mapStops = $this->stopsForMap($activeRoutes);
        $reverb = [
            'key' => config('broadcasting.connections.reverb.key'),
            'port' => config('broadcasting.connections.reverb.options.port'),
        ];
        $busPositions = $buses
            ->filter(fn ($bus) => $bus->current_latitude !== null && $bus->current_longitude !== null)
            ->map(fn ($bus) => [
                'id' => $bus->id,
                'busNumber' => $bus->bus_number,
                'lat' => (float) $bus->current_latitude,
                'lng' => (float) $bus->current_longitude,
            ])
            ->values();

        return view('SchoolDashboard::transport.map', compact('activeRoutes', 'buses', 'abidjan', 'mapStops', 'reverb', 'busPositions'));
    }

    /** The day's logged positions for one bus — feeds the dashboard's replay controls. */
    public function busPositionHistory(Request $request, $id, BusRepositoryInterface $busRepository)
    {
        $bus = $busRepository->find($id);
        $date = $request->get('date') ?: Carbon::today()->toDateString();

        $points = TransportBusPositionLog::where('bus_id', $bus->id)
            ->whereDate('recorded_at', $date)
            ->orderBy('recorded_at')
            ->get()
            ->map(fn (TransportBusPositionLog $log) => [
                'lat' => (float) $log->latitude,
                'lng' => (float) $log->longitude,
                'recordedAt' => $log->recorded_at->toIso8601String(),
            ]);

        return response()->json(['points' => $points]);
    }

    public function drivers(DriverRepositoryInterface $driverRepository)
    {
        $drivers = $driverRepository->all();

        return view('SchoolDashboard::transport.drivers', compact('drivers'));
    }

    public function storeDriver(Request $request, CreateDriverUseCase $useCase)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', 'unique:transport_drivers,phone'],
            'password' => ['nullable', 'string', 'min:6'],
            'id_card_front' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'id_card_back' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'license_front' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'license_back' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'has_assistant' => ['nullable', 'boolean'],
            'assistant_name' => ['nullable', 'required_if:has_assistant,1', 'string', 'max:255'],
            'assistant_phone' => ['nullable', 'string', 'max:30'],
            'assistant_id_card_front' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'assistant_id_card_back' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        $data['has_assistant'] = $request->boolean('has_assistant');

        foreach (['id_card_front', 'id_card_back', 'license_front', 'license_back', 'assistant_id_card_front', 'assistant_id_card_back'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('drivers/documents', 'public');
            } else {
                unset($data[$field]);
            }
        }

        if (!$data['has_assistant']) {
            unset($data['assistant_name'], $data['assistant_phone'], $data['assistant_id_card_front'], $data['assistant_id_card_back']);
        }

        $useCase->execute(new CreateDriverDTO($data));

        return back()->with('success', 'Chauffeur ajouté avec succès.');
    }
}

<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Repositories\StudentRepositoryInterface;
use App\Modules\Transport\Application\DTOs\CreateBusDTO;
use App\Modules\Transport\Application\DTOs\CreateDriverDTO;
use App\Modules\Transport\Application\DTOs\CreateRouteDTO;
use App\Modules\Transport\Application\DTOs\CreateStopDTO;
use App\Modules\Transport\Application\DTOs\LogTripDTO;
use App\Modules\Transport\Application\Services\RouteDistanceService;
use App\Modules\Transport\Application\Services\TransportStatsService;
use App\Modules\Transport\Application\UseCases\CreateBusUseCase;
use App\Modules\Transport\Application\UseCases\CreateDriverUseCase;
use App\Modules\Transport\Application\UseCases\CreateRouteUseCase;
use App\Modules\Transport\Application\UseCases\CreateStopUseCase;
use App\Modules\Transport\Application\UseCases\LogTripUseCase;
use App\Modules\Transport\Application\UseCases\UpdateBusUseCase;
use App\Modules\Transport\Domain\Events\BusPositionUpdated;
use App\Modules\Transport\Domain\Models\Bus;
use App\Modules\Transport\Domain\Models\Route as TransportRoute;
use App\Modules\Transport\Domain\Models\RouteStop;
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
        $routes = $routeRepository->all();
        $buses = $busRepository->all();
        $efficiency = $statsService->networkEfficiency();
        $mapStops = $this->stopsForMap($routes);

        return view('SchoolDashboard::transport.routes', compact('routes', 'buses', 'efficiency', 'mapStops'));
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
                    ];
                }
            }
        }

        return $points;
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

        return view('SchoolDashboard::transport.stops', compact('routes', 'selectedRoute', 'stops', 'students', 'abidjan', 'mapStops'));
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

    public function assignStudents(Request $request, $id, RouteStopRepositoryInterface $stopRepository)
    {
        $data = $request->validate([
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $stopRepository->syncStudents($id, $data['student_ids']);

        return back()->with('success', 'Élèves assignés avec succès.');
    }

    public function unassignStudent($id, $studentId, RouteStopRepositoryInterface $stopRepository)
    {
        $stopRepository->detachStudent($id, $studentId);

        return back()->with('success', 'Élève retiré de l\'arrêt.');
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

        return view('SchoolDashboard::transport.map', compact('activeRoutes', 'buses', 'abidjan', 'mapStops'));
    }

    /**
     * Manual real-position reporting: there's no GPS device/driver app in
     * this system, so a dispatcher sets the bus's current point on the map.
     * That real position is persisted and broadcast live to every parent
     * whose child rides this bus.
     */
    public function updateBusPosition(Request $request, $id, BusRepositoryInterface $busRepository)
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $busRepository->update($id, [
            'current_latitude' => $data['latitude'],
            'current_longitude' => $data['longitude'],
            'position_updated_at' => now(),
        ]);
        $bus = $busRepository->find($id);

        $studentIds = RouteStop::whereHas('route', fn ($q) => $q->where('bus_id', $bus->id))
            ->with('students')
            ->get()
            ->pluck('students')
            ->flatten()
            ->pluck('id')
            ->unique();

        foreach ($studentIds as $studentId) {
            broadcast(new BusPositionUpdated($bus, $studentId));
        }

        return back()->with('success', 'Position du bus mise à jour.');
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
            'password' => ['nullable', 'string', 'min:6'],
            'id_card_front' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'id_card_back' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'license_front' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'license_back' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'has_assistant' => ['nullable', 'boolean'],
            'assistant_name' => ['nullable', 'required_if:has_assistant,1', 'string', 'max:255'],
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
            unset($data['assistant_name'], $data['assistant_id_card_front'], $data['assistant_id_card_back']);
        }

        $useCase->execute(new CreateDriverDTO($data));

        return back()->with('success', 'Chauffeur ajouté avec succès.');
    }
}

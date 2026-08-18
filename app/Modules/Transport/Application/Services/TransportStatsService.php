<?php

namespace App\Modules\Transport\Application\Services;

use App\Modules\Transport\Domain\Repositories\BusRepositoryInterface;
use App\Modules\Transport\Domain\Repositories\TripLogRepositoryInterface;
use Illuminate\Support\Carbon;

class TransportStatsService
{
    private BusRepositoryInterface $busRepository;
    private TripLogRepositoryInterface $tripLogRepository;

    public function __construct(BusRepositoryInterface $busRepository, TripLogRepositoryInterface $tripLogRepository)
    {
        $this->busRepository = $busRepository;
        $this->tripLogRepository = $tripLogRepository;
    }

    public function fleetStats(): array
    {
        $counts = $this->busRepository->countByStatus();

        return [
            'total' => array_sum($counts),
            'en_service' => $counts['en_service'] ?? 0,
            'maintenance' => $counts['maintenance'] ?? 0,
            'disponible' => $counts['disponible'] ?? 0,
        ];
    }

    public function networkEfficiency(): ?array
    {
        $today = Carbon::today();
        $trips = $this->tripLogRepository->forRange($today->toDateString(), $today->toDateString());
        $period = 'aujourd\'hui';

        if ($trips->isEmpty()) {
            $trips = $this->tripLogRepository->forRange($today->copy()->subDays(6)->toDateString(), $today->toDateString());
            $period = '7 derniers jours';
        }

        if ($trips->isEmpty()) {
            return null;
        }

        $onTime = $trips->where('status', 'complete')->count();

        return [
            'percent' => (int) round(($onTime / $trips->count()) * 100),
            'period' => $period,
            'totalTrips' => $trips->count(),
        ];
    }

    public function sevenDaySummary(): array
    {
        $end = Carbon::today();
        $start = $end->copy()->subDays(6);
        $trips = $this->tripLogRepository->forRange($start->toDateString(), $end->toDateString());

        if ($trips->isEmpty()) {
            return [
                'punctualityRate' => null,
                'averageAttendance' => null,
                'incidentCount' => 0,
                'totalTrips' => 0,
            ];
        }

        $complete = $trips->where('status', 'complete')->count();
        $withCounts = $trips->filter(fn ($t) => $t->expected_count && $t->expected_count > 0 && $t->attendance_count !== null);
        $avgAttendance = $withCounts->isNotEmpty()
            ? round($withCounts->avg(fn ($t) => ($t->attendance_count / $t->expected_count) * 100))
            : null;

        return [
            'punctualityRate' => (int) round(($complete / $trips->count()) * 100),
            'averageAttendance' => $avgAttendance,
            'incidentCount' => $trips->where('status', 'incident')->count(),
            'totalTrips' => $trips->count(),
        ];
    }

    public function busDailyStatus($bus): array
    {
        $trip = $this->tripLogRepository->latestForBusOnDate($bus->id, Carbon::today()->toDateString());

        if (!$trip) {
            return ['label' => 'Non journalisé', 'tone' => 'neutral'];
        }

        if ($trip->status === 'incident') {
            return ['label' => 'Incident', 'tone' => 'alert', 'detail' => $trip->incident_notes];
        }

        return ['label' => 'À l\'heure', 'tone' => 'ok'];
    }
}

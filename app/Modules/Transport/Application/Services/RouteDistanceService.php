<?php

namespace App\Modules\Transport\Application\Services;

use App\Modules\Transport\Domain\Repositories\RouteRepositoryInterface;
use App\Modules\Transport\Domain\Repositories\RouteStopRepositoryInterface;

class RouteDistanceService
{
    private const EARTH_RADIUS_KM = 6371;

    private RouteStopRepositoryInterface $stopRepository;
    private RouteRepositoryInterface $routeRepository;

    public function __construct(RouteStopRepositoryInterface $stopRepository, RouteRepositoryInterface $routeRepository)
    {
        $this->stopRepository = $stopRepository;
        $this->routeRepository = $routeRepository;
    }

    public function recalculate($routeId): void
    {
        $stops = $this->stopRepository->forRoute($routeId)
            ->filter(fn ($stop) => $stop->latitude !== null && $stop->longitude !== null)
            ->values();

        $distanceKm = null;

        if ($stops->count() >= 2) {
            $total = 0.0;

            for ($i = 1; $i < $stops->count(); $i++) {
                $total += $this->haversineKm(
                    (float) $stops[$i - 1]->latitude,
                    (float) $stops[$i - 1]->longitude,
                    (float) $stops[$i]->latitude,
                    (float) $stops[$i]->longitude
                );
            }

            $distanceKm = round($total, 2);
        }

        $this->routeRepository->update($routeId, ['distance_km' => $distanceKm]);
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }
}

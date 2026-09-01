<?php

namespace App\Modules\Transport\Application\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Real reverse geocoding (OpenStreetMap Nominatim) — turns a boarding scan's
 * raw lat/lng into a short, human-readable address (e.g. "Cocody
 * Saint-Jean, Rue 14") for display, instead of leaving staff to read
 * coordinates. No API key, matching the rest of this app's OSM usage
 * (raster tiles, NearbyAmenitiesService's Overpass calls).
 *
 * Never blocks a scan on failure — a network hiccup or Nominatim being
 * down just means no address, not a broken scan.
 */
class ReverseGeocodingService
{
    public function addressFor(float $lat, float $lng): ?string
    {
        // Rounded to ~11m — scans at the same stop reuse one cached lookup
        // instead of hitting Nominatim (whose usage policy caps at 1 req/s)
        // every time.
        $key = 'reverse-geocode:' . round($lat, 4) . ',' . round($lng, 4);

        return Cache::remember($key, now()->addDays(30), function () use ($lat, $lng) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'AcademiaSchoolApp/1.0 (boarding-scan reverse-geocoding)',
                ])
                    ->timeout(3)
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'lat' => $lat,
                        'lon' => $lng,
                        'format' => 'jsonv2',
                        'addressdetails' => 1,
                    ]);

                if (!$response->successful()) {
                    return null;
                }

                return $this->shortAddress($response->json());
            } catch (\Throwable $e) {
                Log::warning('Reverse geocoding lookup failed', ['lat' => $lat, 'lng' => $lng, 'error' => $e->getMessage()]);

                return null;
            }
        });
    }

    /** "Cocody Saint-Jean, Rue 14" over Nominatim's full, verbose display_name. */
    private function shortAddress(?array $data): ?string
    {
        if (!$data) {
            return null;
        }

        $address = $data['address'] ?? [];
        $area = $address['suburb'] ?? $address['neighbourhood'] ?? $address['quarter'] ?? $address['city_district'] ?? null;
        $road = $address['road'] ?? null;
        $houseNumber = $address['house_number'] ?? null;

        $road = $houseNumber && $road ? "{$road} {$houseNumber}" : $road;
        $parts = array_filter([$area, $road]);

        if (!empty($parts)) {
            return implode(', ', $parts);
        }

        return $data['display_name'] ?? null;
    }
}

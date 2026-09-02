<?php

namespace App\Modules\SuperAdmin\Application\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Address autocomplete backed by OpenStreetMap's free Nominatim geocoder —
 * same data source and "identify your app" usage policy as
 * NearbyAmenitiesService's Overpass calls. Called server-side (not directly
 * from the browser) so we control the User-Agent Nominatim's policy
 * requires and can rate-limit/cache later without a frontend change.
 */
class AddressGeocodingService
{
    private const ENDPOINT = 'https://nominatim.openstreetmap.org/search';

    /** @return array<int, array{label:string, lat:float, lng:float}> */
    public function search(string $query): array
    {
        $query = trim($query);
        if (mb_strlen($query) < 3) {
            return [];
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders(['User-Agent' => 'AcademiaSchoolApp/1.0 (parent address autocomplete)'])
                ->get(self::ENDPOINT, [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 6,
                ]);

            if (!$response->successful()) {
                Log::warning('Nominatim address search returned a non-2xx response', ['status' => $response->status()]);
                return [];
            }

            return collect($response->json())
                ->filter(fn ($r) => isset($r['display_name'], $r['lat'], $r['lon']))
                ->map(fn ($r) => [
                    'label' => $r['display_name'],
                    'lat' => (float) $r['lat'],
                    'lng' => (float) $r['lon'],
                ])
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('Nominatim address search failed', ['error' => $e->getMessage()]);
            return [];
        }
    }
}

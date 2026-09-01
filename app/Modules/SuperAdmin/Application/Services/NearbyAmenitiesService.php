<?php

namespace App\Modules\SuperAdmin\Application\Services;

use App\Modules\SuperAdmin\Domain\Models\School;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Real nearby amenities (pharmacies, hospitals, supermarkets, banks, bus
 * stops...) around a school, fetched live from OpenStreetMap's Overpass API
 * — the same free, no-API-key data source the app's own map tiles already
 * come from. Replaces the old self-reported "nearby_places" field: schools
 * rarely filled it in, and when they did it only ever showed at a
 * fabricated position (a fixed angle around the school) since no real
 * coordinate was ever captured per place. This returns real coordinates and
 * real distances instead.
 */
class NearbyAmenitiesService
{
    private const ENDPOINT = 'https://overpass-api.de/api/interpreter';
    private const RADIUS_METERS = 1000;
    private const CACHE_TTL_DAYS = 14;
    private const MAX_RESULTS = 12;

    /** OSM tag => [emoji, French label, hex color]. Labels reuse the same keywords the mobile map's icon-matching already looks for (pharmaci/bus/commerce.../sante.../banque...), so no mobile-side change is needed to pick the right icon. */
    private const CATEGORIES = [
        'amenity=pharmacy' => ['💊', 'Pharmacie', 0xFF059669],
        'amenity=hospital' => ['🏥', 'Hôpital', 0xFFDC2626],
        'amenity=clinic' => ['🏥', 'Clinique', 0xFFDC2626],
        'amenity=doctors' => ['🏥', 'Cabinet médical', 0xFFDC2626],
        'shop=supermarket' => ['🛒', 'Supermarché', 0xFFF59E0B],
        'shop=convenience' => ['🛒', 'Supérette', 0xFFF59E0B],
        'amenity=bank' => ['🏦', 'Banque', 0xFF1E3A8A],
        'amenity=atm' => ['🏧', 'Distributeur', 0xFF1E3A8A],
        'amenity=bus_station' => ['🚌', 'Gare routière', 0xFF7C3AED],
        'highway=bus_stop' => ['🚌', 'Arrêt de bus', 0xFF7C3AED],
        'amenity=police' => ['🚓', 'Police', 0xFF334155],
        'amenity=fuel' => ['⛽', 'Station essence', 0xFFEA580C],
        'amenity=restaurant' => ['🍽️', 'Restaurant', 0xFFEC4899],
        'amenity=fast_food' => ['🍔', 'Restauration rapide', 0xFFEC4899],
    ];

    /**
     * @return array<int, array{emoji:string,label:string,type:string,distance:string,distanceMeters:int,colorHex:int,latitude:float,longitude:float}>
     */
    public function forSchool(School $school): array
    {
        // Deliberately the school's own raw columns, not getCoordinates()'s
        // city-guess/fabricated fallback — querying real POIs around a made
        // -up point would be worse than showing nothing.
        if (empty($school->latitude) || empty($school->longitude)) {
            return [];
        }

        $lat = (float) $school->latitude;
        $lng = (float) $school->longitude;

        return Cache::remember(
            'school-nearby-amenities:' . $school->id . ':' . round($lat, 4) . ',' . round($lng, 4),
            now()->addDays(self::CACHE_TTL_DAYS),
            fn () => $this->fetch($lat, $lng)
        );
    }

    private function fetch(float $lat, float $lng): array
    {
        $filters = collect(array_keys(self::CATEGORIES))
            ->map(function (string $tag) use ($lat, $lng) {
                [$key, $value] = explode('=', $tag);
                return "node[\"{$key}\"=\"{$value}\"](around:" . self::RADIUS_METERS . ",{$lat},{$lng});";
            })
            ->implode("\n");

        $fetchLimit = self::MAX_RESULTS * 3; // a few extra since unnamed nodes get filtered out below
        $query = "[out:json][timeout:15];\n(\n{$filters}\n);\nout center {$fetchLimit};";

        try {
            // Overpass's Apache front-end 406s requests with Guzzle's
            // default User-Agent — it (reasonably) wants scripted clients
            // to identify themselves, per Overpass's own usage policy.
            $response = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'AcademiaSchoolApp/1.0 (school-track nearby-amenities lookup)'])
                ->asForm()
                ->post(self::ENDPOINT, ['data' => $query]);
            if (!$response->successful()) {
                Log::warning('Overpass nearby-amenities lookup returned a non-2xx response', ['status' => $response->status()]);
                return [];
            }
            $elements = $response->json('elements', []);
        } catch (\Throwable $e) {
            Log::warning('Overpass nearby-amenities lookup failed', ['error' => $e->getMessage()]);
            return [];
        }

        return collect($elements)
            ->filter(fn ($el) => !empty($el['tags']['name']) && isset($el['lat'], $el['lon']))
            ->map(function (array $el) use ($lat, $lng) {
                $tagKey = collect(self::CATEGORIES)->keys()->first(
                    fn (string $tag) => $this->matchesTag($el['tags'] ?? [], $tag)
                );
                [$emoji, $categoryLabel, $color] = self::CATEGORIES[$tagKey] ?? ['📍', 'Lieu', 0xFF1E3A8A];
                $distanceMeters = (int) round($this->haversineMeters($lat, $lng, (float) $el['lat'], (float) $el['lon']));

                return [
                    'emoji' => $emoji,
                    'label' => $el['tags']['name'] . ' · ' . $categoryLabel,
                    'type' => strtolower($categoryLabel),
                    'distance' => $distanceMeters >= 1000
                        ? round($distanceMeters / 1000, 1) . ' km'
                        : $distanceMeters . ' m',
                    'distanceMeters' => $distanceMeters,
                    'colorHex' => $color,
                    'latitude' => (float) $el['lat'],
                    'longitude' => (float) $el['lon'],
                ];
            })
            ->sortBy('distanceMeters')
            ->take(self::MAX_RESULTS)
            ->values()
            ->all();
    }

    private function matchesTag(array $tags, string $categoryKey): bool
    {
        [$key, $value] = explode('=', $categoryKey);
        return ($tags[$key] ?? null) === $value;
    }

    /** Great-circle distance in meters (metric precision — School::calculateDistance() is km-rounded, too coarse for sorting nearby points). */
    private function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusMeters = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusMeters * $c;
    }
}

<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\SchoolRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Models\School as SchoolModel;
use Illuminate\Support\Collection;

class GetMultiCampusNetworkUseCase
{
    public function __construct(
        private SchoolRepositoryInterface $schoolRepository
    ) {}

    public function execute(): array
    {
        $schools = $this->schoolRepository->getAll();
        $allSchools = collect($schools);

        $normalize = fn($str) => str_replace(
            ['é', 'è', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'û', 'ù', 'ç', '\''],
            ['e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'u', 'u', 'c', ' '],
            strtolower($str ?? '')
        );

        $cityCoordinates = [
            'dakar'       => ['lat' => 14.7167, 'lng' => -17.4677, 'top' => '30%', 'left' => '12%'],
            'saint-louis' => ['lat' => 16.0326, 'lng' => -16.4818, 'top' => '22%', 'left' => '14%'],
            'bamako'      => ['lat' => 12.6392, 'lng' => -8.0029,  'top' => '36%', 'left' => '24%'],
            'abidjan'     => ['lat' => 5.3599,  'lng' => -4.0083,  'top' => '62%', 'left' => '32%'],
            'cocody'      => ['lat' => 5.3750,  'lng' => -3.9850,  'top' => '60%', 'left' => '34%'],
            'douala'      => ['lat' => 4.0511,  'lng' => 9.7679,   'top' => '64%', 'left' => '58%'],
            'yaounde'     => ['lat' => 3.8480,  'lng' => 11.5021,  'top' => '66%', 'left' => '62%'],
            'libreville'  => ['lat' => 0.4162,  'lng' => 9.4673,   'top' => '72%', 'left' => '54%'],
        ];

        // Groups by the actual matched city (searched across the *whole*
        // location string, same as the individual-marker matching below) —
        // not just its first comma-separated segment. A location like
        // "Carrefour Guiraud, Rue Marie-Rose Guiraud, ..., Cocody, Abidjan,
        // Côte d'Ivoire" has "Carrefour Guiraud" as its first segment, which
        // matches none of the known cities and used to silently fall back to
        // Dakar's coordinates — putting the badge ~1000km from the real school.
        $cityKeyFor = function ($school) use ($cityCoordinates, $normalize) {
            $locNorm = $normalize($school->location ?? '');
            $nameNorm = $normalize($school->name ?? '');
            foreach ($cityCoordinates as $key => $coords) {
                if (str_contains($locNorm, $key) || str_contains($nameNorm, $key)) {
                    return $key;
                }
            }
            // No known city matched: fall back to the first location segment
            // as its own bucket rather than lumping every unmatched school
            // into a misleading "Dakar" group.
            $parts = explode(',', $school->location ?? '');

            return 'unmatched:' . trim($parts[0] ?? 'Établissement');
        };

        // Build networks as a Collection for Blade ->take(), ->count() calls
        $networks = $allSchools->groupBy($cityKeyFor)->map(function ($items, $cityKey) use ($cityCoordinates) {
            $items = collect($items);
            $schoolsCollection = $items->map(fn($s) => (object)[
                'id'       => $s->id,
                'name'     => $s->name,
                'code'     => $s->code,
                'status'   => $s->status,
                'plan_name'=> $s->package ?? 'Standard',
            ]);

            $isKnownCity = isset($cityCoordinates[$cityKey]);
            $displayName = $isKnownCity ? ucfirst($cityKey) : str_replace('unmatched:', '', $cityKey);

            // Prefer the real centroid of this group's own geocoded schools
            // (set via the establishment map picker) over the hardcoded city
            // table — accurate, and works even for cities not in that list.
            $withCoords = $items->filter(fn($s) => is_numeric($s->latitude ?? null) && is_numeric($s->longitude ?? null));
            if ($withCoords->isNotEmpty()) {
                $geo = [
                    'lat' => $withCoords->avg('latitude'),
                    'lng' => $withCoords->avg('longitude'),
                ];
            } elseif ($isKnownCity) {
                $geo = $cityCoordinates[$cityKey];
            } else {
                $geo = ['lat' => 14.7167, 'lng' => -17.4677];
            }

            return [
                'city'             => $displayName,
                'region'           => 'Afrique de l\'Ouest & Centrale',
                'school_count'     => $items->count(),
                'growth'           => '+' . ($items->count() * 5) . '%',
                'students'         => $items->sum('studentsCount'),
                'enterprise_count' => $items->filter(fn($i) => str_contains(strtolower($i->package ?? ''), 'enterprise') || str_contains(strtolower($i->package ?? ''), 'premium'))->count(),
                'status'           => 'Active',
                'schools'          => $schoolsCollection,
                'lat'              => round((float) $geo['lat'], 6),
                'lng'              => round((float) $geo['lng'], 6),
            ];
        })->values();

        $allSchoolsCollection = $allSchools->map(function ($s) use ($cityCoordinates, $normalize) {
            // Prefer the school's own stored GPS coordinates (set via the
            // registration wizard / establishment map picker) — accurate and
            // naturally distinct per school. Only fall back to guessing a
            // city center from free-text `location` when no real coordinates
            // exist yet.
            if (is_numeric($s->latitude ?? null) && is_numeric($s->longitude ?? null)) {
                $lat = round((float) $s->latitude, 6);
                $lng = round((float) $s->longitude, 6);
            } else {
                $locNorm = $normalize($s->location ?? '');
                $nameNorm = $normalize($s->name ?? '');
                $baseGeo = ['lat' => 14.7167, 'lng' => -17.4677];

                foreach ($cityCoordinates as $key => $geo) {
                    if (str_contains($locNorm, $key) || str_contains($nameNorm, $key)) {
                        $baseGeo = $geo;
                        break;
                    }
                }

                // Spread schools without real coordinates around their matched
                // city center on a golden-angle spiral — unlike an id%5 offset
                // (only 5 distinct positions, so any two schools 5 apart in id
                // land on the exact same point and one marker hides the other),
                // this never repeats and grows outward as more schools share a
                // city, so every point stays individually visible when zoomed in.
                $angle = deg2rad($s->id * 137.508);
                $radius = 0.008 * sqrt($s->id);
                $lat = round($baseGeo['lat'] + $radius * cos($angle), 6);
                $lng = round($baseGeo['lng'] + $radius * sin($angle), 6);
            }

            return (object)[
                'id'             => $s->id,
                'name'           => $s->name,
                'code'           => $s->code,
                'location'       => $s->location,
                'status'         => strtolower($s->status ?? 'actif'),
                'type'           => $s->type ?? 'Établissement',
                'plan_name'      => $s->package ?? 'Standard',
                'students_count' => $s->studentsCount,
                'lat'            => $lat,
                'lng'            => $lng,
            ];
        });

        $dbStorage = SchoolModel::sum('storage_used_gb');
        $calculatedStorage = $dbStorage > 0 ? (float)$dbStorage : (float)round($allSchools->count() * 18.5, 1);

        $stats = [
            'total_campus'     => $allSchools->count(),
            'enterprise_count' => $allSchools->filter(fn($s) => str_contains(strtolower($s->package ?? ''), 'enterprise') || str_contains(strtolower($s->package ?? ''), 'premium'))->count(),
            'premium_count'    => $allSchools->filter(fn($s) => str_contains(strtolower($s->package ?? ''), 'pro'))->count(),
            'total_students'   => $allSchools->sum('studentsCount'),
            'total_storage_gb' => $calculatedStorage,
        ];

        return [
            'networks'   => $networks,
            'stats'      => $stats,
            'allSchools' => $allSchoolsCollection,
        ];
    }
}

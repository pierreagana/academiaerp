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

        // Build networks as a Collection for Blade ->take(), ->count() calls
        $networks = $allSchools->groupBy(function ($school) {
            $parts = explode(',', $school->location ?? '');
            return trim($parts[0] ?? 'Dakar');
        })->map(function ($items, $city) use ($cityCoordinates, $normalize) {
            $schoolsCollection = collect($items)->map(fn($s) => (object)[
                'id'       => $s->id,
                'name'     => $s->name,
                'code'     => $s->code,
                'status'   => $s->status,
                'plan_name'=> $s->package ?? 'Standard',
            ]);

            $cityNorm = $normalize($city);
            $geo = ['lat' => 14.7167, 'lng' => -17.4677, 'top' => '45%', 'left' => '40%'];

            foreach ($cityCoordinates as $key => $coords) {
                if (str_contains($cityNorm, $key)) {
                    $geo = $coords;
                    break;
                }
            }

            return [
                'city'             => $city,
                'region'           => 'Afrique de l\'Ouest & Centrale',
                'school_count'     => count($items),
                'growth'           => '+' . (count($items) * 5) . '%',
                'students'         => collect($items)->sum('studentsCount'),
                'enterprise_count' => collect($items)->filter(fn($i) => str_contains(strtolower($i->package ?? ''), 'enterprise') || str_contains(strtolower($i->package ?? ''), 'premium'))->count(),
                'status'           => 'Active',
                'schools'          => $schoolsCollection,
                'lat'              => $geo['lat'],
                'lng'              => $geo['lng'],
                'top'              => $geo['top'],
                'left'             => $geo['left'],
            ];
        })->values();

        $allSchoolsCollection = $allSchools->map(function ($s) use ($cityCoordinates, $normalize) {
            $locNorm = $normalize($s->location ?? '');
            $nameNorm = $normalize($s->name ?? '');
            $baseGeo = ['lat' => 14.7167, 'lng' => -17.4677];

            foreach ($cityCoordinates as $key => $geo) {
                if (str_contains($locNorm, $key) || str_contains($nameNorm, $key)) {
                    $baseGeo = $geo;
                    break;
                }
            }

            // Micro-offset coordinates so multiple schools in the same city are distinctly visible when zooming in
            $latOffset = (($s->id % 5) - 2) * 0.012;
            $lngOffset = ((($s->id * 3) % 5) - 2) * 0.012;

            return (object)[
                'id'             => $s->id,
                'name'           => $s->name,
                'code'           => $s->code,
                'location'       => $s->location,
                'status'         => strtolower($s->status ?? 'actif'),
                'type'           => $s->type ?? 'Établissement',
                'plan_name'      => $s->package ?? 'Standard',
                'students_count' => $s->studentsCount,
                'lat'            => round($baseGeo['lat'] + $latOffset, 4),
                'lng'            => round($baseGeo['lng'] + $lngOffset, 4),
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

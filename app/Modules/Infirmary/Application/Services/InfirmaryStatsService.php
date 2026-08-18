<?php

namespace App\Modules\Infirmary\Application\Services;

use App\Modules\Infirmary\Domain\Repositories\InterventionRepositoryInterface;
use App\Modules\Infirmary\Domain\Repositories\MedicationMovementRepositoryInterface;
use App\Modules\Infirmary\Domain\Repositories\MedicationRepositoryInterface;
use Illuminate\Support\Carbon;

class InfirmaryStatsService
{
    private InterventionRepositoryInterface $interventionRepository;
    private MedicationRepositoryInterface $medicationRepository;
    private MedicationMovementRepositoryInterface $movementRepository;

    public function __construct(
        InterventionRepositoryInterface $interventionRepository,
        MedicationRepositoryInterface $medicationRepository,
        MedicationMovementRepositoryInterface $movementRepository
    ) {
        $this->interventionRepository = $interventionRepository;
        $this->medicationRepository = $medicationRepository;
        $this->movementRepository = $movementRepository;
    }

    public function dashboardStats(): array
    {
        $lowStockCount = $this->medicationRepository->all()
            ->filter(fn ($m) => in_array($m->status, ['critical', 'to_monitor']))
            ->count();

        return [
            'daily_consultations' => $this->interventionRepository->countToday(),
            'low_stock_alerts' => $lowStockCount,
            'active_incidents' => $this->interventionRepository->activeToday(),
        ];
    }

    public function consultationMotivesToday(): array
    {
        return $this->interventionRepository->motiveCountsToday();
    }

    public function feverTrend(string $motiveName = 'Fièvre'): array
    {
        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek();
        $lastWeekStart = $thisWeekStart->copy()->subWeek();
        $lastWeekEnd = $thisWeekEnd->copy()->subWeek();

        $thisWeek = $this->interventionRepository->motiveCountForRange($motiveName, $thisWeekStart->toDateTimeString(), $thisWeekEnd->toDateTimeString());
        $lastWeek = $this->interventionRepository->motiveCountForRange($motiveName, $lastWeekStart->toDateTimeString(), $lastWeekEnd->toDateTimeString());

        $percentChange = $lastWeek > 0
            ? round((($thisWeek - $lastWeek) / $lastWeek) * 100)
            : ($thisWeek > 0 ? 100 : 0);

        return [
            'motive' => $motiveName,
            'thisWeek' => $thisWeek,
            'lastWeek' => $lastWeek,
            'percentChange' => $percentChange,
            'increased' => $thisWeek > $lastWeek && $lastWeek > 0,
        ];
    }

    public function stockoutRisk(): ?array
    {
        $medications = $this->medicationRepository->all();
        $riskiest = null;

        foreach ($medications as $medication) {
            $dailyRate = $this->movementRepository->dailyUsageRate($medication->id);

            if ($dailyRate <= 0) {
                continue;
            }

            $daysRemaining = (int) floor($medication->quantity / $dailyRate);

            if ($riskiest === null || $daysRemaining < $riskiest['daysRemaining']) {
                $riskiest = ['medication' => $medication, 'daysRemaining' => $daysRemaining];
            }
        }

        return $riskiest;
    }
}

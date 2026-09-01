<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Models\AIModel;
use App\Modules\SuperAdmin\Domain\Models\Invoice;
use App\Modules\SuperAdmin\Domain\Models\School;
use App\Modules\SuperAdmin\Domain\Models\SystemLog;
use App\Modules\Canteen\Domain\Models\MealRecord;
use App\Modules\Library\Domain\Models\Loan;
use App\Modules\Infirmary\Domain\Models\Intervention;
use App\Modules\Transport\Domain\Models\TripLog;

class GetAiAnalyticsUseCase
{
    public function execute(): array
    {
        // 1. Real KPIs — a genuine 0 is shown as 0, no fabricated fallback.
        $totalSchools = School::count();
        $activeSchools = School::where('status', 'active')->orWhere('status', 'actif')->count();

        $premiumSchools = School::all()->filter(function ($s) {
            $pName = $s->plan_name ?? '';
            return str_contains($pName, 'Pro') || str_contains($pName, 'Enterprise') || str_contains($pName, 'Premium');
        })->count();

        $totalStudents = (int) School::sum('students_count');
        $totalPaidRevenue = (float) Invoice::where('status', 'paid')->sum('amount');
        $pendingRevenue = (float) Invoice::where('status', 'pending')->sum('amount');
        $errorLogsCount = SystemLog::where('level', 'error')->count();

        $aiModels = AIModel::where('status', 'active')->get();

        $kpis = [
            'total_schools'    => $totalSchools,
            'active_schools'   => $activeSchools,
            'premium_schools'  => $premiumSchools,
            'total_students'   => number_format($totalStudents, 0, ',', ' '),
            'total_revenue'    => number_format($totalPaidRevenue, 0, ',', ' '),
            'pending_revenue'  => number_format($pendingRevenue, 0, ',', ' '),
            'error_logs'       => $errorLogsCount,
        ];

        // 2. Real module adoption — % of schools with at least one genuine
        // record in each module, over the last 60 days. Replaces fake
        // "Tuteur IA" / "Tuteur Virtuel" stats — no such feature exists
        // anywhere in the app.
        $schoolIds = School::pluck('id');
        $totalSchoolsForAdoption = max($schoolIds->count(), 1);
        $since = now()->subDays(60);

        $adoptionFor = fn ($model) => $model::where('created_at', '>=', $since)
            ->whereIn('school_id', $schoolIds)
            ->distinct('school_id')
            ->count('school_id');

        $engagementData = [
            [
                'label' => 'Écoles Actives sur la Cantine',
                'value' => round((MealRecord::where('date', '>=', $since)->whereIn('school_id', $schoolIds)->distinct('school_id')->count('school_id') / $totalSchoolsForAdoption) * 100) . '%',
                'color' => 'emerald',
            ],
            [
                'label' => 'Écoles Actives sur la Bibliothèque',
                'value' => round(($adoptionFor(Loan::class) / $totalSchoolsForAdoption) * 100) . '%',
                'color' => 'blue',
            ],
            [
                'label' => 'Écoles Actives sur l\'Infirmerie',
                'value' => round(($adoptionFor(Intervention::class) / $totalSchoolsForAdoption) * 100) . '%',
                'color' => 'indigo',
            ],
            [
                'label' => 'Écoles Actives sur le Transport',
                'value' => round((TripLog::where('trip_date', '>=', $since)->whereIn('school_id', $schoolIds)->distinct('school_id')->count('school_id') / $totalSchoolsForAdoption) * 100) . '%',
                'color' => 'purple',
            ],
        ];

        // 3. Real risk flags — no more invented schools/scenarios. Genuine
        // signals: suspended status, unpaid/overdue invoices, near-zero
        // enrollment on a paid plan.
        $predictions = [];

        foreach (School::where('status', 'suspendu')->get() as $school) {
            $predictions[] = [
                'school' => $school->name,
                'severity' => 'high',
                'risk' => 'Établissement Suspendu',
                'reason' => 'Le statut de cet établissement est actuellement "suspendu".',
            ];
        }

        $overdueBySchool = Invoice::whereIn('status', ['overdue', 'failed'])
            ->get()
            ->groupBy('school_name');
        foreach ($overdueBySchool as $schoolName => $invoices) {
            $predictions[] = [
                'school' => $schoolName,
                'severity' => 'medium',
                'risk' => 'Factures en Retard',
                'reason' => $invoices->count() . ' facture(s) impayée(s) pour un total de ' . number_format($invoices->sum('amount'), 0, ',', ' ') . ' FCFA.',
            ];
        }

        foreach (School::where('plan_name', '!=', 'Starter')->where('students_count', '<', 10)->get() as $school) {
            $predictions[] = [
                'school' => $school->name,
                'severity' => 'low',
                'risk' => 'Sous-utilisation du Forfait',
                'reason' => "Forfait {$school->plan_name} avec seulement {$school->students_count} élève(s) enregistré(s).",
            ];
        }

        return [
            'kpis'           => $kpis,
            'engagementData' => $engagementData,
            'predictions'    => $predictions,
            'aiModels'       => $aiModels,
        ];
    }
}

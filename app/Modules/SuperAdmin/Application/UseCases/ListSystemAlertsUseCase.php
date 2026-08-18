<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\SystemAlertRepositoryInterface;

class ListSystemAlertsUseCase
{
    public function __construct(
        private SystemAlertRepositoryInterface $alertRepository
    ) {}

    public function execute(): array
    {
        $rules = $this->alertRepository->getAll();

        // Build $alerts for the table in system-alerts.blade.php. Status reflects
        // the rule's real is_active flag — there is no incident-lifecycle tracking
        // (no "investigating" / "awaiting triage" state actually exists).
        $alerts = collect($rules)->map(function ($rule) {
            return [
                'id'            => $rule->id,
                'severity_type' => $rule->severity,
                'category'      => $rule->metric ?? 'Système',
                'title'         => $rule->title ?? 'Alerte Système',
                'context'       => 'Seuil: ' . ($rule->threshold ?? 'N/A') . ' | Métrique: ' . ($rule->metric ?? 'CPU'),
                'details'       => ($rule->title ?? 'Règle') . ' (seuil: ' . ($rule->threshold ?? 'N/A') . ')',
                'is_active'     => $rule->isActive,
            ];
        })->toArray();

        $activeRulesCount = collect($rules)->where('isActive', true)->count();
        $totalRulesCount = count($rules);

        $kpis = [
            'total_alerts' => $totalRulesCount,
            'critical_triggers' => collect($rules)->where('severity', 'critical')->count(),
            'active_rules' => $activeRulesCount,
            'active_rules_pct' => $totalRulesCount > 0 ? round(($activeRulesCount / $totalRulesCount) * 100) : 0,
        ];

        $dbSettings = collect(\App\Modules\SuperAdmin\Domain\Models\GlobalSetting::all())->mapWithKeys(fn($s) => [$s->key => $s->value]);

        $configurations = [
            ['title' => 'Seuil Charge CPU', 'subtitle' => 'Déclenchement alerte', 'value' => ($dbSettings->get('alert_server_load_percent', '85')) . '%'],
            ['title' => 'Retard Paiement', 'subtitle' => 'Seuil d\'alerte', 'value' => ($dbSettings->get('alert_payment_delay_days', '15')) . ' Jours'],
            ['title' => 'Baisse Assiduité', 'subtitle' => 'Seuil d\'alerte globale', 'value' => ($dbSettings->get('alert_attendance_drop_percent', '15')) . '%'],
        ];

        // Real breakdown of configured rules by severity (no historical alert-firing
        // log exists, so a time-based trend cannot be honestly derived).
        $severityBreakdown = [
            ['label' => 'Critique', 'count' => collect($rules)->where('severity', 'critical')->count(), 'color' => 'bg-red-500'],
            ['label' => 'Avertissement', 'count' => collect($rules)->where('severity', 'warning')->count(), 'color' => 'bg-amber-400'],
            ['label' => 'Info', 'count' => collect($rules)->whereNotIn('severity', ['critical', 'warning'])->count(), 'color' => 'bg-blue-300'],
        ];

        return [
            'rules' => $rules,
            'alerts' => $alerts,
            'kpis' => $kpis,
            'configurations' => $configurations,
            'severityBreakdown' => $severityBreakdown,
        ];
    }
}

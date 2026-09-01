<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Application\UseCases\GetRevenueAnalysisUseCase;
use App\Modules\SuperAdmin\Application\Services\AIService;

class RevenueAnalysisController extends Controller
{
    public function __construct(
        private GetRevenueAnalysisUseCase $getRevenueAnalysisUseCase
    ) {}

    public function index(\Illuminate\Http\Request $request)
    {
        $period = $request->get('period', '6_months');
        $data = $this->getRevenueAnalysisUseCase->execute($period);

        return view('SuperAdmin::revenue-analysis', array_merge($data, ['selectedPeriod' => $period]));
    }

    /**
     * Real trend extrapolation from the last 6 months of actual paid
     * invoices — no fake "94.2% Précision" score. A simple average/trend
     * projection, explicitly framed as indicative, narrated by AI.
     */
    public function aiForecast(AIService $aiService)
    {
        $data = $this->getRevenueAnalysisUseCase->execute('6_months');
        $months = collect($data['months']);

        $recentAvg = $months->take(-3)->avg('total') ?? 0;
        $projectedQuarter = round($recentAvg * 3);

        $firstHalfAvg = $months->take(3)->avg('total') ?? 0;
        $secondHalfAvg = $months->skip(3)->avg('total') ?? 0;
        $trendPct = $firstHalfAvg > 0
            ? round((($secondHalfAvg - $firstHalfAvg) / $firstHalfAvg) * 100, 1)
            : null;

        $stats = [
            'mrr_actuel_fcfa' => $data['mrr'],
            'moyenne_mensuelle_3_derniers_mois_fcfa' => round($recentAvg),
            'projection_trimestre_prochain_fcfa' => $projectedQuarter,
            'tendance_pct_1re_vs_2e_moitie_semestre' => $trendPct,
        ];

        $systemPrompt = "Tu es un analyste financier SaaS pour AcademiaERP. Tu commentes une simple extrapolation de tendance sur des chiffres réels — PAS un modèle prédictif entraîné. Reste honnête sur le caractère indicatif et incertain de l'estimation, ne donne jamais de faux pourcentage de précision ou de confiance.";
        $userPrompt = "Voici les données réelles de revenus (FCFA), calculées à partir des factures payées :\n"
            . json_encode($stats, JSON_UNESCAPED_UNICODE)
            . "\n\nRédige un commentaire court (2 à 3 phrases) sur cette tendance, en précisant explicitement qu'il s'agit d'une simple extrapolation basée sur la moyenne récente, pas d'une prédiction certaine.";

        $result = $aiService->generateText($systemPrompt, $userPrompt, 220);

        return response()->json([
            'success' => $result['success'],
            'commentary' => $result['text'],
            'error' => $result['error'],
            'stats' => $stats,
        ]);
    }
}

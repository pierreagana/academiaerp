<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Application\UseCases\ListSystemLogsUseCase;
use App\Modules\SuperAdmin\Application\Services\AIService;
use App\Modules\SuperAdmin\Domain\Models\SystemLog;
use Illuminate\Support\Facades\DB;

class SystemLogsController extends Controller
{
    public function __construct(
        private ListSystemLogsUseCase $listSystemLogsUseCase
    ) {}

    /**
     * Real log-based aggregates (no more fake "98.5%"/"5 requêtes"/"99.98%
     * Uptime" — those fields don't exist anywhere in system_logs), narrated
     * by AI into a short summary.
     */
    public function aiAuditSummary(AIService $aiService)
    {
        $sevenDaysAgo = now()->subDays(7);
        $fourteenDaysAgo = now()->subDays(14);

        $thisWeekCount = SystemLog::where('created_at', '>=', $sevenDaysAgo)->count();
        $lastWeekCount = SystemLog::whereBetween('created_at', [$fourteenDaysAgo, $sevenDaysAgo])->count();
        $errorCount = SystemLog::where('created_at', '>=', $sevenDaysAgo)->whereIn('level', ['error', 'critical'])->count();
        $topSource = SystemLog::where('created_at', '>=', $sevenDaysAgo)
            ->select('source', DB::raw('count(*) as c'))
            ->groupBy('source')
            ->orderByDesc('c')
            ->first();

        $changePct = $lastWeekCount > 0
            ? round((($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100)
            : null;

        $stats = [
            'logs_7_derniers_jours' => $thisWeekCount,
            'logs_semaine_precedente' => $lastWeekCount,
            'variation_pct' => $changePct,
            'erreurs_critiques_7j' => $errorCount,
            'source_la_plus_active' => $topSource->source ?? 'aucune',
            'occurrences_source_active' => $topSource->c ?? 0,
        ];

        $systemPrompt = "Tu es un analyste sécurité pour AcademiaERP, un SaaS de gestion scolaire. Tu résumes des statistiques réelles de journaux système en français, de façon factuelle — jamais d'alarmisme si les chiffres sont normaux, jamais de minimisation si les erreurs augmentent nettement.";
        $userPrompt = "Voici les statistiques réelles des journaux système des 7 derniers jours (données SQL, pas d'invention) :\n"
            . json_encode($stats, JSON_UNESCAPED_UNICODE)
            . "\n\nRédige un résumé court (2 à 3 phrases), professionnel, en français, basé strictement sur ces chiffres.";

        $result = $aiService->generateText($systemPrompt, $userPrompt, 200);

        return response()->json([
            'success' => $result['success'],
            'summary' => $result['text'],
            'error' => $result['error'],
            'stats' => $stats,
        ]);
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $level  = $request->get('level', 'all');
        $period = $request->get('period', 'all');

        $query = SystemLog::latest('id');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('message', 'LIKE', "%{$search}%")
                  ->orWhere('source', 'LIKE', "%{$search}%")
                  ->orWhere('ip_address', 'LIKE', "%{$search}%");
            });
        }

        if ($level !== 'all') {
            if ($level === 'error_critical') {
                $query->whereIn('level', ['error', 'critical']);
            } else {
                $query->where('level', $level);
            }
        }

        if ($period !== 'all') {
            $startDate = match ($period) {
                'today'   => now()->startOfDay(),
                '7_days'  => now()->subDays(7),
                '30_days' => now()->subDays(30),
                default   => null,
            };
            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }
        }

        $logs = $query->paginate(15)->withQueryString();

        return view('SuperAdmin::system-logs', compact('logs', 'search', 'level', 'period'));
    }

    public function exportCsv(Request $request)
    {
        $search = $request->get('search');
        $level  = $request->get('level', 'all');

        $query = SystemLog::latest('id');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('message', 'LIKE', "%{$search}%")
                  ->orWhere('source', 'LIKE', "%{$search}%")
                  ->orWhere('ip_address', 'LIKE', "%{$search}%");
            });
        }

        if ($level !== 'all') {
            $query->where('level', $level);
        }

        $logs = $query->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=journaux_systeme_academia_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Horodatage', 'Niveau', 'Source', 'Message', 'Adresse IP']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    strtoupper($log->level ?? 'INFO'),
                    $log->source ?? 'Système',
                    $log->message,
                    $log->ip_address ?? 'N/A'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

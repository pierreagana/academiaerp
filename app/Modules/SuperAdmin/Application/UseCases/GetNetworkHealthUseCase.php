<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Models\School;
use App\Modules\SuperAdmin\Domain\Models\SystemLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GetNetworkHealthUseCase
{
    /**
     * This platform runs as a single application instance, not a distributed
     * multi-region cluster. Every signal below is measured live against the
     * real database, cache, queue and disk this request is running on —
     * nothing here is simulated or seeded.
     */
    public function execute(): array
    {
        $components = [];

        // Database connectivity + measured response time.
        $dbStart = microtime(true);
        $dbOk = true;
        try {
            DB::select('select 1');
        } catch (\Throwable $e) {
            $dbOk = false;
        }
        $dbLatencyMs = (int) round((microtime(true) - $dbStart) * 1000);
        $components[] = [
            'name' => 'Base de Données',
            'icon' => 'ph-database',
            'status' => $dbOk ? 'ok' : 'down',
            'detail' => $dbOk ? "Réponse en {$dbLatencyMs} ms" : 'Connexion impossible',
        ];

        // Cache read/write round-trip.
        $cacheStart = microtime(true);
        $cacheOk = true;
        try {
            $probeKey = 'network-health-probe';
            Cache::put($probeKey, 1, 5);
            $cacheOk = Cache::get($probeKey) === 1;
        } catch (\Throwable $e) {
            $cacheOk = false;
        }
        $cacheLatencyMs = (int) round((microtime(true) - $cacheStart) * 1000);
        $components[] = [
            'name' => 'Cache',
            'icon' => 'ph-lightning',
            'status' => $cacheOk ? 'ok' : 'down',
            'detail' => $cacheOk ? "Réponse en {$cacheLatencyMs} ms" : 'Cache indisponible',
        ];

        // Queue backlog (real jobs/failed_jobs tables).
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        $components[] = [
            'name' => "File d'Attente",
            'icon' => 'ph-stack',
            'status' => $failedJobs > 0 ? 'warning' : 'ok',
            'detail' => "{$pendingJobs} en attente, {$failedJobs} échoué(s)",
        ];

        // Disk usage on the storage volume.
        $diskTotal = @disk_total_space(storage_path());
        $diskFree = @disk_free_space(storage_path());
        $diskUsedPct = ($diskTotal && $diskTotal > 0) ? (int) round((($diskTotal - $diskFree) / $diskTotal) * 100) : null;
        $components[] = [
            'name' => 'Stockage',
            'icon' => 'ph-hard-drive',
            'status' => $diskUsedPct === null ? 'unknown' : ($diskUsedPct >= 90 ? 'warning' : 'ok'),
            'detail' => $diskUsedPct === null ? 'Indisponible' : "{$diskUsedPct}% utilisé",
        ];

        $totalSchools = School::count();
        $activeSchools = School::where('status', 'actif')->count();

        $downCount = collect($components)->where('status', 'down')->count();
        $warningCount = collect($components)->where('status', 'warning')->count();

        $overallStatus = $downCount > 0 ? 'critical' : ($warningCount > 0 ? 'warning' : 'ok');

        $kpis = [
            'db_latency_ms' => $dbLatencyMs,
            'db_ok' => $dbOk,
            'pending_jobs' => $pendingJobs,
            'failed_jobs' => $failedJobs,
            'disk_used_pct' => $diskUsedPct,
            'total_schools' => $totalSchools,
            'active_schools' => $activeSchools,
            'overall_status' => $overallStatus,
        ];

        // Recent platform events, from the real system_logs table — no
        // fabricated fallback incident.
        $recentEvents = SystemLog::latest()->take(6)->get()->map(fn ($log) => [
            'timestamp' => $log->created_at->diffForHumans(),
            'level' => $log->level,
            'message' => $log->message,
            'source' => $log->source,
        ])->toArray();

        // Derived operational notices, based on the live checks above only.
        $notices = [];
        if (!$dbOk) {
            $notices[] = ['type' => 'error', 'title' => 'Base de données inaccessible', 'description' => 'La connexion à la base de données a échoué lors de la dernière vérification.'];
        }
        if ($failedJobs > 0) {
            $notices[] = ['type' => 'warning', 'title' => $failedJobs . ' tâche(s) en échec', 'description' => "La file d'attente contient des tâches ayant échoué et nécessitant une attention."];
        }
        if ($diskUsedPct !== null && $diskUsedPct >= 90) {
            $notices[] = ['type' => 'warning', 'title' => 'Espace disque faible', 'description' => "Le stockage est utilisé à {$diskUsedPct}%."];
        }

        return [
            'components' => $components,
            'kpis' => $kpis,
            'notices' => $notices,
            'recentEvents' => $recentEvents,
        ];
    }
}

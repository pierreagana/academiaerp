<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Application\UseCases\ListSystemLogsUseCase;
use App\Modules\SuperAdmin\Domain\Models\SystemLog;

class SystemLogsController extends Controller
{
    public function __construct(
        private ListSystemLogsUseCase $listSystemLogsUseCase
    ) {}

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

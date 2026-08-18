<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Modules\SuperAdmin\Application\UseCases\GetDashboardStatsUseCase;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function __construct(
        private GetDashboardStatsUseCase $getDashboardStatsUseCase
    ) {}

    public function index()
    {
        $stats = $this->getDashboardStatsUseCase->execute();
        $recentSchools = \App\Modules\SuperAdmin\Domain\Models\School::latest()->take(5)->get();
        
        $totalSchools = \App\Modules\SuperAdmin\Domain\Models\School::count();
        $premiumCount = \App\Modules\SuperAdmin\Domain\Models\School::where('plan_name', 'like', '%Premium%')->count();
        $proCount = \App\Modules\SuperAdmin\Domain\Models\School::where('plan_name', 'like', '%Pro%')->count();
        $basicCount = $totalSchools - $premiumCount - $proCount;

        $planStats = [
            'premium' => $totalSchools > 0 ? round(($premiumCount / $totalSchools) * 100) : 0,
            'pro' => $totalSchools > 0 ? round(($proCount / $totalSchools) * 100) : 0,
            'basic' => $totalSchools > 0 ? round(($basicCount / $totalSchools) * 100) : 0,
            'total' => $totalSchools
        ];

        $activeTicketsCount = \App\Modules\SuperAdmin\Domain\Models\SupportTicket::whereIn('status', ['open', 'in_progress'])->count();

        return view('SuperAdmin::dashboard', compact('stats', 'recentSchools', 'planStats', 'activeTicketsCount'));
    }
}

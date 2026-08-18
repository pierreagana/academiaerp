<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Application\UseCases\GetRevenueAnalysisUseCase;

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
}

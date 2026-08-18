<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Application\UseCases\GetAiAnalyticsUseCase;

class AiAnalyticsController extends Controller
{
    public function __construct(
        private GetAiAnalyticsUseCase $getAiAnalyticsUseCase
    ) {}

    public function index()
    {
        $data = $this->getAiAnalyticsUseCase->execute();

        return view('SuperAdmin::ai-analytics', $data);
    }
}

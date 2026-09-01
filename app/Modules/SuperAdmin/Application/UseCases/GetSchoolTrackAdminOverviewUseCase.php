<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SchoolTrack\Application\Services\SchoolTrackAccessService;
use App\Modules\SchoolTrack\Domain\Models\SchoolTrackSubscription;

class GetSchoolTrackAdminOverviewUseCase
{
    public function __construct(private SchoolTrackAccessService $access) {}

    public function execute(?string $search = null, ?string $status = null, ?string $plan = null): array
    {
        $active = SchoolTrackSubscription::where('status', 'active')->where('expires_at', '>', now());

        $kpis = [
            'total_revenue' => (float) SchoolTrackSubscription::sum('amount_paid'),
            'active_count' => (clone $active)->count(),
            'monthly_count' => (clone $active)->where('plan', SchoolTrackSubscription::PLAN_MONTHLY)->count(),
            'yearly_count' => (clone $active)->where('plan', SchoolTrackSubscription::PLAN_YEARLY)->count(),
        ];

        $query = SchoolTrackSubscription::with('parent')->orderByDesc('created_at');

        if ($search) {
            $query->whereHas('parent', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($plan) {
            $query->where('plan', $plan);
        }

        return [
            'moduleEnabled' => $this->access->isModuleEnabled(),
            'kpis' => $kpis,
            'subscriptions' => $query->paginate(15)->withQueryString(),
        ];
    }
}

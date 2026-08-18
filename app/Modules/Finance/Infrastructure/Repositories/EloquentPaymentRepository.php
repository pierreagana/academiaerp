<?php

namespace App\Modules\Finance\Infrastructure\Repositories;

use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\Finance\Domain\Repositories\PaymentRepositoryInterface;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Payment::create($data);
    }

    public function forStudent($studentId, ?string $type = null)
    {
        return Payment::where('school_id', auth()->user()->school_id)
            ->where('student_id', $studentId)
            ->when($type, fn ($query) => $query->where('type', $type))
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get();
    }

    public function monthlyTotals(int $schoolId, int $months = 7, string $type = 'tuition')
    {
        $start = now()->subMonths($months - 1)->startOfMonth();

        return Payment::where('school_id', $schoolId)
            ->where('type', $type)
            ->where('paid_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as ym, SUM(amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');
    }
}

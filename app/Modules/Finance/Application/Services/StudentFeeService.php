<?php

namespace App\Modules\Finance\Application\Services;

use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Finance\Domain\Models\FeeLevel;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\Finance\Domain\Models\Scholarship;
use App\Modules\Finance\Domain\Repositories\PaymentRepositoryInterface;
use Illuminate\Support\Collection;

class StudentFeeService
{
    private PaymentRepositoryInterface $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Compute the applicable fee structure, totals, status and derived
     * installment schedule for one student. Nothing here is persisted —
     * it's recomputed from FeeLevel + the sum of Payments every time.
     */
    /**
     * @param string|null $zone For type 'transport': the student's enrolled
     *   route zone, if known — resolves the per-zone tariff instead of the
     *   old single school-wide sentinel (see Route price sync in
     *   SchoolDashboard's TransportController). Null falls back to the
     *   sentinel, so a school with no zone-priced routes yet (or a student
     *   not enrolled) behaves exactly as before.
     */
    public function summaryFor(Student $student, string $type = 'tuition', ?string $zone = null): array
    {
        $academicYear = $student->academic_year;
        $level = $student->academicClass?->level;

        $feeLevel = $type === 'tuition'
            ? (($level && $academicYear)
                ? FeeLevel::where('school_id', $student->school_id)
                    ->where('type', 'tuition')
                    ->where('level', $level)
                    ->where('academic_year', $academicYear)
                    ->first()
                : null)
            : ($academicYear
                ? FeeLevel::where('school_id', $student->school_id)
                    ->where('type', $type)
                    ->where('level', $type === 'transport' && $zone ? $zone : FeeLevel::schoolWideLevelFor($type))
                    ->where('academic_year', $academicYear)
                    ->first()
                : null);

        $totalPaid = (float) Payment::where('student_id', $student->id)->where('type', $type)->sum('amount');

        if (!$feeLevel) {
            return [
                'feeLevel' => null,
                'total' => 0.0,
                'paid' => $totalPaid,
                'scholarshipCredit' => 0.0,
                'remaining' => 0.0,
                'status' => 'unconfigured',
                'schedule' => [],
                'nextDueDate' => null,
            ];
        }

        $total = $feeLevel->total_amount;

        // A scholarship is a real reduction of what the family owes for tuition
        // (not a stipend paid alongside it) — only "active" scholarships count,
        // and only against tuition, never cantine/transport. Capped at $total so
        // a scholarship can never flip the balance negative.
        $scholarshipCredit = 0.0;
        if ($type === 'tuition') {
            $scholarshipCredit = (float) Scholarship::where('student_id', $student->id)
                ->where('status', 'active')
                ->get()
                ->sum(fn (Scholarship $s) => $s->annual_amount);
            $scholarshipCredit = min($scholarshipCredit, $total);
        }

        $effectivePaid = $totalPaid + $scholarshipCredit;
        $remaining = max($total - $effectivePaid, 0);

        // The 1st due date carries both the registration fee and the 1st monthly
        // installment together (one combined payment) — every later installment is
        // then spaced a month apart from that same 1st due date.
        $lines = [];
        if ($feeLevel->installments_count < 1) {
            $lines[] = ['label' => 'Échéance 1 (Inscription)', 'amount' => (float) $feeLevel->registration_fee, 'due_date' => $feeLevel->start_date->copy()];
        } else {
            for ($i = 1; $i <= $feeLevel->installments_count; $i++) {
                $amount = $feeLevel->installmentAmount($i);
                $label = 'Échéance ' . $i;
                if ($i === 1) {
                    $amount += (float) $feeLevel->registration_fee;
                    $label = 'Échéance 1 (Inscription + 1er versement)';
                }
                $lines[] = [
                    'label' => $label,
                    'amount' => $amount,
                    'due_date' => $feeLevel->start_date->copy()->addMonths($i - 1),
                ];
            }
        }

        $schedule = [];
        $cumulative = 0.0;
        $nextUnpaidAssigned = false;
        $nextDueDate = null;

        foreach ($lines as $line) {
            $cumulative += $line['amount'];

            if ($effectivePaid >= $cumulative) {
                $status = 'paid';
            } elseif (!$nextUnpaidAssigned) {
                $status = 'due';
                $nextUnpaidAssigned = true;
                $nextDueDate = $line['due_date'];
            } else {
                $status = 'upcoming';
            }

            $schedule[] = [
                'label' => $line['label'],
                'amount' => $line['amount'],
                'due_date' => $line['due_date'],
                'status' => $status,
            ];
        }

        if ($remaining <= 0) {
            $overallStatus = 'paid';
        } else {
            $isLate = collect($schedule)->contains(fn ($l) => $l['status'] !== 'paid' && $l['due_date']->lte(now()));
            $overallStatus = $isLate ? 'late' : ($effectivePaid > 0 ? 'partial' : 'pending');
        }

        return [
            'feeLevel' => $feeLevel,
            'total' => $total,
            'paid' => $totalPaid,
            'scholarshipCredit' => $scholarshipCredit,
            'remaining' => $remaining,
            'status' => $overallStatus,
            'schedule' => $schedule,
            'nextDueDate' => $nextDueDate,
        ];
    }

    /**
     * Fee summary for every student of the school. Recomputes per student
     * (small school datasets, matches this codebase's existing non-batched
     * query style elsewhere).
     */
    public function summaries(int $schoolId, string $type = 'tuition'): Collection
    {
        return Student::where('school_id', $schoolId)
            ->with('academicClass')
            ->get()
            ->map(fn (Student $student) => array_merge(['student' => $student], $this->summaryFor($student, $type)));
    }

    public function overallStats(int $schoolId, string $type = 'tuition'): array
    {
        $summaries = $this->summaries($schoolId, $type);

        $totalExpected = $summaries->sum('total');
        $totalCollected = (float) Payment::where('school_id', $schoolId)->where('type', $type)->sum('amount');
        $outstanding = max($totalExpected - $totalCollected, 0);
        $rate = $totalExpected > 0 ? round(($totalCollected / $totalExpected) * 100, 1) : 0.0;

        return [
            'totalExpected' => $totalExpected,
            'totalCollected' => $totalCollected,
            'outstanding' => $outstanding,
            'collectionRate' => $rate,
            'activeStudents' => $summaries->count(),
        ];
    }

    /**
     * Accounts whose next due installment is more than $daysThreshold days
     * overdue, with the total remaining balance across them.
     */
    public function overdueStats(int $schoolId, int $daysThreshold = 30, string $type = 'tuition'): array
    {
        $overdue = $this->summaries($schoolId, $type)
            ->filter(fn ($s) => $s['status'] === 'late' && $s['nextDueDate'])
            ->filter(fn ($s) => now()->diffInDays($s['nextDueDate'], true) > $daysThreshold);

        return [
            'count' => $overdue->count(),
            'amount' => $overdue->sum('remaining'),
        ];
    }

    public function monthlyTrend(int $schoolId, int $months = 7, string $type = 'tuition'): array
    {
        $totals = $this->paymentRepository->monthlyTotals($schoolId, $months, $type);
        $result = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $result[] = [
                'label' => ucfirst($date->translatedFormat('M')),
                'total' => (float) ($totals[$key] ?? 0),
            ];
        }

        return $result;
    }

    public function lateAlerts(int $schoolId, int $limit = 4, string $type = 'tuition'): array
    {
        $alerts = $this->summaries($schoolId, $type)
            ->filter(fn ($s) => $s['status'] === 'late' && $s['nextDueDate'])
            ->map(function ($s) {
                // nextDueDate is guaranteed <= now here (filtered by 'late' status above).
                $s['daysLate'] = (int) floor(now()->diffInDays($s['nextDueDate'], true));
                return $s;
            })
            ->sortByDesc('daysLate')
            ->take($limit)
            ->values();

        return $alerts->all();
    }
}

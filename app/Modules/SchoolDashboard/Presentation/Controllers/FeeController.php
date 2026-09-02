<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Finance\Application\DTOs\CreateFeeLevelDTO;
use App\Modules\Finance\Application\DTOs\CreatePaymentDTO;
use App\Modules\Finance\Application\DTOs\UpdateFeeLevelDTO;
use App\Modules\Finance\Application\Services\StudentFeeService;
use App\Modules\Finance\Application\UseCases\CreateFeeLevelUseCase;
use App\Modules\Finance\Application\UseCases\CreatePaymentUseCase;
use App\Modules\Finance\Application\UseCases\DeleteFeeLevelUseCase;
use App\Modules\Finance\Application\UseCases\UpdateFeeLevelUseCase;
use App\Modules\Finance\Domain\Models\FeeLevel;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\Finance\Domain\Repositories\FeeLevelRepositoryInterface;
use App\Modules\Finance\Domain\Repositories\PaymentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class FeeController extends Controller
{
    public function overview(StudentFeeService $feeService)
    {
        $schoolId = auth()->user()->school_id;

        $stats = $feeService->overallStats($schoolId);
        $trend = $feeService->monthlyTrend($schoolId);
        $lateAlerts = $feeService->lateAlerts($schoolId, 4);

        return view('SchoolDashboard::finance.overview', compact('stats', 'trend', 'lateAlerts'));
    }

    public function payments(Request $request, StudentFeeService $feeService)
    {
        $schoolId = auth()->user()->school_id;

        $type = $request->get('type', 'tuition');
        abort_unless(array_key_exists($type, FeeLevel::TYPES), 404);

        $search = $request->get('search');
        $classId = $request->get('class_id');
        $status = $request->get('status');
        $page = (int) $request->get('page', 1);
        $perPage = 10;

        $summaries = $feeService->summaries($schoolId, $type);

        if (!empty($search)) {
            $summaries = $summaries->filter(function ($s) use ($search) {
                $name = $s['student']->first_name . ' ' . $s['student']->last_name;
                return str_contains(strtolower($name), strtolower($search))
                    || str_contains(strtolower($s['student']->roll_number ?? ''), strtolower($search));
            });
        }

        if (!empty($classId)) {
            $summaries = $summaries->filter(fn ($s) => $s['student']->academic_class_id == $classId);
        }

        if (!empty($status)) {
            $summaries = $summaries->filter(fn ($s) => $s['status'] === $status);
        }

        $summaries = $summaries->values();

        $students = new LengthAwarePaginator(
            $summaries->forPage($page, $perPage),
            $summaries->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $classes = AcademicClass::where('school_id', $schoolId)->orderBy('name')->get();
        $allStudents = Student::where('school_id', $schoolId)->orderBy('first_name')->get();

        return view('SchoolDashboard::finance.payments', compact('students', 'classes', 'allStudents', 'search', 'classId', 'status', 'type'));
    }

    public function storePayment(Request $request, CreatePaymentUseCase $useCase)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'type' => ['required', 'string', 'in:' . implode(',', array_keys(FeeLevel::TYPES))],
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', 'string', 'in:' . implode(',', array_keys(Payment::METHODS))],
            'paid_at' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ]);

        $data['recorded_by'] = auth()->id();

        $dto = new CreatePaymentDTO($data);
        $useCase->execute($dto);

        return back()->with('success', 'Paiement enregistré avec succès.');
    }

    public function walletRechargeRequests()
    {
        $schoolId = auth()->user()->school_id;

        $requests = \App\Modules\Finance\Domain\Models\WalletRechargeRequest::where('school_id', $schoolId)
            ->where('status', \App\Modules\Finance\Domain\Models\WalletRechargeRequest::STATUS_PENDING)
            ->with('parent')
            ->latest()
            ->get();

        return view('SchoolDashboard::finance.wallet-recharges', compact('requests'));
    }

    public function approveWalletRecharge($id, \App\Modules\Finance\Application\Services\WalletRechargeService $rechargeService)
    {
        $schoolId = auth()->user()->school_id;
        $rechargeRequest = \App\Modules\Finance\Domain\Models\WalletRechargeRequest::where('school_id', $schoolId)->findOrFail($id);
        $rechargeService->approve($rechargeRequest, auth()->user());

        return back()->with('success', 'Recharge confirmée et créditée au portefeuille du parent.');
    }

    public function rejectWalletRecharge(Request $request, $id, \App\Modules\Finance\Application\Services\WalletRechargeService $rechargeService)
    {
        $schoolId = auth()->user()->school_id;
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);
        $rechargeRequest = \App\Modules\Finance\Domain\Models\WalletRechargeRequest::where('school_id', $schoolId)->findOrFail($id);
        $rechargeService->reject($rechargeRequest, auth()->user(), $data['reason'] ?? null);

        return back()->with('success', 'Demande de recharge refusée.');
    }

    public function config(FeeLevelRepositoryInterface $repository, Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $type = $request->get('type', 'tuition');
        abort_unless(array_key_exists($type, FeeLevel::TYPES), 404);

        $feeLevels = $repository->all($type);
        $levels = AcademicClass::where('school_id', $schoolId)->pluck('level')->filter()->unique()->values();
        $zones = \App\Modules\Transport\Domain\Models\Route::where('school_id', $schoolId)
            ->whereNotNull('zone')
            ->distinct()
            ->orderBy('zone')
            ->pluck('zone');

        return view('SchoolDashboard::finance.config', compact('feeLevels', 'levels', 'zones', 'type'));
    }

    public function storeFeeLevel(Request $request, CreateFeeLevelUseCase $useCase)
    {
        $data = $this->validateFeeLevel($request);

        // Tuition can be created for several levels at once (checkbox list) — one
        // identical fee structure gets created per selected level. Cantine (school-wide)
        // and transport (one zone) always submit $data['level'] as a plain string.
        if ($data['type'] === 'tuition' && is_array($data['level'])) {
            $levels = $data['level'];
            foreach ($levels as $level) {
                $useCase->execute(new CreateFeeLevelDTO(array_merge($data, ['level' => $level])));
            }
            $message = count($levels) > 1
                ? count($levels) . ' structures de frais créées avec succès.'
                : 'Structure de frais créée avec succès.';
        } else {
            $useCase->execute(new CreateFeeLevelDTO($data));
            $message = 'Structure de frais créée avec succès.';
        }

        return redirect()->route('school.finance.fees.config', ['type' => $data['type']])->with('success', $message);
    }

    public function updateFeeLevel(Request $request, $id, UpdateFeeLevelUseCase $useCase)
    {
        $data = $this->validateFeeLevel($request, $id);

        $dto = new UpdateFeeLevelDTO($data);
        $useCase->execute($id, $dto);

        return redirect()->route('school.finance.fees.config', ['type' => $data['type']])->with('success', 'Structure de frais mise à jour avec succès.');
    }

    public function destroyFeeLevel(Request $request, $id, DeleteFeeLevelUseCase $useCase)
    {
        $type = $request->get('type', 'tuition');
        $useCase->execute($id);

        return redirect()->route('school.finance.fees.config', ['type' => $type])->with('success', 'Structure de frais supprimée avec succès.');
    }

    private function validateFeeLevel(Request $request, $ignoreId = null): array
    {
        $baseRules = [
            'type' => ['required', 'string', 'in:' . implode(',', array_keys(FeeLevel::TYPES))],
            'academic_year' => ['required', 'string', 'max:20'],
            'registration_fee' => ['required', 'numeric', 'min:0'],
            'installments_count' => ['required', 'integer', 'min:0', 'max:12'],
            'start_date' => ['required', 'date'],
            // Optional custom per-installment breakdown — null/absent means every
            // installment is an equal split of total_scolarite (see below).
            'monthly_amounts' => ['nullable', 'array'],
            'monthly_amounts.*' => ['numeric', 'min:0'],
            // Always user-entered — the total of all installments (registration fee is
            // separate). monthly_fee is never typed directly; it's always derived from
            // this, either as an even split or as the average of a custom breakdown.
            'total_scolarite' => ['required', 'numeric', 'min:0'],
        ];

        // Creating a tuition structure: a checkbox list lets one submission pick several
        // levels at once (one structure per level). Transport is one zone per submission.
        // Editing always targets one existing row, so both keep the single-level shape.
        $isCreate = $ignoreId === null;
        $type = $request->input('type');
        $schoolId = auth()->user()->school_id;
        $academicYear = $request->input('academic_year');

        if ($isCreate && $type === 'tuition') {
            $baseRules['level'] = ['required', 'array', 'min:1'];
            $baseRules['level.*'] = [
                'string', 'max:255',
                Rule::unique('fee_levels', 'level')
                    ->where(fn ($q) => $q->where('school_id', $schoolId)->where('academic_year', $academicYear))
                    ->whereNull('deleted_at'),
            ];
        } elseif ($isCreate && $type === 'transport') {
            $baseRules['level'] = [
                'required', 'string', 'max:255',
                Rule::unique('fee_levels', 'level')
                    ->where(fn ($q) => $q->where('school_id', $schoolId)->where('academic_year', $academicYear))
                    ->whereNull('deleted_at'),
            ];
        } else {
            $baseRules['level'] = ['required_if:type,tuition,transport', 'nullable', 'string', 'max:255'];
        }

        $data = $request->validate($baseRules);

        // Cantine tariffs are school-wide, not per zone/level —
        // force the sentinel level regardless of what (if anything) was posted.
        if ($data['type'] === 'cantine') {
            $data['level'] = FeeLevel::schoolWideLevelFor($data['type']);
        }

        if (!empty($data['monthly_amounts'])) {
            if (count($data['monthly_amounts']) !== (int) $data['installments_count']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'monthly_amounts' => "Le nombre de montants saisis doit correspondre au nombre de mensualités ({$data['installments_count']}).",
                ]);
            }

            $ceiling = (float) $data['total_scolarite'];
            $sum = array_sum($data['monthly_amounts']);
            if ($sum > $ceiling) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'monthly_amounts' => 'Le total réparti (' . number_format($sum, 0, ',', ' ') . ' FCFA) dépasse la Scolarité (Somme Totale) de '
                        . number_format($ceiling, 0, ',', ' ') . ' FCFA.',
                ]);
            }

            // monthly_fee is never typed directly — keep it populated with the average,
            // since it's still a real, always-present column read elsewhere.
            $data['monthly_fee'] = $data['installments_count'] > 0 ? round($sum / (int) $data['installments_count'], 2) : 0;
        } else {
            $data['monthly_amounts'] = null;
            // No custom breakdown: an even split of the entered total across installments.
            $data['monthly_fee'] = $data['installments_count'] > 0 ? round((float) $data['total_scolarite'] / (int) $data['installments_count'], 2) : 0;
        }

        unset($data['total_scolarite']);

        return $data;
    }

    public function studentShow($studentId, Request $request, StudentFeeService $feeService, PaymentRepositoryInterface $paymentRepository)
    {
        $type = $request->get('type', 'tuition');
        abort_unless(array_key_exists($type, FeeLevel::TYPES), 404);

        $student = Student::where('school_id', auth()->user()->school_id)->with('academicClass')->findOrFail($studentId);

        $summary = $feeService->summaryFor($student, $type);
        $transactions = $paymentRepository->forStudent($student->id, $type);

        return view('SchoolDashboard::finance.student_show', compact('student', 'summary', 'transactions', 'type'));
    }

    public function exportPayments(Request $request, StudentFeeService $feeService)
    {
        $schoolId = auth()->user()->school_id;
        $type = $request->get('type', 'tuition');
        abort_unless(array_key_exists($type, FeeLevel::TYPES), 404);
        $summaries = $feeService->summaries($schoolId, $type);

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=suivi_paiements_' . date('Y-m-d') . '.csv',
        ];

        $callback = function () use ($summaries, $type) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Étudiant', 'Classe', 'Total ' . FeeLevel::TYPES[$type], 'Montant Payé', 'Reste à Payer', 'Statut'], ',', '"', '\\');

            foreach ($summaries as $s) {
                fputcsv($file, [
                    $s['student']->first_name . ' ' . $s['student']->last_name,
                    $s['student']->academicClass->name ?? '-',
                    $s['total'],
                    $s['paid'],
                    $s['remaining'],
                    $s['status'],
                ], ',', '"', '\\');
            }

            fclose($file);
        };

        return response()->streamDownload($callback, 'suivi_paiements_' . date('Y-m-d') . '.csv', $headers);
    }

    public function exportStudentStatement($studentId, Request $request, PaymentRepositoryInterface $paymentRepository)
    {
        $type = $request->get('type', 'tuition');
        abort_unless(array_key_exists($type, FeeLevel::TYPES), 404);

        $student = Student::where('school_id', auth()->user()->school_id)->findOrFail($studentId);
        $transactions = $paymentRepository->forStudent($student->id, $type);

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=releve_' . $student->id . '_' . date('Y-m-d') . '.csv',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Référence', 'Méthode', 'Montant'], ',', '"', '\\');

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->paid_at->format('d/m/Y'),
                    $t->reference ?? '-',
                    Payment::METHODS[$t->method] ?? $t->method,
                    $t->amount,
                ], ',', '"', '\\');
            }

            fclose($file);
        };

        return response()->streamDownload($callback, 'releve_' . $student->id . '_' . date('Y-m-d') . '.csv', $headers);
    }
}

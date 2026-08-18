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

    public function config(FeeLevelRepositoryInterface $repository, Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $type = $request->get('type', 'tuition');
        abort_unless(array_key_exists($type, FeeLevel::TYPES), 404);

        $feeLevels = $repository->all($type);
        $levels = AcademicClass::where('school_id', $schoolId)->pluck('level')->filter()->unique()->values();

        return view('SchoolDashboard::finance.config', compact('feeLevels', 'levels', 'type'));
    }

    public function storeFeeLevel(Request $request, CreateFeeLevelUseCase $useCase)
    {
        $data = $this->validateFeeLevel($request);

        $dto = new CreateFeeLevelDTO($data);
        $useCase->execute($dto);

        return redirect()->route('school.finance.fees.config', ['type' => $data['type']])->with('success', 'Structure de frais créée avec succès.');
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
        $data = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', array_keys(FeeLevel::TYPES))],
            'level' => ['required_if:type,tuition', 'nullable', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:20'],
            'registration_fee' => ['required', 'numeric', 'min:0'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'installments_count' => ['required', 'integer', 'min:0', 'max:12'],
            'start_date' => ['required', 'date'],
        ]);

        // Cantine/transport tariffs are school-wide, not per grade level —
        // force the sentinel level regardless of what (if anything) was posted.
        if ($data['type'] !== 'tuition') {
            $data['level'] = FeeLevel::schoolWideLevelFor($data['type']);
        }

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

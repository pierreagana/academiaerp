<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Finance\Application\UseCases\GenerateSalaryExpensesUseCase;
use App\Modules\HR\Application\DTOs\CreateContractDTO;
use App\Modules\HR\Application\DTOs\CreatePayrollComponentDTO;
use App\Modules\HR\Application\DTOs\CreateSalaryGradeDTO;
use App\Modules\HR\Application\Services\HRStatsService;
use App\Modules\HR\Application\UseCases\CreateContractTypeUseCase;
use App\Modules\HR\Application\UseCases\CreateContractUseCase;
use App\Modules\HR\Application\UseCases\CreatePayrollComponentUseCase;
use App\Modules\HR\Application\UseCases\CreateSalaryGradeUseCase;
use App\Modules\HR\Domain\Models\PayrollComponent;
use App\Modules\HR\Domain\Repositories\ContractRepositoryInterface;
use App\Modules\HR\Domain\Repositories\ContractTypeRepositoryInterface;
use App\Modules\HR\Domain\Repositories\PayrollComponentRepositoryInterface;
use App\Modules\HR\Domain\Repositories\SalaryGradeRepositoryInterface;
use Illuminate\Http\Request;

class HRController extends Controller
{
    public function dashboard(HRStatsService $statsService)
    {
        $schoolId = auth()->user()->school_id;

        $stats = $statsService->dashboardStats($schoolId);
        $payrollTrend = $statsService->payrollTrend($schoolId);
        $unconfiguredSalaries = $statsService->unconfiguredSalaries($schoolId);
        $upcomingContractEnds = $statsService->upcomingContractEnds($schoolId);

        return view('SchoolDashboard::hr.dashboard', compact(
            'stats', 'payrollTrend', 'unconfiguredSalaries', 'upcomingContractEnds'
        ));
    }

    public function directory(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $teachers = Teacher::where('school_id', $schoolId)->get()->map(fn ($t) => $this->toDirectoryEntry($t, 'Enseignant', 'school.academic.teachers.show'));
        $staff = Staff::where('school_id', $schoolId)->get()->map(fn ($s) => $this->toDirectoryEntry($s, $s->role ?: 'Personnel', 'school.academic.personnel.edit'));

        $employees = $teachers->concat($staff);

        if ($request->filled('department')) {
            $employees = $employees->where('department', $request->get('department'));
        }

        if ($request->filled('contract_type')) {
            $employees = $employees->where('contract_type', $request->get('contract_type'));
        }

        if ($request->filled('search')) {
            $search = mb_strtolower($request->get('search'));
            $employees = $employees->filter(fn ($e) => str_contains(mb_strtolower($e['name'] . ' ' . $e['title'] . ' ' . $e['employee_id']), $search));
        }

        $departments = $teachers->concat($staff)->pluck('department')->filter()->unique()->sort()->values();

        return view('SchoolDashboard::hr.directory', compact('employees', 'departments'));
    }

    private function toDirectoryEntry($model, string $title, string $showRoute): array
    {
        return [
            'name' => $model->first_name . ' ' . $model->last_name,
            'title' => $title,
            'department' => $model->department ?? null,
            'employee_id' => $model->employee_id,
            'photo_path' => $model->photo_path,
            'contract_type' => $model->contract_type ?? 'cdi',
            'contract_end_date' => $model->contract_end_date,
            'show_url' => route($showRoute, $model->id),
        ];
    }

    public function payroll(HRStatsService $statsService)
    {
        $schoolId = auth()->user()->school_id;
        $roster = $statsService->payrollRoster($schoolId);

        return view('SchoolDashboard::hr.payroll', compact('roster'));
    }

    public function runPayroll(GenerateSalaryExpensesUseCase $useCase)
    {
        $count = $useCase->execute(auth()->user()->school_id, auth()->id());

        $message = $count > 0
            ? "$count fiche(s) de paie générée(s) pour ce mois."
            : 'Tous les salaires de ce mois ont déjà été générés.';

        return back()->with('success', $message);
    }

    public function configuration(SalaryGradeRepositoryInterface $gradeRepository, PayrollComponentRepositoryInterface $componentRepository)
    {
        $componentRepository->ensureDefaults();

        $salaryGrades = $gradeRepository->all();
        $components = $componentRepository->all();

        return view('SchoolDashboard::hr.configuration', compact('salaryGrades', 'components'));
    }

    public function storeSalaryGrade(Request $request, CreateSalaryGradeUseCase $useCase)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'base_salary' => ['required', 'numeric', 'min:0'],
        ]);

        $useCase->execute(new CreateSalaryGradeDTO($data));

        return back()->with('success', 'Échelon ajouté avec succès.');
    }

    public function storeComponent(Request $request, CreatePayrollComponentUseCase $useCase)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:' . implode(',', array_keys(PayrollComponent::TYPES))],
            'rate_type' => ['required', 'string', 'in:' . implode(',', array_keys(PayrollComponent::RATE_TYPES))],
            'rate_value' => ['required', 'numeric', 'min:0'],
        ]);

        $data['enabled'] = true;
        $useCase->execute(new CreatePayrollComponentDTO($data));

        return back()->with('success', 'Rubrique de paie ajoutée avec succès.');
    }

    public function toggleComponent($id, PayrollComponentRepositoryInterface $repository)
    {
        $repository->toggle($id);

        return back()->with('success', 'Rubrique mise à jour avec succès.');
    }

    public function contracts(ContractRepositoryInterface $contractRepository, ContractTypeRepositoryInterface $typeRepository)
    {
        $schoolId = auth()->user()->school_id;
        $typeRepository->ensureDefaults();

        $contracts = $contractRepository->all();
        $types = $typeRepository->all();

        $teachers = Teacher::where('school_id', $schoolId)->orderBy('first_name')->get();
        $staff = Staff::where('school_id', $schoolId)->orderBy('first_name')->get();

        $stats = [
            'active' => $contracts->where('status', 'active')->reject->is_expired->count(),
            'needsReminder' => $contracts->filter->needs_reminder->count(),
            'expired' => $contracts->filter->is_expired->count(),
        ];

        return view('SchoolDashboard::hr.contracts', compact('contracts', 'types', 'teachers', 'staff', 'stats'));
    }

    public function storeContract(Request $request, CreateContractUseCase $useCase)
    {
        $data = $request->validate([
            'holder' => ['required', 'string'],
            'contract_type' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'reminder_days_before' => ['required', 'integer', 'min:1', 'max:365'],
            'notes' => ['nullable', 'string'],
        ]);

        [$holderType, $holderId] = explode(':', $data['holder'], 2);
        unset($data['holder']);
        $data['holder_type'] = $holderType;
        $data['holder_id'] = $holderId;
        $data['status'] = 'active';

        $useCase->execute(new CreateContractDTO($data));

        return back()->with('success', 'Contrat créé avec succès.');
    }

    public function storeContractType(Request $request, CreateContractTypeUseCase $useCase)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);
        $useCase->execute($data['name']);

        return back()->with('success', 'Type de contrat ajouté avec succès.');
    }

    public function destroyContractType($id, ContractTypeRepositoryInterface $repository)
    {
        $repository->delete($id);

        return back()->with('success', 'Type de contrat supprimé avec succès.');
    }

    public function acknowledgeReminder($id, ContractRepositoryInterface $repository)
    {
        $repository->acknowledgeReminder($id);

        return back()->with('success', 'Rappel marqué comme traité.');
    }
}

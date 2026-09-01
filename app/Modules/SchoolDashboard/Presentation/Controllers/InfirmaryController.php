<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Repositories\StudentRepositoryInterface;
use App\Modules\Infirmary\Application\DTOs\AdjustMedicationStockDTO;
use App\Modules\Infirmary\Application\DTOs\CreateInterventionDTO;
use App\Modules\Infirmary\Application\DTOs\CreateMedicationDTO;
use App\Modules\Infirmary\Application\Services\InfirmaryStatsService;
use App\Modules\Infirmary\Application\UseCases\AdjustMedicationStockUseCase;
use App\Modules\Infirmary\Application\UseCases\CreateMedicationUseCase;
use App\Modules\Infirmary\Application\UseCases\RecordInterventionUseCase;
use App\Modules\Infirmary\Domain\Models\Allergy as InfirmaryAllergy;
use App\Modules\Infirmary\Domain\Models\Intervention;
use App\Modules\Infirmary\Domain\Models\PrescriptionDocument;
use App\Modules\Infirmary\Domain\Models\Vaccine;
use App\Modules\Infirmary\Domain\Repositories\ConsultationMotiveRepositoryInterface;
use App\Modules\Infirmary\Domain\Repositories\InterventionRepositoryInterface;
use App\Modules\Infirmary\Domain\Repositories\MedicationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use RuntimeException;

class InfirmaryController extends Controller
{
    public function dashboard(InfirmaryStatsService $statsService, InterventionRepositoryInterface $interventionRepository)
    {
        $stats = $statsService->dashboardStats();
        $motivesToday = $statsService->consultationMotivesToday();
        $feverTrend = $statsService->feverTrend();
        $recentInterventions = $interventionRepository->recent(4);

        return view('SchoolDashboard::infirmary.dashboard', compact(
            'stats', 'motivesToday', 'feverTrend', 'recentInterventions'
        ));
    }

    public function interventions(
        InterventionRepositoryInterface $interventionRepository,
        ConsultationMotiveRepositoryInterface $motiveRepository
    ) {
        $motiveRepository->ensureDefaults();
        $motives = $motiveRepository->all();
        $recent = $interventionRepository->recent(10);

        // Real weekly recap — replaces a generic "cas similaires, vérifiez
        // une allergie saisonnière" claim with nothing behind it.
        $weekMotiveCounts = $recent->filter(fn ($i) => $i->created_at >= now()->subDays(7))
            ->countBy('motive')
            ->sortDesc();
        $topMotiveThisWeek = $weekMotiveCounts->keys()->first();
        $topMotiveCount = $weekMotiveCounts->first();

        return view('SchoolDashboard::infirmary.interventions', compact(
            'motives', 'recent', 'topMotiveThisWeek', 'topMotiveCount'
        ));
    }

    public function storeIntervention(Request $request, RecordInterventionUseCase $useCase)
    {
        $data = $request->validate([
            'student_identifier' => ['required', 'string', 'max:255'],
            'arrival_time' => ['required', 'date_format:H:i'],
            'temperature' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'motive' => ['required', 'string', 'max:100'],
            'care_notes' => ['nullable', 'string'],
            'decision' => ['required', 'string', 'in:' . implode(',', array_keys(Intervention::DECISIONS))],
        ]);

        $schoolId = auth()->user()->school_id;
        $identifier = $data['student_identifier'];
        $like = '%' . $identifier . '%';

        $student = Student::where('school_id', $schoolId)
            ->where(function ($q) use ($identifier, $like) {
                $q->where('roll_number', $identifier)
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$like]);
            })
            ->first();

        if (!$student) {
            return back()->withErrors(['student_identifier' => 'Aucun élève trouvé pour ce nom ou ce matricule.'])->withInput();
        }

        $useCase->execute(new CreateInterventionDTO([
            'student_id' => $student->id,
            'arrival_time' => Carbon::today()->setTimeFromTimeString($data['arrival_time']),
            'temperature' => $data['temperature'] ?? null,
            'motive' => $data['motive'],
            'care_notes' => $data['care_notes'] ?? null,
            'decision' => $data['decision'],
            'created_by' => auth()->id(),
        ]));

        return back()->with('success', 'Intervention enregistrée avec succès.');
    }

    public function storeMotive(Request $request, ConsultationMotiveRepositoryInterface $motiveRepository)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $motiveRepository->create($data['name']);

        return back()->with('success', 'Motif ajouté avec succès.');
    }

    public function destroyMotive($id, ConsultationMotiveRepositoryInterface $motiveRepository)
    {
        $motiveRepository->delete($id);
        return back()->with('success', 'Motif supprimé avec succès.');
    }

    public function students(Request $request, StudentRepositoryInterface $studentRepository, InterventionRepositoryInterface $interventionRepository)
    {
        $students = $studentRepository->all();

        $lastVisits = Intervention::where('school_id', auth()->user()->school_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->selectRaw('student_id, MAX(arrival_time) as last_visit')
            ->groupBy('student_id')
            ->pluck('last_visit', 'student_id');

        $parentReportedAllergyStudentIds = InfirmaryAllergy::where('school_id', auth()->user()->school_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->pluck('student_id')
            ->unique();

        $students->each(function ($student) use ($lastVisits, $parentReportedAllergyStudentIds) {
            $student->last_visit = $lastVisits->get($student->id);
            $student->has_parent_reported_allergy = $parentReportedAllergyStudentIds->contains($student->id);
        });

        $selectedStudent = null;
        $history = collect();
        $parentVaccines = collect();
        $parentAllergies = collect();
        $parentPrescriptions = collect();

        if ($request->filled('student')) {
            $selectedStudent = $students->firstWhere('id', (int) $request->get('student'));

            if ($selectedStudent) {
                $history = $interventionRepository->forStudent($selectedStudent->id);
                $parentVaccines = Vaccine::where('student_id', $selectedStudent->id)->orderByDesc('administered_at')->get();
                $parentAllergies = InfirmaryAllergy::where('student_id', $selectedStudent->id)->orderByDesc('created_at')->get();
                $parentPrescriptions = PrescriptionDocument::where('student_id', $selectedStudent->id)->orderByDesc('created_at')->get();
            }
        }

        $showFullHistory = $request->boolean('full_history');

        return view('SchoolDashboard::infirmary.students', compact(
            'students', 'selectedStudent', 'history', 'showFullHistory',
            'parentVaccines', 'parentAllergies', 'parentPrescriptions'
        ));
    }

    public function printHealthRecord($id, StudentRepositoryInterface $studentRepository, InterventionRepositoryInterface $interventionRepository)
    {
        $student = $studentRepository->find($id);
        $history = $interventionRepository->forStudent($id);
        $school = auth()->user()->school;
        $parentVaccines = Vaccine::where('student_id', $id)->orderByDesc('administered_at')->get();
        $parentAllergies = InfirmaryAllergy::where('student_id', $id)->orderByDesc('created_at')->get();
        $parentPrescriptions = PrescriptionDocument::where('student_id', $id)->orderByDesc('created_at')->get();

        return view('SchoolDashboard::infirmary.print', compact(
            'student', 'history', 'school', 'parentVaccines', 'parentAllergies', 'parentPrescriptions'
        ));
    }

    public function pharmacy(MedicationRepositoryInterface $medicationRepository, InfirmaryStatsService $statsService)
    {
        $medications = $medicationRepository->paginate(10);
        $allMedications = $medicationRepository->all();
        $expiringSoon = $medicationRepository->expiringWithin(30);
        $globalStockLevel = $medicationRepository->globalStockLevel();
        $recentMovements = $medicationRepository->recentMovements(5);
        $stockoutRisk = $statsService->stockoutRisk();

        return view('SchoolDashboard::infirmary.pharmacy', compact(
            'medications', 'allMedications', 'expiringSoon', 'globalStockLevel', 'recentMovements', 'stockoutRisk'
        ));
    }

    public function storeMedication(Request $request, CreateMedicationUseCase $useCase)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0'],
            'max_quantity' => ['nullable', 'integer', 'min:1'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'critical_threshold' => ['nullable', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
        ]);

        $useCase->execute(new CreateMedicationDTO($data));

        return back()->with('success', 'Médicament ajouté au stock avec succès.');
    }

    public function adjustMedicationStock(Request $request, AdjustMedicationStockUseCase $useCase)
    {
        $data = $request->validate([
            'medication_id' => ['required', 'exists:infirmary_medications,id'],
            'type' => ['required', 'string', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'source' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $useCase->execute(new AdjustMedicationStockDTO($data));
        } catch (RuntimeException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        return back()->with('success', 'Stock mis à jour avec succès.');
    }
}

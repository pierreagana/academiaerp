<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Finance\Application\DTOs\CreateScholarshipDTO;
use App\Modules\Finance\Application\DTOs\CreateScholarshipTypeDTO;
use App\Modules\Finance\Application\DTOs\UpdateScholarshipDTO;
use App\Modules\Finance\Application\DTOs\UpdateScholarshipTypeDTO;
use App\Modules\Finance\Application\Services\ScholarshipStatsService;
use App\Modules\Finance\Application\UseCases\CreateScholarshipTypeUseCase;
use App\Modules\Finance\Application\UseCases\CreateScholarshipUseCase;
use App\Modules\Finance\Application\UseCases\DeleteScholarshipTypeUseCase;
use App\Modules\Finance\Application\UseCases\UpdateScholarshipTypeUseCase;
use App\Modules\Finance\Application\UseCases\UpdateScholarshipUseCase;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\Finance\Domain\Models\Scholarship;
use App\Modules\Finance\Domain\Models\ScholarshipDisbursement;
use App\Modules\Finance\Domain\Models\ScholarshipDocument;
use App\Modules\Finance\Domain\Models\ScholarshipGrade;
use App\Modules\Finance\Domain\Repositories\ScholarshipRepositoryInterface;
use App\Modules\Finance\Domain\Repositories\ScholarshipTypeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ScholarshipController extends Controller
{
    public function dashboard(ScholarshipStatsService $statsService, ScholarshipTypeRepositoryInterface $typeRepository)
    {
        $schoolId = auth()->user()->school_id;

        $stats = $statsService->dashboardStats($schoolId);
        $breakdown = $statsService->typeBreakdown($schoolId);
        $pending = $statsService->pendingRequests($schoolId);
        $types = $typeRepository->all();
        $allStudents = Student::where('school_id', $schoolId)->orderBy('first_name')->get();

        return view('SchoolDashboard::finance.scholarships.dashboard', compact('stats', 'breakdown', 'pending', 'types', 'allStudents'));
    }

    public function students(Request $request, ScholarshipRepositoryInterface $repository, ScholarshipTypeRepositoryInterface $typeRepository)
    {
        $schoolId = auth()->user()->school_id;

        $search = $request->get('search');
        $typeId = $request->get('type_id');
        $status = $request->get('status');
        $page = (int) $request->get('page', 1);
        $perPage = 10;

        $scholarships = $repository->all();

        if (!empty($search)) {
            $scholarships = $scholarships->filter(function (Scholarship $s) use ($search) {
                $name = $s->student->first_name . ' ' . $s->student->last_name;
                return str_contains(strtolower($name), strtolower($search))
                    || str_contains(strtolower($s->student->roll_number ?? ''), strtolower($search));
            });
        }

        if (!empty($typeId)) {
            $scholarships = $scholarships->filter(fn (Scholarship $s) => $s->scholarship_type_id == $typeId);
        }

        if (!empty($status)) {
            $scholarships = $scholarships->filter(fn (Scholarship $s) => $s->status === $status);
        }

        $scholarships = $scholarships->values();

        $paginated = new LengthAwarePaginator(
            $scholarships->forPage($page, $perPage),
            $scholarships->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $types = $typeRepository->all();
        $allStudents = Student::where('school_id', $schoolId)->orderBy('first_name')->get();

        return view('SchoolDashboard::finance.scholarships.students', compact('paginated', 'types', 'allStudents', 'search', 'typeId', 'status'));
    }

    public function store(Request $request, CreateScholarshipUseCase $useCase)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'scholarship_type_id' => ['required', 'exists:scholarship_types,id'],
            'monthly_amount' => ['required', 'numeric', 'min:0'],
            'declared_average' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'priority' => ['nullable', 'boolean'],
            'awarded_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['status'] = 'pending';
        $data['priority'] = $request->boolean('priority');

        $dto = new CreateScholarshipDTO($data);
        $useCase->execute($dto);

        return back()->with('success', 'Bourse créée et mise en attente de validation.');
    }

    public function approve($id, UpdateScholarshipUseCase $useCase)
    {
        $data = ['status' => 'active'];

        $scholarship = Scholarship::where('school_id', auth()->user()->school_id)->findOrFail($id);
        if (!$scholarship->awarded_date) {
            $data['awarded_date'] = now()->toDateString();
        }

        $useCase->execute($id, new UpdateScholarshipDTO($data));

        return back()->with('success', 'Bourse approuvée avec succès.');
    }

    public function reject($id, UpdateScholarshipUseCase $useCase)
    {
        $useCase->execute($id, new UpdateScholarshipDTO(['status' => 'rejected']));
        return back()->with('success', 'Demande rejetée.');
    }

    public function suspend($id, UpdateScholarshipUseCase $useCase)
    {
        $useCase->execute($id, new UpdateScholarshipDTO(['status' => 'suspended']));
        return back()->with('success', 'Bourse suspendue.');
    }

    public function reactivate($id, UpdateScholarshipUseCase $useCase)
    {
        $useCase->execute($id, new UpdateScholarshipDTO(['status' => 'active']));
        return back()->with('success', 'Bourse réactivée.');
    }

    public function renew($id, UpdateScholarshipUseCase $useCase)
    {
        $scholarship = Scholarship::where('school_id', auth()->user()->school_id)->findOrFail($id);
        $base = $scholarship->expiry_date ?? now();

        $useCase->execute($id, new UpdateScholarshipDTO([
            'status' => 'active',
            'expiry_date' => $base->copy()->addYear()->toDateString(),
        ]));

        return back()->with('success', 'Bourse renouvelée pour une année supplémentaire.');
    }

    public function criteria(ScholarshipTypeRepositoryInterface $repository)
    {
        $types = $repository->all();
        return view('SchoolDashboard::finance.scholarships.criteria', compact('types'));
    }

    public function storeType(Request $request, CreateScholarshipTypeUseCase $useCase)
    {
        $data = $this->validateType($request);
        $useCase->execute(new CreateScholarshipTypeDTO($data));

        return redirect()->route('school.finance.scholarships.criteria')->with('success', 'Type de bourse créé avec succès.');
    }

    public function updateType(Request $request, $id, UpdateScholarshipTypeUseCase $useCase)
    {
        $data = $this->validateType($request);
        $useCase->execute($id, new UpdateScholarshipTypeDTO($data));

        return redirect()->route('school.finance.scholarships.criteria')->with('success', 'Type de bourse mis à jour avec succès.');
    }

    public function destroyType($id, DeleteScholarshipTypeUseCase $useCase)
    {
        $useCase->execute($id);
        return redirect()->route('school.finance.scholarships.criteria')->with('success', 'Type de bourse supprimé avec succès.');
    }

    private function validateType(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'min_average' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'min_attendance_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_family_income' => ['nullable', 'numeric', 'min:0'],
            'min_competition_level' => ['nullable', 'string', 'max:255'],
            'required_documents' => ['nullable', 'string'],
            'default_monthly_amount' => ['required', 'numeric', 'min:0'],
        ]);
    }

    public function show($id, ScholarshipRepositoryInterface $repository)
    {
        $scholarship = $repository->find($id);
        return view('SchoolDashboard::finance.scholarships.show', compact('scholarship'));
    }

    public function storeDisbursement(Request $request, $scholarshipId)
    {
        $scholarship = Scholarship::where('school_id', auth()->user()->school_id)->findOrFail($scholarshipId);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'due_date' => ['required', 'date'],
        ]);

        $data['scholarship_id'] = $scholarship->id;
        $data['status'] = 'pending';

        ScholarshipDisbursement::create($data);

        return back()->with('success', 'Tranche ajoutée avec succès.');
    }

    public function markDisbursementPaid(Request $request, $id)
    {
        $disbursement = ScholarshipDisbursement::whereHas('scholarship', function ($q) {
            $q->where('school_id', auth()->user()->school_id);
        })->findOrFail($id);

        $data = $request->validate([
            'method' => ['required', 'string', 'in:' . implode(',', array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $disbursement->update([
            'status' => 'paid',
            'paid_date' => now()->toDateString(),
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
        ]);

        return back()->with('success', 'Versement marqué comme payé.');
    }

    public function storeDocument(Request $request, $scholarshipId)
    {
        $scholarship = Scholarship::where('school_id', auth()->user()->school_id)->findOrFail($scholarshipId);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:5120'],
        ]);

        $path = $request->file('file')->store('scholarships/documents', 'public');

        ScholarshipDocument::create([
            'scholarship_id' => $scholarship->id,
            'label' => $data['label'],
            'file_path' => $path,
            'status' => 'verified',
        ]);

        return back()->with('success', 'Document ajouté avec succès.');
    }

    public function destroyDocument($id)
    {
        $document = ScholarshipDocument::whereHas('scholarship', function ($q) {
            $q->where('school_id', auth()->user()->school_id);
        })->findOrFail($id);

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Document supprimé avec succès.');
    }

    public function storeGrade(Request $request, $scholarshipId)
    {
        $scholarship = Scholarship::where('school_id', auth()->user()->school_id)->findOrFail($scholarshipId);

        $data = $request->validate([
            'period' => ['required', 'string', 'max:255'],
            'average' => ['required', 'numeric', 'min:0', 'max:20'],
            'recorded_at' => ['required', 'date'],
        ]);

        $data['scholarship_id'] = $scholarship->id;

        ScholarshipGrade::create($data);

        return back()->with('success', 'Note enregistrée avec succès.');
    }

    public function exportCertificate($id, ScholarshipRepositoryInterface $repository)
    {
        $scholarship = $repository->find($id);

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=certificat_bourse_' . $scholarship->id . '.csv',
        ];

        $callback = function () use ($scholarship) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Champ', 'Valeur'], ',', '"', '\\');
            fputcsv($file, ['Étudiant', $scholarship->student->first_name . ' ' . $scholarship->student->last_name], ',', '"', '\\');
            fputcsv($file, ['Type de Bourse', $scholarship->type->name ?? '-'], ',', '"', '\\');
            fputcsv($file, ['Montant Annuel', $scholarship->annual_amount], ',', '"', '\\');
            fputcsv($file, ['Statut', Scholarship::STATUSES[$scholarship->status] ?? $scholarship->status], ',', '"', '\\');
            fputcsv($file, ['Date d\'attribution', $scholarship->awarded_date?->format('d/m/Y') ?? '-'], ',', '"', '\\');
            fputcsv($file, ['Date d\'expiration', $scholarship->expiry_date?->format('d/m/Y') ?? '-'], ',', '"', '\\');
            fclose($file);
        };

        return response()->streamDownload($callback, 'certificat_bourse_' . $scholarship->id . '.csv', $headers);
    }
}

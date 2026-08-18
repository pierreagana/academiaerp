<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Cards\Application\DTOs\SaveCardTemplateDTO;
use App\Modules\Cards\Application\UseCases\SaveCardTemplateUseCase;
use App\Modules\Cards\Domain\Models\CardTemplate;
use App\Modules\Cards\Domain\Repositories\CardTemplateRepositoryInterface;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function show(string $type, Request $request, CardTemplateRepositoryInterface $repository)
    {
        abort_unless(array_key_exists($type, CardTemplate::TYPES), 404);

        $schoolId = auth()->user()->school_id;
        $template = $repository->findOrDefault($type);

        if ($type === 'student') {
            $records = Student::where('school_id', $schoolId)->with('academicClass', 'guardians')->get()
                ->map(fn ($s) => $this->tagHolder($s, 'student'));
        } else {
            $teachers = Teacher::where('school_id', $schoolId)->get()->map(fn ($t) => $this->tagHolder($t, 'teacher'));
            $staff = Staff::where('school_id', $schoolId)->get()->map(fn ($s) => $this->tagHolder($s, 'staff'));
            $records = $teachers->concat($staff);
        }

        $previewKey = $request->get('preview');
        $selected = $previewKey ? $records->firstWhere('preview_key', $previewKey) : $records->first();

        $previewData = $selected ? $this->buildPreviewData($type, $selected) : [];
        $previewPhoto = $selected && $selected->photo_path ? asset('storage/' . $selected->photo_path) : null;
        $fieldCatalog = $template->fieldCatalog();
        $school = auth()->user()->school;

        $matricule = $previewData['student_id'] ?? $previewData['employee_id'] ?? null;
        $qrData = ($selected && $matricule) ? trim(($school->code ?? '') . ':' . $matricule, ':') : null;

        return view('SchoolDashboard::cards.builder', compact(
            'type', 'template', 'records', 'selected', 'previewData', 'previewPhoto', 'fieldCatalog', 'school', 'qrData'
        ));
    }

    private function tagHolder($model, string $holderType)
    {
        $model->holder_type = $holderType;
        $model->preview_key = $holderType . ':' . $model->id;
        return $model;
    }

    private function buildPreviewData(string $type, $record): array
    {
        if ($type === 'student') {
            return [
                'full_name' => $record->first_name . ' ' . $record->last_name,
                'student_id' => $record->roll_number,
                'class' => $record->academicClass->name ?? '-',
                'blood_group' => $record->blood_group ?? '-',
                'academic_year' => $record->academic_year ?? '-',
                'date_of_birth' => $record->dob ? \Illuminate\Support\Carbon::parse($record->dob)->format('d M Y') : '-',
                'emergency_contact' => optional($record->guardians->first())->phone ?? '-',
            ];
        }

        return [
            'full_name' => $record->first_name . ' ' . $record->last_name,
            'employee_id' => $record->employee_id,
            'department' => $record->department ?? '-',
            'role' => $record->role ?? '-',
            'phone' => $record->phone ?? '-',
            'hire_date' => $record->hire_date ? \Illuminate\Support\Carbon::parse($record->hire_date)->format('d M Y') : '-',
            'contract_type' => strtoupper($record->contract_type ?? 'cdi'),
        ];
    }

    public function store(string $type, Request $request, SaveCardTemplateUseCase $useCase)
    {
        abort_unless(array_key_exists($type, CardTemplate::TYPES), 404);

        $data = $request->validate([
            'primary_color' => ['required', 'string', 'max:20'],
            'background_color' => ['required', 'string', 'max:20'],
            'text_color' => ['required', 'string', 'max:20'],
            'orientation' => ['required', 'string', 'in:' . implode(',', array_keys(CardTemplate::ORIENTATIONS))],
            'photo_position' => ['required', 'string', 'in:' . implode(',', array_keys(CardTemplate::PHOTO_POSITIONS))],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'watermark' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'front_fields' => ['nullable', 'array'],
            'front_fields.*' => ['string'],
            'back_fields' => ['nullable', 'array'],
            'back_fields.*' => ['string'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('cards/logos', 'public');
        }
        unset($data['logo']);

        if ($request->hasFile('watermark')) {
            $data['watermark_path'] = $request->file('watermark')->store('cards/watermarks', 'public');
        }
        unset($data['watermark']);

        $data['front_fields'] = $data['front_fields'] ?? [];
        $data['back_fields'] = $data['back_fields'] ?? [];

        $useCase->execute(new SaveCardTemplateDTO($type, $data));

        return back()->with('success', 'Modèle enregistré avec succès.');
    }

    public function printCards(string $type, Request $request, CardTemplateRepositoryInterface $repository)
    {
        abort_unless(array_key_exists($type, CardTemplate::TYPES), 404);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'holder_type' => ['required', 'string', 'in:student,teacher,staff'],
        ]);

        $schoolId = auth()->user()->school_id;
        $template = $repository->findOrDefault($type);
        $school = auth()->user()->school;

        $query = match ($data['holder_type']) {
            'student' => Student::where('school_id', $schoolId)->whereIn('id', $data['ids'])->with('academicClass', 'guardians'),
            'teacher' => Teacher::where('school_id', $schoolId)->whereIn('id', $data['ids']),
            'staff' => Staff::where('school_id', $schoolId)->whereIn('id', $data['ids']),
        };

        $people = $query->get()->map(fn ($p) => $this->tagHolder($p, $data['holder_type']));

        $cards = $people->map(function ($person) use ($type, $school) {
            $previewData = $this->buildPreviewData($type, $person);
            $matricule = $previewData['student_id'] ?? $previewData['employee_id'] ?? null;

            return [
                'name' => $person->first_name . ' ' . $person->last_name,
                'data' => $previewData,
                'photo' => $person->photo_path ? asset('storage/' . $person->photo_path) : null,
                'qr' => $matricule ? trim(($school->code ?? '') . ':' . $matricule, ':') : null,
            ];
        });

        return view('SchoolDashboard::cards.print', compact('type', 'template', 'school', 'cards'));
    }
}

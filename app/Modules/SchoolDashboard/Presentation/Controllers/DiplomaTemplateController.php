<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\Award;
use App\Modules\Academic\Domain\Models\AwardType;
use App\Modules\Academic\Domain\Models\DiplomaTemplate;
use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Teacher;
use Illuminate\Http\Request;

class DiplomaTemplateController extends Controller
{
    public function edit(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $school = auth()->user()->school;

        $awardTypeId = $request->get('award_type_id') ? (int) $request->get('award_type_id') : null;
        $awardType = $awardTypeId ? AwardType::availableFor($schoolId)->findOrFail($awardTypeId) : null;

        $template = $awardTypeId
            ? (DiplomaTemplate::forAwardType($schoolId, $awardTypeId) ?? new DiplomaTemplate([
                'school_id' => $schoolId,
                'award_type_id' => $awardTypeId,
                'title' => 'DIPLÔME',
                'subtitle' => 'Décerné à',
                'body_text' => DiplomaTemplate::DEFAULT_BODY_TEXT,
                'orientation' => 'landscape',
                'border_style' => 'classic',
                'layout' => 'classic',
                'primary_color' => '#031C5B',
                'background_color' => '#FFFFFF',
                'text_color' => '#0F172A',
            ]))
            : DiplomaTemplate::findOrDefault($schoolId);

        $awardsQuery = Award::where('school_id', $schoolId)->with('type')->orderByDesc('awarded_date');
        if ($awardTypeId) {
            $awardsQuery->where('award_type_id', $awardTypeId);
        }
        $awards = $awardsQuery->limit(50)->get()->map(function (Award $award) {
            $award->recipientName = $this->recipientName($award);
            return $award;
        });

        $previewId = $request->get('preview');
        $selectedAward = $previewId ? $awards->firstWhere('id', (int) $previewId) : $awards->first();

        $previewData = $selectedAward ? [
            'recipient' => $selectedAward->recipientName,
            'award' => $selectedAward->type->name ?? '',
            'reason' => $selectedAward->reason ?? '',
            'date' => $selectedAward->awarded_date->format('d/m/Y'),
            'school' => $school->name ?? '',
        ] : [
            'recipient' => 'Nom du Bénéficiaire',
            'award' => $awardType->name ?? "Tableau d'honneur",
            'reason' => 'Excellence académique du trimestre',
            'date' => now()->format('d/m/Y'),
            'school' => $school->name ?? '',
        ];

        $fieldValues = $selectedAward ? $this->resolveFieldValues($selectedAward) : [
            'recipient_name' => 'Nom du Bénéficiaire',
            'recipient_first_name' => 'Prénom',
            'recipient_last_name' => 'Nom',
            'age' => '12 ans',
            'date_of_birth' => '01/01/2013',
            'class_name' => 'CM2',
            'matricule' => 'MAT-0001',
            'award_name' => $awardType->name ?? "Tableau d'honneur",
            'award_category' => $awardType->category ?? 'Récompenses académiques',
            'reason' => 'Excellence académique du trimestre',
            'awarded_date' => now()->format('d/m/Y'),
            'school_name' => $school->name ?? '',
        ];

        return view('SchoolDashboard::academic.diploma_template', compact(
            'template', 'awards', 'selectedAward', 'previewData', 'school', 'awardType', 'fieldValues'
        ));
    }

    public function update(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate([
            'award_type_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['required', 'string', 'max:255'],
            'body_text' => ['required', 'string', 'max:2000'],
            'orientation' => ['required', 'string', 'in:' . implode(',', array_keys(DiplomaTemplate::ORIENTATIONS))],
            'border_style' => ['required', 'string', 'in:' . implode(',', array_keys(DiplomaTemplate::BORDER_STYLES))],
            'layout' => ['required', 'string', 'in:' . implode(',', array_keys(DiplomaTemplate::LAYOUTS))],
            'primary_color' => ['required', 'string', 'max:20'],
            'background_color' => ['required', 'string', 'max:20'],
            'text_color' => ['required', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'seal' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:4096'],
            'signature_1_name' => ['nullable', 'string', 'max:255'],
            'signature_1_title' => ['nullable', 'string', 'max:255'],
            'signature_2_name' => ['nullable', 'string', 'max:255'],
            'signature_2_title' => ['nullable', 'string', 'max:255'],
            'fields_layout' => ['nullable', 'string', 'max:10000'],
        ]);

        $data['fields_layout'] = $this->sanitizeFieldsLayout($data['fields_layout'] ?? null);

        $awardTypeId = $data['award_type_id'] ?? null;
        unset($data['award_type_id']);

        if ($awardTypeId) {
            // Must be a type this school is actually allowed to see (global catalog or its own custom model).
            AwardType::availableFor($schoolId)->findOrFail($awardTypeId);
        }

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('diplomas/logos', 'public');
        }
        unset($data['logo']);

        if ($request->hasFile('seal')) {
            $data['seal_path'] = $request->file('seal')->store('diplomas/seals', 'public');
        }
        unset($data['seal']);

        if ($request->hasFile('background_image')) {
            $data['background_image_path'] = $request->file('background_image')->store('diplomas/backgrounds', 'public');
        }
        unset($data['background_image']);

        DiplomaTemplate::updateOrCreate(
            ['school_id' => $schoolId, 'award_type_id' => $awardTypeId],
            $data
        );

        return redirect()->route('school.academic.awards.template.edit', array_filter(['award_type_id' => $awardTypeId]))
            ->with('success', 'Modèle de diplôme enregistré avec succès.');
    }

    public function print(int $id)
    {
        $schoolId = auth()->user()->school_id;

        $award = Award::where('school_id', $schoolId)->with('type')->findOrFail($id);

        return $this->renderForAward($award);
    }

    /**
     * Guard-agnostic renderer: derives the school from the Award itself (not
     * from auth()) so it can be reused by callers authenticated on a
     * different guard — e.g. ParentDashboardController runs under
     * `auth:parent`, where auth()->user() (default 'web' guard) is null.
     * Callers are responsible for verifying the caller may see this award
     * before calling this.
     */
    public function renderForAward(Award $award)
    {
        $award->loadMissing('type');
        $school = $award->school;
        $template = DiplomaTemplate::resolveFor($award->school_id, $award->award_type_id);
        $award->recipientName = $this->recipientName($award);

        $fieldValues = $this->resolveFieldValues($award);
        $body = $template->render($fieldValues);

        return view('SchoolDashboard::academic.diploma_print', compact('award', 'template', 'body', 'school', 'fieldValues'));
    }

    private function recipientName(Award $award): string
    {
        $recipient = $this->resolveRecipient($award);

        if (!$recipient) {
            return 'Personne supprimée';
        }

        return trim($recipient->first_name . ' ' . $recipient->last_name);
    }

    private function resolveRecipient(Award $award)
    {
        return match ($award->recipient_type) {
            'student' => Student::with('academicClass')->find($award->recipient_id),
            'teacher' => Teacher::find($award->recipient_id),
            'staff' => Staff::find($award->recipient_id),
            default => null,
        };
    }

    /** Resolves every value in DiplomaTemplate::AVAILABLE_FIELDS for one award, for the checkbox-driven positioned fields. */
    private function resolveFieldValues(Award $award): array
    {
        $award->loadMissing(['type', 'school']);
        $recipient = $this->resolveRecipient($award);
        $isStudent = $award->recipient_type === 'student';
        $dob = $isStudent ? ($recipient->dob ?? null) : null;

        return [
            'recipient_name' => $recipient ? trim($recipient->first_name . ' ' . $recipient->last_name) : 'Personne supprimée',
            'recipient_first_name' => $recipient->first_name ?? '',
            'recipient_last_name' => $recipient->last_name ?? '',
            'age' => $dob ? \Illuminate\Support\Carbon::parse($dob)->age . ' ans' : '—',
            'date_of_birth' => $dob ? \Illuminate\Support\Carbon::parse($dob)->format('d/m/Y') : '—',
            'class_name' => $isStudent ? ($recipient->academicClass->name ?? '—') : '—',
            'matricule' => $isStudent ? ($recipient->roll_number ?? '—') : ($recipient->employee_id ?? '—'),
            'award_name' => $award->type->name ?? '',
            'award_category' => $award->type->category ?? '',
            'reason' => $award->reason ?? '',
            'awarded_date' => $award->awarded_date->format('d/m/Y'),
            'school_name' => $award->school->name ?? '',
        ];
    }

    /** Keeps only known field keys with a boolean `enabled` and 0-100 clamped x/y — never trust raw client JSON. */
    private function sanitizeFieldsLayout(?string $json): array
    {
        if (!$json) {
            return [];
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $allowedKeys = array_merge(array_keys(DiplomaTemplate::AVAILABLE_FIELDS), array_keys(DiplomaTemplate::POSITIONABLE_MEDIA));

        $clean = [];
        foreach ($allowedKeys as $key) {
            if (!isset($decoded[$key]) || !is_array($decoded[$key])) {
                continue;
            }
            $entry = $decoded[$key];
            $clean[$key] = [
                'enabled' => (bool) ($entry['enabled'] ?? false),
                'x' => max(0, min(100, (float) ($entry['x'] ?? 50))),
                'y' => max(0, min(100, (float) ($entry['y'] ?? 50))),
            ];
        }

        return $clean;
    }
}

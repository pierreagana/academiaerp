<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\Award;
use App\Modules\Academic\Domain\Models\AwardType;
use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Teacher;
use Illuminate\Http\Request;

class AwardController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $recipientType = $request->get('recipient_type');

        $awards = Award::where('school_id', $schoolId)
            ->with(['type', 'awardedBy'])
            ->when($recipientType, fn ($q) => $q->where('recipient_type', $recipientType))
            ->orderByDesc('awarded_date')
            ->paginate(15)
            ->withQueryString();

        // Attach each row's real recipient name — the model can't eager-load
        // this (recipient() is a manual resolver, not a real Eloquent relation,
        // since 'student'/'teacher'/'staff' aren't registered in a morphMap).
        $awards->getCollection()->transform(function (Award $award) {
            $award->recipientName = $this->recipientName($award);
            return $award;
        });

        return view('SchoolDashboard::academic.awards', compact('awards', 'recipientType'));
    }

    private function recipientName(Award $award): string
    {
        $recipient = $award->recipient();
        if (!$recipient) {
            return 'Personne supprimée';
        }

        return trim($recipient->first_name . ' ' . $recipient->last_name);
    }

    public function create(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        // Global catalog models are always attributable (they use the school's default diploma
        // design); custom "Mes Modèles" entries only become attributable once the admin has
        // configured a diploma design for that specific model.
        $awardTypes = AwardType::availableFor($schoolId)
            ->with('diplomaTemplate')
            ->orderBy('order')->orderBy('name')->get()
            ->filter(fn (AwardType $type) => is_null($type->school_id) || $type->diplomaTemplate !== null)
            ->groupBy('category');
        $students = Student::where('school_id', $schoolId)->where('status', 'active')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'roll_number']);
        $teachers = Teacher::where('school_id', $schoolId)->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'employee_id']);
        $staffMembers = Staff::where('school_id', $schoolId)->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'employee_id']);

        return view('SchoolDashboard::academic.award_create', compact('awardTypes', 'students', 'teachers', 'staffMembers'));
    }

    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate([
            'recipient_type' => ['required', 'string', 'in:' . implode(',', array_keys(Award::RECIPIENT_TYPES))],
            'recipient_id' => ['required', 'integer'],
            'award_type_id' => ['required', 'exists:award_types,id'],
            'material_reward' => ['nullable', 'string', 'in:' . implode(',', Award::MATERIAL_REWARDS)],
            'reason' => ['nullable', 'string', 'max:2000'],
            'awarded_date' => ['required', 'date'],
        ]);

        $recipientModel = match ($data['recipient_type']) {
            'student' => Student::where('school_id', $schoolId)->find($data['recipient_id']),
            'teacher' => Teacher::where('school_id', $schoolId)->find($data['recipient_id']),
            'staff' => Staff::where('school_id', $schoolId)->find($data['recipient_id']),
        };

        if (!$recipientModel) {
            return back()->withErrors(['recipient_id' => "Ce destinataire n'appartient pas à votre établissement."])->withInput();
        }

        $awardType = AwardType::availableFor($schoolId)->find($data['award_type_id']);
        if (!$awardType || ($awardType->school_id && !$awardType->diplomaTemplate()->exists())) {
            return back()->withErrors(['award_type_id' => "Cette récompense n'a pas encore de modèle de diplôme configuré."])->withInput();
        }

        $data['school_id'] = $schoolId;
        $data['awarded_by'] = auth()->id();

        Award::create($data);

        return redirect()->route('school.academic.awards.index')->with('success', 'Récompense attribuée avec succès !');
    }

    public function destroy($id)
    {
        $award = Award::where('school_id', auth()->user()->school_id)->findOrFail($id);
        $award->delete();

        return back()->with('success', 'Récompense supprimée avec succès.');
    }
}

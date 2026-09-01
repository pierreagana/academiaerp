<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\AwardType;
use Illuminate\Http\Request;

class AwardTypeController extends Controller
{
    public function index()
    {
        $schoolId = auth()->user()->school_id;

        $awardTypes = AwardType::where('school_id', $schoolId)->with('diplomaTemplate')->orderBy('order')->orderBy('name')->get()->groupBy('category');

        return view('SchoolDashboard::academic.award_types', compact('awardTypes'));
    }

    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate([
            'category' => ['required', 'string', 'in:' . implode(',', AwardType::CATEGORIES)],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $data['school_id'] = $schoolId;

        $awardType = AwardType::create($data);

        return redirect()->route('school.academic.awards.template.edit', ['award_type_id' => $awardType->id])
            ->with('success', 'Modèle créé ! Configurez maintenant son diplôme pour le rendre attribuable.');
    }

    public function destroy(int $id)
    {
        $awardType = AwardType::where('school_id', auth()->user()->school_id)->findOrFail($id);
        $awardType->delete();

        return back()->with('success', 'Modèle de récompense supprimé avec succès.');
    }
}

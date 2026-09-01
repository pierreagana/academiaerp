<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\Branch;
use App\Modules\Academic\Domain\Repositories\BranchRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Models\Country;
use App\Modules\SuperAdmin\Domain\Models\Facility;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request, BranchRepositoryInterface $repository)
    {
        $branches = $repository->all();
        $editBranch = null;

        if ($request->has('edit')) {
            $editBranch = $branches->firstWhere('id', (int) $request->query('edit'));
            $editBranch?->load('facilitiesList');
        }

        $schoolId = auth()->user()->school_id;
        $eligibleDirectors = \App\Models\User::where('school_id', $schoolId)
            ->whereHas('assignedRole', fn ($q) => $q->where('is_branch_director', true))
            ->get();

        $countries = Country::orderBy('order')->get();
        $facilities = Facility::where('is_active', true)->orderBy('order')->orderBy('name')->get();
        $branchFacilityIds = $editBranch ? $editBranch->facilitiesList->pluck('id')->all() : [];

        return view('SchoolDashboard::dashboard.branches', compact(
            'branches', 'editBranch', 'eligibleDirectors', 'countries', 'facilities', 'branchFacilityIds'
        ));
    }

    private function validationRules($schoolId, ?int $ignoreId = null): array
    {
        $uniqueName = \Illuminate\Validation\Rule::unique('branches', 'name')->where('school_id', $schoolId)->whereNull('deleted_at');
        if ($ignoreId) {
            $uniqueName = $uniqueName->ignore($ignoreId);
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueName],
            'type' => ['nullable', 'string', 'max:100'],
            'sector' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:' . implode(',', array_keys(Branch::STATUSES))],
            'language_regime' => ['nullable', 'string', 'max:100'],
            'levels' => ['nullable', 'array'],
            'levels.*' => ['string', 'in:' . implode(',', Branch::LEVELS)],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'exists:countries,name'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'phone_country_code' => ['nullable', 'string', 'exists:countries,dial_code'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['integer', 'exists:facilities,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
            'director_id' => ['nullable', 'exists:users,id'],
        ];
    }

    public function store(Request $request, BranchRepositoryInterface $repository)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate($this->validationRules($schoolId), [
            'name.required' => 'Le nom de la succursale est obligatoire.',
            'name.unique' => 'Ce nom de succursale existe déjà.',
        ]);

        $directorId = $data['director_id'] ?? null;
        unset($data['director_id']);

        $facilityIds = $data['facilities'] ?? [];
        unset($data['facilities']);

        if (!empty($data['phone_number'])) {
            $data['contact_phone'] = ($data['phone_country_code'] ?? '+225') . ' ' . $data['phone_number'];
        }
        unset($data['phone_country_code'], $data['phone_number']);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('branches/logos', 'public');
        }
        unset($data['logo']);

        $data['code'] = 'ETB-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));

        $branch = $repository->create($data);
        $branch->facilitiesList()->sync($facilityIds);

        if ($directorId) {
            \App\Models\User::where('id', $directorId)->where('school_id', $schoolId)->update(['current_branch_id' => $branch->id]);
        }

        return redirect()->route('school.branches')->with('success', 'Succursale créée avec succès !');
    }

    public function update($id, Request $request, BranchRepositoryInterface $repository)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate($this->validationRules($schoolId, (int) $id), [
            'name.required' => 'Le nom de la succursale est obligatoire.',
            'name.unique' => 'Ce nom de succursale existe déjà.',
        ]);

        $directorId = $data['director_id'] ?? null;
        unset($data['director_id']);

        $facilityIds = $data['facilities'] ?? [];
        unset($data['facilities']);

        if (!empty($data['phone_number'])) {
            $data['contact_phone'] = ($data['phone_country_code'] ?? '+225') . ' ' . $data['phone_number'];
        }
        unset($data['phone_country_code'], $data['phone_number']);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('branches/logos', 'public');
        }
        unset($data['logo']);

        if (empty($repository->find($id)->code)) {
            $data['code'] = 'ETB-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));
        }

        $branch = $repository->update($id, $data);
        $branch->facilitiesList()->sync($facilityIds);

        \App\Models\User::where('school_id', $schoolId)->where('current_branch_id', $id)->update(['current_branch_id' => null]);
        if ($directorId) {
            \App\Models\User::where('id', $directorId)->where('school_id', $schoolId)->update(['current_branch_id' => $id]);
        }

        return redirect()->route('school.branches')->with('success', 'Succursale mise à jour avec succès !');
    }

    public function destroy($id, BranchRepositoryInterface $repository)
    {
        try {
            $repository->delete($id);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('school.branches')->withErrors(['branch' => $e->getMessage()]);
        }

        return redirect()->route('school.branches')->with('success', 'Succursale supprimée avec succès !');
    }

    public function setMain($id, BranchRepositoryInterface $repository)
    {
        $repository->setMain($id);

        return redirect()->route('school.branches')->with('success', 'Succursale principale mise à jour.');
    }

    public function switch(Request $request)
    {
        abort_if(auth()->user()->isBranchDirector(), 403, 'Votre compte est rattaché à une seule succursale.');

        $data = $request->validate([
            'branch_id' => ['required', 'string'],
        ]);

        if ($data['branch_id'] === 'all') {
            auth()->user()->update(['view_all_branches' => true, 'current_branch_id' => null]);

            return back()->with('success', 'Vous voyez maintenant les données de toutes les succursales (Vue Globale).');
        }

        $branch = \App\Modules\Academic\Domain\Models\Branch::where('school_id', auth()->user()->school_id)->findOrFail((int) $data['branch_id']);

        auth()->user()->update(['view_all_branches' => false, 'current_branch_id' => $branch->id]);

        return back()->with('success', 'Vous travaillez maintenant sur la succursale « ' . $branch->name . ' ».');
    }
}

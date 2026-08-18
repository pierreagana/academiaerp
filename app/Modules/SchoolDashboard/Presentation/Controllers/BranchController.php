<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Repositories\BranchRepositoryInterface;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request, BranchRepositoryInterface $repository)
    {
        $branches = $repository->all();
        $editBranch = null;

        if ($request->has('edit')) {
            $editBranch = $branches->firstWhere('id', (int) $request->query('edit'));
        }

        return view('SchoolDashboard::dashboard.branches', compact('branches', 'editBranch'));
    }

    public function store(Request $request, BranchRepositoryInterface $repository)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('branches', 'name')->where('school_id', auth()->user()->school_id)->whereNull('deleted_at')],
            'type' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
        ], [
            'name.required' => 'Le nom de la succursale est obligatoire.',
            'name.unique' => 'Ce nom de succursale existe déjà.',
        ]);

        $repository->create($data);

        return redirect()->route('school.branches')->with('success', 'Succursale créée avec succès !');
    }

    public function update($id, Request $request, BranchRepositoryInterface $repository)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('branches', 'name')->where('school_id', auth()->user()->school_id)->ignore($id)->whereNull('deleted_at')],
            'type' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
        ], [
            'name.required' => 'Le nom de la succursale est obligatoire.',
            'name.unique' => 'Ce nom de succursale existe déjà.',
        ]);

        $repository->update($id, $data);

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

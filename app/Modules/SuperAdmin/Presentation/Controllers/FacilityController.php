<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Modules\SuperAdmin\Domain\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Facility::withCount('schools')->orderBy('order')->orderBy('name');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $facilities = $query->paginate(15)->withQueryString();
        $totalCount = Facility::count();
        $activeCount = Facility::where('is_active', true)->count();

        return view('SuperAdmin::facilities', compact('facilities', 'search', 'totalCount', 'activeCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'order' => 'nullable|integer|min:0',
        ]);

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (Facility::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-" . (++$counter);
        }

        Facility::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'icon' => $validated['icon'] ?: 'ph-buildings',
            'category' => $validated['category'] ?: 'Général',
            'description' => $validated['description'] ?? null,
            'order' => $validated['order'] ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('superadmin.facilities')->with('success', 'Équipement ajouté avec succès.');
    }

    public function update(Request $request, int $id)
    {
        $facility = Facility::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $facility->update([
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?: $facility->icon,
            'category' => $validated['category'] ?: $facility->category,
            'description' => $validated['description'] ?? null,
            'order' => $validated['order'] ?? $facility->order,
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : $facility->is_active,
        ]);

        return redirect()->route('superadmin.facilities')->with('success', 'Équipement mis à jour avec succès.');
    }

    public function toggle(int $id)
    {
        $facility = Facility::findOrFail($id);
        $facility->is_active = !$facility->is_active;
        $facility->save();

        return redirect()->back()->with('success', 'Statut de l\'équipement modifié.');
    }

    public function destroy(int $id)
    {
        $facility = Facility::findOrFail($id);
        $facility->schools()->detach();
        $facility->delete();

        return redirect()->route('superadmin.facilities')->with('success', 'Équipement supprimé avec succès.');
    }
}

<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Domain\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * School Track is the parent-facing school-discovery/comparison tool
 * (mobile app, gated behind a parent's own subscription — see
 * Api\SchoolTrackController). This controller is the school-side counterpart:
 * self-service editing of the marketing profile that makes a school
 * discoverable at all. A school only appears in results once
 * School::isSchoolTrackProfileComplete() is true.
 */
class SchoolTrackController extends Controller
{
    public function edit()
    {
        $school = auth()->user()->school;

        return view('SchoolDashboard::dashboard.school_track_edit', compact('school'));
    }

    public function update(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'description' => 'nullable|string|max:2000',
            'levels' => 'nullable|array',
            'levels.*' => 'in:' . implode(',', School::SCHOOL_TRACK_LEVELS),
            'tags' => 'nullable|string|max:500',
            'facilities' => 'nullable|array',
            'facilities.*' => 'in:' . implode(',', School::FACILITY_KEYS),
            'success_rate' => 'nullable|integer|min:0|max:100',
            'academic_radar' => 'nullable|array',
            'academic_radar.*' => 'nullable|integer|min:0|max:100',
            'nearby_places' => 'nullable|array',
            'nearby_places.*.emoji' => 'nullable|string|max:8',
            'nearby_places.*.label' => 'nullable|string|max:100',
            'nearby_places.*.distance' => 'nullable|string|max:50',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|max:4096',
            'remove_gallery' => 'nullable|array',
            'remove_gallery.*' => 'string',
        ]);

        $school->description = $validated['description'] ?? null;
        $school->levels = $validated['levels'] ?? [];

        $school->tags = collect(explode(',', $request->input('tags', '')))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->values()
            ->all();

        $checkedFacilities = $validated['facilities'] ?? [];
        $facilities = [];
        foreach (School::FACILITY_KEYS as $key) {
            $facilities[$key] = in_array($key, $checkedFacilities, true);
        }
        $school->facilities = $facilities;

        $school->success_rate = $validated['success_rate'] ?? null;

        $radarInput = $validated['academic_radar'] ?? [];
        $radar = [];
        foreach (School::ACADEMIC_RADAR_KEYS as $key) {
            if (isset($radarInput[$key]) && $radarInput[$key] !== '') {
                $radar[$key] = (int) $radarInput[$key];
            }
        }
        $school->academic_radar = $radar;

        $school->nearby_places = collect($validated['nearby_places'] ?? [])
            ->filter(fn ($place) => !empty($place['label']))
            ->map(fn ($place) => [
                'emoji' => $place['emoji'] ?? '📍',
                'label' => $place['label'],
                'distance' => $place['distance'] ?? '',
            ])
            ->values()
            ->all();

        $existingGallery = collect($school->gallery_paths ?? []);
        $toRemove = collect($validated['remove_gallery'] ?? []);
        foreach ($toRemove as $path) {
            Storage::disk('public')->delete($path);
        }
        $keptGallery = $existingGallery->reject(fn ($path) => $toRemove->contains($path));

        $newPaths = collect();
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $newPaths->push($file->store('school_track_gallery', 'public'));
            }
        }
        $school->gallery_paths = $keptGallery->concat($newPaths)->values()->all();

        $school->save();

        return redirect()->route('school.school-track.edit')->with('success', 'Le profil School Track a été mis à jour.');
    }
}

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
    /** Read-only profile — what the school actually has on School Track, mirroring the mobile parent view. */
    public function show()
    {
        $school = auth()->user()->school;

        $examSuccessRates = $school->examSuccessRates();
        $availableExamTypes = $school->availableExamTypes();
        $progressionAnnuelle = $school->computedProgressionAnnuelle();
        $isComplete = $school->isSchoolTrackProfileComplete();
        // Same merged (dynamic catalog + legacy JSON) facility list the
        // mobile app receives via toSchoolTrackArray() — single source of
        // truth, so this page never drifts from what parents actually see.
        $facilities = $school->toSchoolTrackArray()['allFacilities'];

        return view('SchoolDashboard::dashboard.school_track_show', compact(
            'school', 'examSuccessRates', 'availableExamTypes', 'progressionAnnuelle', 'isComplete', 'facilities'
        ));
    }

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
            'academic_radar' => 'nullable|array',
            'academic_radar.*' => 'nullable|integer|min:0|max:100',
            'gallery' => 'nullable|array',
            // Must stay at or under php.ini's upload_max_filesize (currently
            // 10M) — a larger file gets silently dropped by PHP before
            // Laravel ever sees it, so this rule is the only thing that can
            // actually show the user an error instead of nothing happening.
            'gallery.*' => 'image|max:10240',
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

        $radarInput = $validated['academic_radar'] ?? [];
        $radar = [];
        foreach (School::ACADEMIC_RADAR_KEYS as $key) {
            if (isset($radarInput[$key]) && $radarInput[$key] !== '') {
                $radar[$key] = (int) $radarInput[$key];
            }
        }
        $school->academic_radar = $radar;

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

        return redirect()->route('school.school-track')->with('success', 'Le profil School Track a été mis à jour.');
    }
}

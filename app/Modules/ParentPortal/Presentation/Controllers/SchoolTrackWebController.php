<?php

namespace App\Modules\ParentPortal\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\ParentPortal\Application\Services\ParentPortalService;
use App\Modules\SchoolTrack\Application\Services\SchoolTrackAccessService;
use App\Modules\SchoolTrack\Domain\Models\SchoolTrackSubscription;
use App\Modules\SuperAdmin\Domain\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolTrackWebController extends Controller
{
    /**
     * Same "locked" destination the sidebar link uses — enforced here too so
     * the paywall holds for a bookmarked/typed URL, not just the sidebar.
     */
    private function requireActiveSubscription(array $status)
    {
        if (($status['moduleEnabled'] ?? true) && !($status['active'] ?? false)) {
            return redirect()->route('parent.dashboard', ['school_track' => 'locked'])
                ->with('error', 'Votre forfait School Track est inactif. Souscrivez pour y accéder.');
        }

        return null;
    }

    /**
     * The point every "distance" figure is measured from. Preference order:
     * 1) the parent's own address, when they've set one via a real
     *    autocomplete selection (Paramètres) — what they actually care
     *    about (e.g. "distance from home");
     * 2) the parent's child's current school, as a fallback that's still
     *    real and meaningful for a "should we switch schools" comparison;
     * 3) null/null (never a fabricated point) when neither is known, so
     *    toSchoolTrackArray() honestly reports distanceKm as null too.
     * Also returns the human label describing which one was used, so the
     * views can say what the distance is actually measured from.
     */
    private function referenceCoordinates(): array
    {
        $parent = Auth::guard('parent')->user();

        if ($parent && $parent->latitude !== null && $parent->longitude !== null) {
            return [$parent->latitude, $parent->longitude, 'votre adresse'];
        }

        $child = $parent ? app(ParentPortalService::class)->childrenOf($parent)->first() : null;
        $coords = $child ? School::find($child->school_id)?->getCoordinates() : null;

        if (($coords['lat'] ?? null) !== null && ($coords['lng'] ?? null) !== null) {
            return [$coords['lat'], $coords['lng'], "l'école actuelle de votre enfant"];
        }

        return [null, null, null];
    }

    /**
     * Helper to format a School model with complete School Track attributes.
     * Every field here traces back to a real column or a real computed
     * School method — no per-school-code lookup tables, no invented photos,
     * ratings, fees or distances.
     */
    private function transformSchool(School $school, ?float $userLat, ?float $userLng): array
    {
        $data = $school->toSchoolTrackArray($userLat, $userLng);

        $photo = $data['imageUrl'] ?: ($data['galleryUrls'][0] ?? null);
        $frais = $data['fraisAnnuels'];
        $dist = $data['distanceKm'];

        return array_merge($data, [
            'model_id' => $school->id,
            'photo' => $photo,
            // Real, blended promotion/exam-admission score (0-100) — replaces
            // the old fabricated 5-star rating with an actually computed one.
            'performanceScore' => $school->performanceScore(),
            'frais_formatted' => $frais !== null ? number_format($frais, 0, ',', ' ') . ' FCFA' : 'Non renseigné',
            'frais_numeric' => $frais,
            'distance_formatted' => $dist !== null ? number_format($dist, 1) . ' km' : null,
            'distance_numeric' => $dist,
            'lat' => $data['latitude'],
            'lng' => $data['longitude'],
        ]);
    }

    /**
     * School Track: Discovery / Catalog View (Screenshot 1).
     */
    public function index(Request $request, SchoolTrackAccessService $access)
    {
        $parent = Auth::guard('parent')->user();
        $status = $access->statusFor($parent);
        if ($redirect = $this->requireActiveSubscription($status)) {
            return $redirect;
        }

        $query = strtolower(trim($request->query('q', '')));
        $level = $request->query('level');
        $maxPrice = $request->query('max_price');
        $minRating = $request->query('min_rating');
        $facility = $request->query('facility');
        $sortBy = $request->query('sort_by', 'rendement');

        [$refLat, $refLng, $distanceLabel] = $this->referenceCoordinates();
        $schoolsQuery = School::with('facilitiesList')
            ->where('status', '!=', 'suspendu');

        $schools = $schoolsQuery->get()->map(fn(School $s) => $this->transformSchool($s, $refLat, $refLng));

        // Keyword filter
        if (!empty($query)) {
            $schools = $schools->filter(function ($s) use ($query) {
                return str_contains(strtolower($s['name']), $query)
                    || str_contains(strtolower($s['location'] ?? ''), $query)
                    || str_contains(strtolower($s['city'] ?? ''), $query)
                    || collect($s['tags'] ?? [])->contains(fn($t) => str_contains(strtolower($t), $query));
            });
        }

        // Level filter
        if (!empty($level) && $level !== 'all') {
            $schools = $schools->filter(function ($s) use ($level) {
                return collect($s['levels'] ?? [])->contains(fn($l) => str_contains(strtolower($l), strtolower($level)))
                    || str_contains(strtolower($s['type'] ?? ''), strtolower($level));
            });
        }

        // Price filter
        if (!empty($maxPrice)) {
            $schools = $schools->filter(fn($s) => $s['frais_numeric'] <= (int) $maxPrice);
        }

        // Min performance-score filter (the filter bar still labels this "note
        // minimale" — it now reads the real blended score, not a fake rating)
        if (!empty($minRating)) {
            $schools = $schools->filter(fn($s) => $s['performanceScore'] !== null && $s['performanceScore'] >= (float) $minRating);
        }

        // Facility filter
        if (!empty($facility)) {
            $schools = $schools->filter(fn($s) => !empty($s['facilities'][$facility]));
        }

        // Sorting — nulls (no real data yet) always sort last, never mixed in
        // ahead of schools with an actual computed value.
        $sorted = match ($sortBy) {
            'proximite' => $schools->sortBy(fn ($s) => $s['distance_numeric'] ?? PHP_FLOAT_MAX),
            'frais_asc' => $schools->sortBy(fn ($s) => $s['frais_numeric'] ?? PHP_FLOAT_MAX),
            'frais_desc' => $schools->sortByDesc(fn ($s) => $s['frais_numeric'] ?? -1),
            default => $schools->sortByDesc(fn ($s) => $s['performanceScore'] ?? -1),
        };
        $schools = $sorted->values();

        $comparisonIds = session()->get('school_track_comparison', []);

        return view('ParentPortal::school_track.index', compact(
            'schools',
            'status',
            'query',
            'level',
            'maxPrice',
            'minRating',
            'facility',
            'sortBy',
            'comparisonIds',
            'distanceLabel'
        ));
    }

    /**
     * School Track: Side-by-side Comparisons (Screenshot 2).
     */
    public function compare(Request $request, SchoolTrackAccessService $access)
    {
        $parent = Auth::guard('parent')->user();
        $status = $access->statusFor($parent);
        if ($redirect = $this->requireActiveSubscription($status)) {
            return $redirect;
        }

        $idsParam = $request->query('ids');
        if (!empty($idsParam)) {
            $ids = array_filter(explode(',', (string) $idsParam));
        } else {
            $ids = session()->get('school_track_comparison', []);
        }

        [$refLat, $refLng, $distanceLabel] = $this->referenceCoordinates();
        $schools = School::with('facilitiesList')
            ->whereIn('id', $ids)
            ->get()
            ->map(fn(School $s) => $this->transformSchool($s, $refLat, $refLng));

        // Default comparison if none selected
        if ($schools->count() < 2) {
            $schools = School::with('facilitiesList')
                ->where('status', '!=', 'suspendu')
                ->take(3)
                ->get()
                ->map(fn(School $s) => $this->transformSchool($s, $refLat, $refLng));
        }

        $allSchools = School::where('status', '!=', 'suspendu')->get(['id', 'name', 'code']);

        return view('ParentPortal::school_track.compare', compact('schools', 'status', 'allSchools', 'distanceLabel'));
    }

    /**
     * School Track: Map Explorer (Screenshot 3).
     */
    public function map(Request $request, SchoolTrackAccessService $access)
    {
        $parent = Auth::guard('parent')->user();
        $status = $access->statusFor($parent);
        if ($redirect = $this->requireActiveSubscription($status)) {
            return $redirect;
        }

        $level = $request->query('level', 'all');
        $minRating = $request->query('min_rating');
        $facility = $request->query('facility');
        $query = strtolower(trim($request->query('q', '')));

        [$refLat, $refLng, $distanceLabel] = $this->referenceCoordinates();
        $schools = School::with('facilitiesList')
            ->where('status', '!=', 'suspendu')
            ->get()
            ->map(fn(School $s) => $this->transformSchool($s, $refLat, $refLng));

        if (!empty($query)) {
            $schools = $schools->filter(fn($s) => str_contains(strtolower($s['name']), $query) || str_contains(strtolower($s['location']), $query));
        }
        if (!empty($level) && $level !== 'all') {
            $schools = $schools->filter(fn($s) => collect($s['levels'] ?? [])->contains(fn($l) => str_contains(strtolower($l), strtolower($level))));
        }
        if (!empty($minRating)) {
            $schools = $schools->filter(fn($s) => $s['performanceScore'] !== null && $s['performanceScore'] >= (float) $minRating);
        }

        $schools = $schools->values();
        $comparisonIds = session()->get('school_track_comparison', []);

        return view('ParentPortal::school_track.map', compact('schools', 'status', 'level', 'minRating', 'facility', 'query', 'comparisonIds', 'distanceLabel'));
    }

    /**
     * School Track: Detail Profile View (Screenshots 4 & 5).
     */
    public function show(Request $request, $id, SchoolTrackAccessService $access)
    {
        $school = School::with('facilitiesList')->find((int) $id);
        if (!$school) {
            $school = School::with('facilitiesList')->where('code', $id)->first();
        }
        abort_if(!$school, 404, "Établissement non trouvé.");

        [$refLat, $refLng, $distanceLabel] = $this->referenceCoordinates();
        $data = $this->transformSchool($school, $refLat, $refLng);
        $parent = Auth::guard('parent')->user();
        $status = $access->statusFor($parent);
        if ($redirect = $this->requireActiveSubscription($status)) {
            return $redirect;
        }

        // Academic performance table: real exam success rates for this
        // school (current vs. previous academic year), one row per exam
        // type it has ever validated a session for. No invented years,
        // stats or trends — a school with no validated exams yet just
        // produces an empty collection, handled honestly in the view.
        $currentYear = School::currentAcademicYear();
        $previousYear = School::previousAcademicYear($currentYear);
        $academicMetrics = collect(\App\Modules\Academic\Domain\Models\ExamSession::LABELS)
            ->map(function (string $label, string $type) use ($school, $currentYear, $previousYear) {
                $current = $school->examSuccessRate($type, $currentYear);
                $previous = $school->examSuccessRate($type, $previousYear);
                if ($current === null && $previous === null) {
                    return null;
                }
                $trend = ($current !== null && $previous !== null) ? $current - $previous : null;

                return [
                    'metric' => "Taux de Réussite {$label}",
                    'yPrevious' => $previous !== null ? "{$previous}%" : '—',
                    'yCurrent' => $current !== null ? "{$current}%" : '—',
                    'trend' => $trend !== null ? (($trend >= 0 ? '+ ' : '- ') . abs($trend) . '%') : null,
                    'trend_up' => $trend !== null ? $trend >= 0 : null,
                ];
            })
            ->filter()
            ->values();

        // Real facilities the school actually has on file — no invented
        // labs/IT rooms/canteen, and no fake "Campus Security" section
        // (dropped entirely below since nothing backs it — see show.blade.php).
        $facilityCards = collect($data['allFacilities'] ?? [])
            ->filter(fn (array $f) => $f['is_available'])
            ->map(fn (array $f) => ['title' => $f['name'], 'desc' => $f['description'] ?? '', 'ph_icon' => $f['icon']])
            ->values();

        return view('ParentPortal::school_track.show', compact(
            'school',
            'data',
            'status',
            'currentYear',
            'previousYear',
            'academicMetrics',
            'facilityCards',
            'distanceLabel'
        ));
    }

    /**
     * Toggle Comparison list Ajax/POST.
     */
    public function toggleCompare(Request $request)
    {
        $schoolId = (int) $request->input('school_id');
        $comparison = session()->get('school_track_comparison', []);

        if (in_array($schoolId, $comparison)) {
            $comparison = array_values(array_diff($comparison, [$schoolId]));
            $isAdded = false;
        } else {
            if (count($comparison) >= 4) {
                array_shift($comparison);
            }
            $comparison[] = $schoolId;
            $isAdded = true;
        }

        session()->put('school_track_comparison', $comparison);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'is_compared' => $isAdded, 'total' => count($comparison), 'ids' => $comparison]);
        }

        return back();
    }

    /**
     * School Track: Subscribe Action.
     */
    public function subscribe(Request $request, SchoolTrackAccessService $access)
    {
        $validated = $request->validate([
            'plan' => 'required|in:' . implode(',', array_keys(SchoolTrackSubscription::PLAN_PRICES)),
            'payment_method' => 'nullable|in:' . implode(',', array_keys(Payment::METHODS)),
        ]);

        $access->subscribe(Auth::guard('parent')->user(), $validated['plan'], $validated['payment_method'] ?? 'cash');

        return redirect()->route('parent.school-track.index')->with('success', 'Votre abonnement School Track est maintenant actif !');
    }
}

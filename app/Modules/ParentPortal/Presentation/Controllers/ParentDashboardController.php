<?php

namespace App\Modules\ParentPortal\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Canteen\Application\Services\CanteenEnrollmentService;
use App\Modules\ParentPortal\Application\Services\ParentPortalService;
use App\Modules\SchoolTrack\Application\Services\SchoolTrackAccessService;
use App\Modules\SuperAdmin\Application\Services\AddressGeocodingService;
use App\Modules\Transport\Application\Services\TransportEnrollmentService;
use App\Modules\Transport\Domain\Models\Route as TransportRoute;
use App\Modules\Transport\Domain\Models\TransportEnrollmentRequest;
use App\Modules\Canteen\Domain\Models\CanteenEnrollmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentDashboardController extends Controller
{
    public function dashboard(ParentPortalService $service, SchoolTrackAccessService $schoolTrackAccess)
    {
        $parent = Auth::guard('parent')->user();
        $overview = $service->overview($parent);
        $overview['schoolTrackStatus'] = $schoolTrackAccess->statusFor($parent);

        return view('ParentPortal::dashboard', array_merge(['parent' => $parent], $overview));
    }

    public function academic(Request $request, ParentPortalService $service)
    {
        $parent = Auth::guard('parent')->user();
        $studentId = $request->query('student') ? (int) $request->query('student') : null;
        $data = $service->academic($parent, $studentId);

        return view('ParentPortal::academic', array_merge(['parent' => $parent], $data));
    }

    public function services(Request $request, ParentPortalService $service)
    {
        $parent = Auth::guard('parent')->user();
        $studentId = $request->query('student') ? (int) $request->query('student') : null;
        $data = $service->services($parent, $studentId);

        return view('ParentPortal::services', array_merge(['parent' => $parent], $data));
    }

    public function infirmary(Request $request, ParentPortalService $service)
    {
        $parent = Auth::guard('parent')->user();
        $studentId = $request->query('student') ? (int) $request->query('student') : null;
        $data = $service->infirmary($parent, $studentId);

        return view('ParentPortal::infirmary', array_merge(['parent' => $parent], $data));
    }

    public function schoolAccess(Request $request, ParentPortalService $service)
    {
        $parent = Auth::guard('parent')->user();
        $studentId = $request->query('student') ? (int) $request->query('student') : null;
        $data = $service->schoolAccess($parent, $studentId);

        return view('ParentPortal::school_access', array_merge(['parent' => $parent], $data));
    }

    public function settings(ParentPortalService $service)
    {
        $parent = Auth::guard('parent')->user();
        $data = $service->settings($parent);

        return view('ParentPortal::settings', array_merge(['parent' => $parent], $data));
    }

    public function updateSettings(Request $request)
    {
        $parent = Auth::guard('parent')->user();
        $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $fullName = trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? ''));
        if (empty($fullName)) {
            $fullName = $parent->name;
        }

        $parent->update([
            'name' => $fullName,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            // Coordinates are only ever trusted when they came from a real
            // selected autocomplete suggestion, never a free-text guess —
            // the frontend clears these two fields the moment the address
            // text is edited without picking a fresh suggestion.
            'latitude' => $request->filled('address') ? $request->latitude : null,
            'longitude' => $request->filled('address') ? $request->longitude : null,
        ]);

        return back()->with('success', 'Vos informations ont été mises à jour avec succès.');
    }

    /** Server-side proxy to Nominatim so the address-autocomplete field never calls a third party directly from the browser. */
    public function searchAddress(Request $request, AddressGeocodingService $geocoder)
    {
        return response()->json($geocoder->search((string) $request->query('q', '')));
    }

    public function signLegalDocument(int $legalDocument, ParentPortalService $service)
    {
        $parent = Auth::guard('parent')->user();
        $service->signLegalDocument($parent, $legalDocument);

        return back()->with('success', 'Document signé avec succès.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password:parent',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $parent = Auth::guard('parent')->user();
        $parent->update([
            'password' => $request->password,
            'password_changed_at' => now(),
        ]);

        return back()->with('success', 'Votre mot de passe a été modifié avec succès.');
    }

    public function bulletin(int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent(Auth::guard('parent')->user(), $student);
        $data = $service->bulletin($child);

        return view('ParentPortal::bulletin', array_merge(['child' => $child], $data));
    }

    public function attendance(int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent(Auth::guard('parent')->user(), $student);
        $data = $service->attendance($child);

        return view('ParentPortal::attendance', array_merge(['child' => $child], $data));
    }

    public function homework(int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent(Auth::guard('parent')->user(), $student);
        $data = $service->homework($child);

        return view('ParentPortal::homework', array_merge(['child' => $child], $data));
    }

    public function diplomas(int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent(Auth::guard('parent')->user(), $student);
        $data = $service->diplomas($child);

        return view('ParentPortal::diplomas', array_merge(['child' => $child], $data));
    }

    public function card(int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent(Auth::guard('parent')->user(), $student);
        $data = $service->studentCard($child);

        return view('ParentPortal::card', array_merge(['child' => $child], $data));
    }

    public function printDiploma(int $student, int $award, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent(Auth::guard('parent')->user(), $student);

        $awardModel = \App\Modules\Academic\Domain\Models\Award::where('recipient_type', 'student')
            ->where('recipient_id', $child->id)
            ->findOrFail($award);

        return app(\App\Modules\SchoolDashboard\Presentation\Controllers\DiplomaTemplateController::class)->renderForAward($awardModel);
    }

    public function finance(ParentPortalService $service)
    {
        $parent = Auth::guard('parent')->user();
        $data = $service->finance($parent);

        return view('ParentPortal::finance', array_merge(['parent' => $parent], $data));
    }

    public function fees(int $student, ParentPortalService $service, CanteenEnrollmentService $canteenEnrollment, TransportEnrollmentService $transportEnrollment)
    {
        $parent = Auth::guard('parent')->user();
        $child = $service->ensureChildBelongsToParent($parent, $student);
        $financeData = $service->finance($parent);

        // Tuition always applies; cantine/transport only show up here when
        // the student is actually enrolled in that service — otherwise a
        // school-wide fee schedule they don't owe would show as if they did.
        $fees = ['tuition' => $service->fees($child, 'tuition')];

        if ($canteenEnrollment->isEnrolled($child->id)) {
            $fees['cantine'] = $service->fees($child, 'cantine');
        }

        if ($transportEnrollment->isEnrolled($child->id, 'morning') || $transportEnrollment->isEnrolled($child->id, 'evening')) {
            $fees['transport'] = $service->fees($child, 'transport');
        }

        return view('ParentPortal::fees', array_merge(['child' => $child, 'parent' => $parent, 'fees' => $fees], $financeData));
    }

    public function canteen(int $student, ParentPortalService $service, CanteenEnrollmentService $enrollmentService)
    {
        $child = $service->ensureChildBelongsToParent(Auth::guard('parent')->user(), $student);
        $data = $service->canteen($child);
        $data['canteenEnrolled'] = $enrollmentService->isEnrolled($child->id);
        $data['canteenEnrollmentRequest'] = $enrollmentService->latestRequestFor($child->id);

        return view('ParentPortal::canteen', array_merge(['child' => $child], $data));
    }

    public function requestCanteenEnrollment(int $student, ParentPortalService $service, CanteenEnrollmentService $enrollmentService)
    {
        $parent = Auth::guard('parent')->user();
        $child = $service->ensureChildBelongsToParent($parent, $student);

        $pending = $enrollmentService->latestRequestFor($child->id);
        if ($pending && $pending->status === CanteenEnrollmentRequest::STATUS_PENDING) {
            return redirect()->route('parent.canteen', $student)->with('success', 'Une demande est déjà en attente.');
        }

        $enrollmentService->requestEnrollment($child, $parent);

        return redirect()->route('parent.canteen', $student)->with('success', "Votre demande d'inscription à la cantine a été envoyée à l'école.");
    }

    /**
     * Normalized {status, rejectionReason, pendingStopId} for one period —
     * 'approved' always wins even over a stale non-approved request row, since
     * the pivot (isEnrolled) is the real source of truth. `pendingStopId` lets
     * the page pre-select the zone/stop the parent already asked for, so they
     * can amend a still-pending request instead of starting over blind.
     */
    private function transportPeriodStatus(int $studentId, string $period, TransportEnrollmentService $enrollmentService): array
    {
        if ($enrollmentService->isEnrolled($studentId, $period)) {
            return ['status' => 'approved', 'rejectionReason' => null, 'pendingStopId' => null];
        }
        $latest = $enrollmentService->latestRequestFor($studentId, $period);

        return [
            'status' => $latest->status ?? 'none',
            'rejectionReason' => $latest->rejection_reason ?? null,
            'pendingStopId' => ($latest && $latest->status === TransportEnrollmentRequest::STATUS_PENDING) ? $latest->route_stop_id : null,
        ];
    }

    /**
     * A student is picked up and dropped off at the same home, so once a period
     * has a real (enrolled or pending) stop, the other period is locked to that
     * same zone — a rejected/withdrawn request doesn't count, the parent is free
     * to pick again for both periods in that case.
     */
    private function lockedZoneFor(?\App\Modules\Transport\Domain\Models\RouteStop $enrolledStop, string $studentId, string $period, TransportEnrollmentService $enrollmentService): ?string
    {
        $zoneOf = fn ($stop) => $stop?->route ? ($stop->route->zone ?: $stop->route->name) : null;

        if ($enrolledStop) {
            return $zoneOf($enrolledStop);
        }

        $latest = $enrollmentService->latestRequestFor((int) $studentId, $period);
        if ($latest && $latest->status === TransportEnrollmentRequest::STATUS_PENDING) {
            return $zoneOf($latest->routeStop()->with('route')->first());
        }

        return null;
    }

    public function transport(int $student, ParentPortalService $service, TransportEnrollmentService $enrollmentService)
    {
        $child = $service->ensureChildBelongsToParent(Auth::guard('parent')->user(), $student);
        $data = $service->transport($child);
        $data['morningStatus'] = $this->transportPeriodStatus($child->id, 'morning', $enrollmentService);
        $data['eveningStatus'] = $this->transportPeriodStatus($child->id, 'evening', $enrollmentService);

        $data['lockedZoneByPeriod'] = [
            'morning' => $this->lockedZoneFor($data['morningStop'] ?? null, (string) $child->id, 'morning', $enrollmentService),
            'evening' => $this->lockedZoneFor($data['eveningStop'] ?? null, (string) $child->id, 'evening', $enrollmentService),
        ];

        // A route's `period` column is null for "both periods", or locked to one —
        // a morning-only route (e.g. "Zone Nord Matin A") must never show up in the
        // evening picker and vice versa.
        $routes = TransportRoute::where('school_id', $child->school_id)->with('stops')->get();
        $data['availableRoutesByPeriod'] = [
            'morning' => $routes->filter(fn ($r) => in_array($r->period, [null, 'morning'], true))->values(),
            'evening' => $routes->filter(fn ($r) => in_array($r->period, [null, 'evening'], true))->values(),
        ];

        // Grouped by zone (a route's own `zone` field, falling back to its name for
        // the handful of test routes with no zone set) — the parent picks a zone
        // first, then an arrêt within it, rather than one long mixed list. Only
        // stops with real coordinates can be placed on the map or matched against
        // a geocoded address.
        $otherPeriod = ['morning' => 'evening', 'evening' => 'morning'];
        $data['zonesByPeriod'] = collect($data['availableRoutesByPeriod'])
            ->map(function ($routesForPeriod, $period) use ($data, $otherPeriod) {
                $byZone = [];
                foreach ($routesForPeriod as $r) {
                    $zoneName = $r->zone ?: $r->name;
                    foreach ($r->stops as $s) {
                        if ($s->latitude === null || $s->longitude === null) {
                            continue;
                        }
                        $byZone[$zoneName][] = [
                            'id' => $s->id,
                            'name' => $s->name,
                            'arrival_time' => $s->arrival_time,
                            'lat' => (float) $s->latitude,
                            'lng' => (float) $s->longitude,
                        ];
                    }
                }

                // Same home address morning and evening — once the other period
                // already has a real (enrolled or pending) zone, this period is
                // locked to it too, so only that one zone is even offered here.
                $lockedZone = $data['lockedZoneByPeriod'][$otherPeriod[$period]] ?? null;
                if ($lockedZone !== null) {
                    $byZone = array_intersect_key($byZone, [$lockedZone => true]);
                }

                return collect($byZone)
                    ->map(fn ($stops, $zone) => ['zone' => $zone, 'stops' => $stops])
                    ->values();
            })
            ->all();

        return view('ParentPortal::transport', array_merge(['child' => $child], $data));
    }

    public function requestTransportEnrollment(int $student, Request $request, ParentPortalService $service, TransportEnrollmentService $enrollmentService)
    {
        $parent = Auth::guard('parent')->user();
        $child = $service->ensureChildBelongsToParent($parent, $student);

        $validated = $request->validate([
            'route_stop_id' => ['required', 'integer', 'exists:transport_route_stops,id'],
            'period' => ['required', 'in:morning,evening'],
        ]);

        $pending = $enrollmentService->latestRequestFor($child->id, $validated['period']);
        $isPending = $pending && $pending->status === TransportEnrollmentRequest::STATUS_PENDING;

        $stop = \App\Modules\Transport\Domain\Models\RouteStop::where('school_id', $child->school_id)
            ->with('route')
            ->findOrFail($validated['route_stop_id']);

        // Same home address morning and evening: once the other period already has
        // a real (enrolled or pending) zone, this request must use that same zone.
        // The zone picker on the page already only offers that zone, so hitting this
        // means the form was tampered with or the two tabs raced each other.
        $otherPeriod = $validated['period'] === 'morning' ? 'evening' : 'morning';
        $otherPeriodStop = $service->transport($child)[$otherPeriod . 'Stop'] ?? null;
        $lockedZone = $this->lockedZoneFor($otherPeriodStop, (string) $child->id, $otherPeriod, $enrollmentService);
        $requestedZone = $stop->route ? ($stop->route->zone ?: $stop->route->name) : null;

        if ($lockedZone !== null && $requestedZone !== $lockedZone) {
            return redirect()->route('parent.transport', $student)
                ->with('error', "Le trajet du " . ($otherPeriod === 'morning' ? 'Matin' : 'Soir') . " utilise déjà la zone « {$lockedZone} » — le trajet du " . ($validated['period'] === 'morning' ? 'Matin' : 'Soir') . " doit être dans la même zone.");
        }

        // Still awaiting the school's decision — amend that same request in place
        // rather than piling up a second pending row for the same period.
        if ($isPending) {
            $pending->update(['route_stop_id' => $stop->id]);

            return redirect()->route('parent.transport', $student)->with('success', "Votre demande d'inscription au bus a été modifiée.");
        }

        $enrollmentService->requestEnrollment($child, $stop, $validated['period'], $parent);

        return redirect()->route('parent.transport', $student)->with('success', "Votre demande d'inscription au bus a été envoyée à l'école.");
    }
}

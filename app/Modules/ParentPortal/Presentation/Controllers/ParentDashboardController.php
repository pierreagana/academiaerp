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

    /** Normalized {status, rejectionReason} for one period — 'approved' always wins even over a stale non-approved request row, since the pivot (isEnrolled) is the real source of truth. */
    private function transportPeriodStatus(int $studentId, string $period, TransportEnrollmentService $enrollmentService): array
    {
        if ($enrollmentService->isEnrolled($studentId, $period)) {
            return ['status' => 'approved', 'rejectionReason' => null];
        }
        $latest = $enrollmentService->latestRequestFor($studentId, $period);

        return ['status' => $latest->status ?? 'none', 'rejectionReason' => $latest->rejection_reason ?? null];
    }

    public function transport(int $student, ParentPortalService $service, TransportEnrollmentService $enrollmentService)
    {
        $child = $service->ensureChildBelongsToParent(Auth::guard('parent')->user(), $student);
        $data = $service->transport($child);
        $data['morningStatus'] = $this->transportPeriodStatus($child->id, 'morning', $enrollmentService);
        $data['eveningStatus'] = $this->transportPeriodStatus($child->id, 'evening', $enrollmentService);
        $data['availableRoutes'] = TransportRoute::where('school_id', $child->school_id)->with('stops')->get();

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
        if ($pending && $pending->status === TransportEnrollmentRequest::STATUS_PENDING) {
            return redirect()->route('parent.transport', $student)->with('success', 'Une demande est déjà en attente pour cette période.');
        }

        $stop = \App\Modules\Transport\Domain\Models\RouteStop::where('school_id', $child->school_id)->findOrFail($validated['route_stop_id']);
        $enrollmentService->requestEnrollment($child, $stop, $validated['period'], $parent);

        return redirect()->route('parent.transport', $student)->with('success', "Votre demande d'inscription au bus a été envoyée à l'école.");
    }
}

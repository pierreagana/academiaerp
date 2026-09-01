<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Presence\Application\Services\AccessControlStatsService;
use App\Modules\Presence\Application\Services\AttendanceStatsService;
use App\Modules\Presence\Application\UseCases\RecordAccessCheckInUseCase;
use App\Modules\Presence\Application\UseCases\SaveAttendanceUseCase;
use App\Modules\Presence\Domain\Models\AccessDevice;
use App\Modules\Presence\Domain\Models\AttendanceRecord;
use App\Modules\Presence\Domain\Repositories\AccessLogRepositoryInterface;
use App\Modules\Presence\Domain\Repositories\AccessPointRepositoryInterface;
use App\Modules\Presence\Domain\Repositories\AttendanceRecordRepositoryInterface;
use App\Modules\SchoolDashboard\Application\Services\TeacherSessionCheckinService;
use App\Modules\Transport\Domain\Repositories\BusRepositoryInterface;
use App\Modules\Transport\Domain\Repositories\RouteRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PresenceController extends Controller
{
    /**
     * A teacher taking today's attendance for a class they teach today must have
     * already pointed their own presence for that session first. Doesn't apply to
     * non-teacher staff, past/future dates, or classes the teacher doesn't teach
     * today — those cases fall outside what "pointage" can even mean.
     */
    private function checkinGateMessage(int $classId, string $date, TeacherSessionCheckinService $checkinService): ?string
    {
        if ($date !== Carbon::today()->toDateString()) {
            return null;
        }

        $teacher = auth()->user()->teacher;
        if (!$teacher) {
            return null;
        }

        if (!$checkinService->teachesClassToday($teacher, $classId)) {
            return "Vous n'avez pas cours avec cette classe aujourd'hui.";
        }

        if ($checkinService->hasCheckedInForClassToday($teacher, $classId)) {
            return null;
        }

        if ($checkinService->missedCheckinForClassToday($teacher, $classId)) {
            return "Vous êtes marqué absent pour ce cours : le pointage n'a pas été fait dans l'heure suivant le début du cours. L'appel n'est plus disponible pour cette séance — contactez l'administration.";
        }

        return "Vous devez pointer votre présence pour ce cours avant de prendre l'appel. Rendez-vous sur votre tableau de bord (Mes Classes).";
    }

    public function attendanceDashboard(Request $request, AttendanceStatsService $stats)
    {
        $schoolId = auth()->user()->school_id;
        $branchId = auth()->user()->activeBranchId();
        $date = $request->get('date', Carbon::today()->toDateString());

        $dashboard = $stats->dashboardStats($schoolId, $date, $branchId);
        $trend = $stats->weeklyTrend($schoolId, $branchId);
        $repeatedAbsences = $stats->repeatedAbsences($schoolId, $branchId);
        $classOverview = $stats->classOverview($schoolId, $date, $branchId);

        return view('SchoolDashboard::presence.attendance_dashboard', compact('date', 'dashboard', 'trend', 'repeatedAbsences', 'classOverview'));
    }

    public function takeAttendance(Request $request, AttendanceRecordRepositoryInterface $repository, TeacherSessionCheckinService $checkinService, \App\Modules\SchoolDashboard\Application\Services\TeacherDashboardService $teacherDashboard)
    {
        $schoolId = auth()->user()->school_id;
        $branchId = auth()->user()->activeBranchId();
        $classes = AcademicClass::where('school_id', $schoolId)->whereBranch($branchId)->orderBy('name')->get();

        // A teacher only ever takes attendance for their own classes — admin/staff (no
        // linked Teacher) keep seeing every class in the branch, unrestricted.
        $teacher = auth()->user()->teacher;
        if ($teacher) {
            $ownClassIds = $teacherDashboard->myClasses($teacher)->pluck('id');
            $classes = $classes->whereIn('id', $ownClassIds)->values();
        }

        $classId = $request->get('class_id');
        $date = $request->get('date', Carbon::today()->toDateString());

        $selectedClass = null;
        $students = collect();
        $existingStatuses = collect();
        $checkinWarning = null;

        if ($classId) {
            $selectedClass = $classes->firstWhere('id', (int) $classId);
            if ($selectedClass) {
                $checkinWarning = $this->checkinGateMessage($selectedClass->id, $date, $checkinService);
                if (!$checkinWarning) {
                    $students = Student::where('school_id', $schoolId)->where('academic_class_id', $selectedClass->id)->where('status', 'active')->orderBy('first_name')->get();
                    $existingStatuses = $repository->forClassAndDate($selectedClass->id, $date);
                }
            }
        }

        return view('SchoolDashboard::presence.take_attendance', compact('classes', 'selectedClass', 'date', 'students', 'existingStatuses', 'checkinWarning'));
    }

    public function storeAttendance(Request $request, SaveAttendanceUseCase $useCase, TeacherSessionCheckinService $checkinService, \App\Modules\SchoolDashboard\Application\Services\TeacherDashboardService $teacherDashboard)
    {
        $data = $request->validate([
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'date' => ['required', 'date'],
            'statuses' => ['required', 'array', 'min:1'],
            'statuses.*' => ['required', 'string', 'in:' . implode(',', [
                AttendanceRecord::STATUS_PRESENT, AttendanceRecord::STATUS_ABSENT, AttendanceRecord::STATUS_LATE,
            ])],
            'late_minutes' => ['nullable', 'array'],
            'late_minutes.*' => ['nullable', 'integer', 'min:1', 'max:600'],
            'justified' => ['nullable', 'array'],
        ]);

        // Ownership check independent of date/checkin-gate: the checkin gate below only
        // ever applies to today's date, so without this a teacher could submit attendance
        // for any class in the school on a past or future date.
        $teacher = auth()->user()->teacher;
        if ($teacher && !$teacherDashboard->myClasses($teacher)->contains('id', (int) $data['academic_class_id'])) {
            abort(403, "Vous n'êtes pas autorisé à prendre l'appel pour cette classe.");
        }

        $warning = $this->checkinGateMessage((int) $data['academic_class_id'], $data['date'], $checkinService);
        if ($warning) {
            return redirect()->route('school.academic.presence.attendance.take', ['class_id' => $data['academic_class_id'], 'date' => $data['date']])
                ->with('error', $warning);
        }

        $class = AcademicClass::where('school_id', auth()->user()->school_id)->findOrFail($data['academic_class_id']);
        $useCase->execute(
            auth()->user()->school_id,
            (int) $data['academic_class_id'],
            $data['date'],
            $data['statuses'],
            auth()->id(),
            $class->branch_id,
            $data['late_minutes'] ?? [],
            $data['justified'] ?? []
        );

        return redirect()->route('school.academic.presence.attendance.take', ['class_id' => $data['academic_class_id'], 'date' => $data['date']])
            ->with('success', 'Présence enregistrée avec succès !');
    }

    public function accessControlDashboard(
        Request $request,
        AccessControlStatsService $stats,
        AccessPointRepositoryInterface $accessPointRepository,
        AccessLogRepositoryInterface $logRepository
    ) {
        $schoolId = auth()->user()->school_id;
        $branchId = auth()->user()->activeBranchId();
        $accessPointRepository->ensureDefaults();

        $accessPoints = $accessPointRepository->all();
        $onCampus = $stats->onCampusCount($schoolId, $branchId);
        $peakHour = $stats->peakEntryHour($schoolId, $branchId);
        $unauthorizedCount = $stats->unauthorizedTodayCount($schoolId, $branchId);

        $filters = $request->only(['role_label', 'access_point_id']);
        $logs = $logRepository->paginate($filters + ['branch_id' => $branchId], 12);

        return view('SchoolDashboard::presence.access_control', compact(
            'accessPoints', 'onCampus', 'peakHour', 'unauthorizedCount', 'logs', 'filters'
        ));
    }

    public function accessDevicesDashboard(
        AccessPointRepositoryInterface $accessPointRepository,
        BusRepositoryInterface $busRepository,
        RouteRepositoryInterface $routeRepository
    ) {
        $schoolId = auth()->user()->school_id;
        $accessPointRepository->ensureDefaults();

        $devices = AccessDevice::where('school_id', $schoolId)->with(['accessPoint', 'bus', 'route'])->orderBy('name')->get();
        $accessPoints = $accessPointRepository->all();
        $buses = $busRepository->all();
        $routes = $routeRepository->all();
        $schoolCode = auth()->user()->school->code ?? '';

        return view('SchoolDashboard::presence.access_devices', compact(
            'devices', 'accessPoints', 'buses', 'routes', 'schoolCode'
        ));
    }

    public function storeAccessDevice(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:4'],
            'access_type' => ['required', 'string', 'in:' . implode(',', array_keys(AccessDevice::TYPES))],
            'access_point_id' => ['nullable', 'exists:access_points,id'],
            'bus_id' => ['nullable', 'exists:transport_buses,id'],
            'route_id' => ['nullable', 'exists:transport_routes,id'],
        ]);

        AccessDevice::create([
            'school_id' => auth()->user()->school_id,
            'branch_id' => auth()->user()->activeBranchId(),
            'name' => $data['name'],
            'password' => $data['password'],
            'access_type' => $data['access_type'],
            'access_point_id' => in_array($data['access_type'], ['portal_entry', 'portal_exit', 'canteen'], true) ? ($data['access_point_id'] ?? null) : null,
            'bus_id' => in_array($data['access_type'], ['bus_board', 'bus_alight'], true) ? ($data['bus_id'] ?? null) : null,
            'route_id' => in_array($data['access_type'], ['bus_board', 'bus_alight'], true) ? ($data['route_id'] ?? null) : null,
        ]);

        return redirect()->route('school.academic.presence.access.devices')->with('success', 'Appareil de scan créé avec succès !');
    }

    public function toggleAccessDevice($id)
    {
        $device = AccessDevice::where('school_id', auth()->user()->school_id)->findOrFail($id);
        $device->update(['is_active' => !$device->is_active]);

        return redirect()->route('school.academic.presence.access.devices')->with('success', $device->is_active ? 'Appareil réactivé.' : 'Appareil désactivé.');
    }

    public function destroyAccessDevice($id)
    {
        $device = AccessDevice::where('school_id', auth()->user()->school_id)->findOrFail($id);
        $device->tokens()->delete();
        $device->delete();

        return redirect()->route('school.academic.presence.access.devices')->with('success', 'Appareil supprimé avec succès !');
    }

    public function storeCheckIn(Request $request, RecordAccessCheckInUseCase $useCase)
    {
        $data = $request->validate([
            'scanned_code' => ['required', 'string', 'max:255'],
            'action' => ['required', 'string', 'in:entry,exit'],
            'access_point_id' => ['nullable', 'exists:access_points,id'],
        ]);

        $log = $useCase->execute(auth()->user()->school_id, $data['scanned_code'], $data['action'], $data['access_point_id'] ?? null, auth()->user()->activeBranchId());

        $message = $log->authorized
            ? $log->person_name . ' — ' . ($data['action'] === 'entry' ? 'entrée' : 'sortie') . ' enregistrée.'
            : 'Code non reconnu : "' . $log->scanned_code . '" — tentative non autorisée enregistrée.';

        return redirect()->route('school.academic.presence.access')->with($log->authorized ? 'success' : 'warning', $message);
    }

    public function storeAccessPoint(Request $request, AccessPointRepositoryInterface $repository)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $repository->create($data['name']);

        return redirect()->route('school.academic.presence.access')->with('success', 'Portail ajouté avec succès !');
    }

    public function destroyAccessPoint($id, AccessPointRepositoryInterface $repository)
    {
        $repository->delete($id);

        return redirect()->route('school.academic.presence.access')->with('success', 'Portail supprimé avec succès !');
    }

    public function exportAccessLog(AccessLogRepositoryInterface $repository)
    {
        $logs = $repository->paginate(['branch_id' => auth()->user()->activeBranchId()], 1000);

        return response()->streamDownload(function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Heure', 'Individu', 'Code scanné', 'Rôle', 'Action', 'Portail', 'Statut'], ',', '"', '\\');
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->occurred_at->format('d/m/Y H:i:s'),
                    $log->person_name,
                    $log->scanned_code,
                    $log->role_label,
                    $log->action === 'entry' ? 'Entrée' : 'Sortie',
                    $log->accessPoint->name ?? '-',
                    $log->authorized ? 'Autorisé' : 'Non Autorisé',
                ], ',', '"', '\\');
            }
            fclose($file);
        }, 'journal-acces-' . now()->format('Y-m-d') . '.csv');
    }
}

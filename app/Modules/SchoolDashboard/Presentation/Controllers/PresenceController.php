<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Presence\Application\Services\AccessControlStatsService;
use App\Modules\Presence\Application\Services\AttendanceStatsService;
use App\Modules\Presence\Application\UseCases\RecordAccessCheckInUseCase;
use App\Modules\Presence\Application\UseCases\SaveAttendanceUseCase;
use App\Modules\Presence\Domain\Models\AttendanceRecord;
use App\Modules\Presence\Domain\Repositories\AccessLogRepositoryInterface;
use App\Modules\Presence\Domain\Repositories\AccessPointRepositoryInterface;
use App\Modules\Presence\Domain\Repositories\AttendanceRecordRepositoryInterface;
use App\Modules\SchoolDashboard\Application\Services\TeacherSessionCheckinService;
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
        if (!$teacher || !$checkinService->teachesClassToday($teacher, $classId)) {
            return null;
        }

        if ($checkinService->hasCheckedInForClassToday($teacher, $classId)) {
            return null;
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

    public function takeAttendance(Request $request, AttendanceRecordRepositoryInterface $repository, TeacherSessionCheckinService $checkinService)
    {
        $schoolId = auth()->user()->school_id;
        $branchId = auth()->user()->activeBranchId();
        $classes = AcademicClass::where('school_id', $schoolId)->whereBranch($branchId)->orderBy('name')->get();
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

    public function storeAttendance(Request $request, SaveAttendanceUseCase $useCase, TeacherSessionCheckinService $checkinService)
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

    public function accessControlDashboard(Request $request, AccessControlStatsService $stats, AccessPointRepositoryInterface $accessPointRepository, AccessLogRepositoryInterface $logRepository)
    {
        $schoolId = auth()->user()->school_id;
        $branchId = auth()->user()->activeBranchId();
        $accessPointRepository->ensureDefaults();

        $accessPoints = $accessPointRepository->all();
        $onCampus = $stats->onCampusCount($schoolId, $branchId);
        $peakHour = $stats->peakEntryHour($schoolId, $branchId);
        $unauthorizedCount = $stats->unauthorizedTodayCount($schoolId, $branchId);

        $filters = $request->only(['role_label', 'access_point_id']);
        $logs = $logRepository->paginate($filters + ['branch_id' => $branchId], 12);

        return view('SchoolDashboard::presence.access_control', compact('accessPoints', 'onCampus', 'peakHour', 'unauthorizedCount', 'logs', 'filters'));
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

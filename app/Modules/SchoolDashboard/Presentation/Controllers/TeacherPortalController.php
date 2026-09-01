<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\Award;
use App\Modules\Academic\Domain\Models\Timetable;
use App\Modules\Presence\Application\UseCases\RecordAccessCheckInUseCase;
use App\Modules\SchoolDashboard\Application\Services\TeacherAttendanceHistoryService;
use App\Modules\SchoolDashboard\Application\Services\TeacherDashboardService;
use App\Modules\SchoolDashboard\Application\Services\TeacherSessionCheckinService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Self-service pages for the logged-in teacher's own data: "Mes Classes" and
 * "Ma Présence" (own badge-in/out history — not student attendance-taking,
 * see PresenceController/BulletinController for that).
 */
class TeacherPortalController extends Controller
{
    public function classes(TeacherDashboardService $teacherDashboard, TeacherSessionCheckinService $checkinService)
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 403);

        $currentSemester = $teacherDashboard->currentSemester($teacher->school_id);
        $myClasses = $teacherDashboard->myClasses($teacher);

        $classCards = $myClasses->map(function ($class) use ($teacher, $teacherDashboard, $currentSemester, $checkinService) {
            $sharedSubjects = $class->subjects->pluck('name')->intersect($teacher->subjects->pluck('name'));
            $teachesToday = $checkinService->teachesClassToday($teacher, $class->id);

            return [
                'class' => $class,
                'subjectLabel' => $sharedSubjects->isNotEmpty() ? $sharedSubjects->implode(', ') : ($teacher->isHeadTeacherOf($class->id) ? 'Professeur principal' : null),
                'room' => $teacherDashboard->classRoom($teacher, $class),
                'studentCount' => $class->students()->where('status', 'active')->count(),
                'average' => $teacherDashboard->classAverageForClass($teacher, $class, $currentSemester?->id),
                'attendanceRate' => $teacherDashboard->classAttendanceRate($class),
                'teachesToday' => $teachesToday,
                'checkedInToday' => $teachesToday && $checkinService->hasCheckedInForClassToday($teacher, $class->id),
            ];
        });

        $totalStudents = $classCards->sum('studentCount');
        $nextCourse = $teacherDashboard->nextCourse($teacher);
        $gradesToEnter = $teacherDashboard->gradesToEnter($teacher, $currentSemester?->id);
        $todaysCourses = $teacherDashboard->todaysCourses($teacher);

        $diplomaCount = Award::where('recipient_type', 'teacher')->where('recipient_id', $teacher->id)->count();
        $latestDiploma = Award::where('recipient_type', 'teacher')->where('recipient_id', $teacher->id)
            ->with('type')->orderByDesc('awarded_date')->first();

        return view('SchoolDashboard::teacher.classes', compact(
            'teacher', 'classCards', 'totalStudents', 'nextCourse', 'gradesToEnter', 'todaysCourses',
            'diplomaCount', 'latestDiploma'
        ));
    }

    public function diplomas()
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 403);

        $awards = Award::where('recipient_type', 'teacher')->where('recipient_id', $teacher->id)
            ->with('type')->orderByDesc('awarded_date')->get();

        return view('SchoolDashboard::teacher.diplomas', compact('teacher', 'awards'));
    }

    public function printDiploma(int $id)
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 403);

        $award = Award::where('recipient_type', 'teacher')->where('recipient_id', $teacher->id)->findOrFail($id);

        return app(DiplomaTemplateController::class)->renderForAward($award);
    }

    public function checkIn(Request $request, TeacherSessionCheckinService $checkinService)
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 403);

        $data = $request->validate([
            'timetable_id' => ['required', 'integer', 'exists:timetables,id'],
        ]);

        try {
            $checkin = $checkinService->checkIn($teacher, (int) $data['timetable_id']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = $checkin->late_minutes > 0
            ? "Pointage enregistré — {$checkin->late_minutes} min de retard."
            : "Pointage enregistré — à l'heure.";

        return back()->with('success', $message);
    }

    /** Self-service "présence à l'école" badge-in — reuses the same access-control pipeline as a gate scan, keyed by the teacher's own employee_id, so it produces a real, indistinguishable AccessLog row. */
    public function checkInSchool(RecordAccessCheckInUseCase $useCase)
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 403);

        if ($teacher->hasCheckedInAtSchoolToday()) {
            return back()->with('success', 'Vous avez déjà pointé votre présence à l\'école aujourd\'hui.');
        }

        if (empty($teacher->employee_id)) {
            return back()->withErrors(['checkin' => "Aucun matricule (employee_id) n'est enregistré pour votre compte — contactez l'administration."]);
        }

        $log = $useCase->execute($teacher->school_id, $teacher->employee_id, 'entry', null, auth()->user()->activeBranchId());

        $message = $log->authorized
            ? 'Présence à l\'école enregistrée.'
            : "Votre matricule n'a pas été reconnu — contactez l'administration.";

        return back()->with($log->authorized ? 'success' : 'error', $message);
    }

    public function classSchedule(int $classId, TeacherDashboardService $teacherDashboard)
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 403);

        $class = $teacherDashboard->myClasses($teacher)->firstWhere('id', $classId);
        abort_unless($class, 403, "Cette classe ne vous est pas assignée.");

        $days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
        $slotsByDay = Timetable::where('academic_class_id', $class->id)
            ->where('status', 'published')
            ->with(['subject', 'teacher', 'room'])
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        return view('SchoolDashboard::teacher.class_schedule', compact('class', 'days', 'slotsByDay'));
    }

    public function attendanceHistory(Request $request, TeacherAttendanceHistoryService $history)
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 403);

        $month = Carbon::parse($request->get('month', now()->toDateString()))->startOfMonth();

        $stats = $history->monthlyStats($teacher, $month);
        $calendar = $history->monthCalendar($teacher, $month);
        $daily = $history->buildDailyLog($teacher, $month->copy()->startOfMonth(), $month->copy()->endOfMonth())
            ->reverse();

        return view('SchoolDashboard::teacher.attendance_history', compact(
            'teacher', 'month', 'stats', 'calendar', 'daily'
        ));
    }

    public function exportAttendanceHistory(Request $request, TeacherAttendanceHistoryService $history)
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 403);

        $month = Carbon::parse($request->get('month', now()->toDateString()))->startOfMonth();
        $daily = $history->buildDailyLog($teacher, $month->copy()->startOfMonth(), $month->copy()->endOfMonth());

        $labels = ['present' => 'Présent', 'late' => 'Retard', 'absent' => 'Absent'];

        return response()->streamDownload(function () use ($daily, $labels) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Arrivée', 'Départ', 'Durée', 'Statut École', 'Séances Pointées', 'Séances Prévues'], ',', '"', '\\');
            foreach ($daily as $date => $row) {
                if ($row['status'] === 'not_applicable') {
                    continue;
                }
                fputcsv($file, [
                    Carbon::parse($date)->format('d/m/Y'),
                    $row['arrival']?->format('H:i') ?? '-',
                    $row['departure']?->format('H:i') ?? '-',
                    $row['duration_minutes'] ? intdiv($row['duration_minutes'], 60) . 'h' . str_pad($row['duration_minutes'] % 60, 2, '0', STR_PAD_LEFT) : '-',
                    $labels[$row['status']] ?? $row['status'],
                    $row['sessions_checked_in'] ?? 0,
                    $row['sessions_scheduled'] ?? 0,
                ], ',', '"', '\\');
            }
            fclose($file);
        }, 'ma-presence-' . $month->format('Y-m') . '.csv');
    }
}

<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\Timetable;
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

        return view('SchoolDashboard::teacher.classes', compact(
            'teacher', 'classCards', 'totalStudents', 'nextCourse', 'gradesToEnter', 'todaysCourses'
        ));
    }

    public function checkIn(Request $request, TeacherSessionCheckinService $checkinService)
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 403);

        $data = $request->validate([
            'timetable_id' => ['required', 'integer', 'exists:timetables,id'],
        ]);

        $checkin = $checkinService->checkIn($teacher, (int) $data['timetable_id']);

        $message = $checkin->late_minutes > 0
            ? "Pointage enregistré — {$checkin->late_minutes} min de retard."
            : "Pointage enregistré — à l'heure.";

        return back()->with('success', $message);
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
            fputcsv($file, ['Date', 'Arrivée', 'Départ', 'Durée', 'Statut'], ',', '"', '\\');
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
                ], ',', '"', '\\');
            }
            fclose($file);
        }, 'ma-presence-' . $month->format('Y-m') . '.csv');
    }
}

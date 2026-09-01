<?php

namespace App\Modules\ParentPortal\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\Academic\Domain\Models\Semester;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Subject;
use App\Modules\Academic\Domain\Models\Syllabus;
use App\Modules\Academic\Domain\Models\Timetable;
use App\Modules\Bulletin\Domain\Models\BulletinPublication;
use App\Modules\Bulletin\Application\Services\BulletinStatsService;
use App\Modules\Bulletin\Domain\Repositories\BulletinGradeRepositoryInterface;
use App\Modules\Bulletin\Domain\Models\BulletinGrade;
use App\Modules\Canteen\Domain\Models\Account as CanteenAccount;
use App\Modules\Canteen\Domain\Models\CanteenReservation;
use App\Modules\Canteen\Domain\Models\MenuItem;
use App\Modules\Communication\Domain\Models\Event;
use App\Modules\Finance\Domain\Models\FeeLevel;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\Homework\Domain\Models\HomeworkAssignment;
use App\Modules\Homework\Domain\Models\HomeworkSubmission;
use App\Modules\Infirmary\Domain\Models\Allergy as InfirmaryAllergy;
use App\Modules\Infirmary\Domain\Models\Intervention;
use App\Modules\Infirmary\Domain\Models\PrescriptionDocument;
use App\Modules\Infirmary\Domain\Models\Vaccine;
use App\Modules\Library\Domain\Models\Loan;
use App\Modules\ParentPortal\Application\Services\ParentPortalService;
use App\Modules\Presence\Domain\Models\AccessLog;
use App\Modules\Presence\Domain\Models\AttendanceRecord;
use App\Modules\ReportCard\Domain\Models\ReportCardObservation;
use App\Modules\Cards\Domain\Models\CardTemplate;
use App\Modules\SuperAdmin\Domain\Models\Facility;
use App\Modules\SuperAdmin\Domain\Models\School;
use App\Modules\Canteen\Application\Services\CanteenEnrollmentService;
use App\Modules\Canteen\Domain\Models\CanteenEnrollmentRequest;
use App\Modules\Transport\Application\Services\TransportEnrollmentService;
use App\Modules\Transport\Domain\Models\Route as TransportRoute;
use App\Modules\Transport\Domain\Models\RouteStop;
use App\Modules\Transport\Domain\Models\TransportBoardingScan;
use App\Modules\Transport\Domain\Models\TransportEnrollmentRequest;
use App\Modules\Transport\Domain\Models\TripLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Mirrors the Flutter app's mock endpoints (`/api/v1/*`, see
 * `academia/lib/core/network/mock_api_client.dart`) field-for-field, so the
 * existing Dart models/cubits/pages need zero changes — only real data behind
 * them. No fabricated "AI insight" text anywhere: fields like `aiInsight`/
 * `aiAdvice` that the mock filled with invented suggestions come back empty,
 * and any other field with no real backend equivalent (calories, live bus
 * GPS distance, per-course chapter counts) gets an honest empty/zero default
 * instead of a made-up value.
 *
 * Single-student mock shapes (attendance/schedule/courses/canteen/transport/
 * infirmary) resolve "the student" via resolveStudent(): an optional
 * `?student_id=` query param (ownership-checked), falling back to the
 * parent's first linked child when absent.
 */
class MobileParentController extends Controller
{
    public function __construct(private ParentPortalService $service, private BulletinStatsService $bulletinStats)
    {
    }

    private function firstChild(ParentAccount $parent): Student
    {
        $children = $this->service->childrenOf($parent);
        abort_if($children->isEmpty(), 404, "Aucun enfant rattaché à votre compte.");

        return $children->first();
    }

    /**
     * Resolves "the student" for single-child endpoints from an optional
     * `?student_id=` query param — real ownership-checked via the same guard
     * used everywhere else — falling back to the parent's first linked child
     * when absent (unchanged default behavior for callers that don't send it).
     */
    private function resolveStudent(Request $request): Student
    {
        $parent = $request->user();

        if ($request->filled('student_id')) {
            return $this->service->ensureChildBelongsToParent($parent, (int) $request->input('student_id'));
        }

        return $this->firstChild($parent);
    }

    /** Real events for every school one of the parent's children attends (multi-school aware, same as the rest of this controller). */
    private function upcomingEventsQuery($schoolIds)
    {
        return Event::whereIn('school_id', collect($schoolIds)->all())
            ->where('status', '!=', 'cancelled')
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->with('room');
    }

    private function formatEvent(Event $e): array
    {
        return [
            'id' => (string) $e->id,
            'title' => $e->title,
            'month' => mb_strtoupper($e->start_at->translatedFormat('M'), 'UTF-8'),
            'day' => $e->start_at->day,
            'startTime' => $e->start_at->format('H:i'),
            'endTime' => $e->end_at?->format('H:i') ?? '',
            'location' => $e->external_address ?: ($e->room?->name ? 'Salle ' . $e->room->name : ''),
            'type' => $e->type,
        ];
    }

    /** Full upcoming-events list — the "Voir tout" destination for home()'s Activité Académique teaser. */
    public function events(Request $request)
    {
        $parent = $request->user();
        $children = $this->service->childrenOf($parent);
        abort_if($children->isEmpty(), 404, "Aucun enfant rattaché à votre compte.");
        $schoolIds = $children->pluck('school_id')->unique()->values();

        $events = $this->upcomingEventsQuery($schoolIds)->limit(50)->get()->map(fn(Event $e) => $this->formatEvent($e))->values();

        return response()->json(['events' => $events]);
    }

    private function formatAssignmentItem($a, Student $student): array
    {
        $isDone = (bool) ($a->submission?->score !== null || $a->submission !== null);

        $badgeText = 'À rendre';
        $badgeColorHex = 'EEF2FF';
        $badgeTextColorHex = '2646A6';

        if ($isDone) {
            $badgeText = $a->submission?->score !== null ? 'Noté : ' . $a->submission->score . '/20' : 'Rendu';
            $badgeColorHex = 'DCFCE7';
            $badgeTextColorHex = '15803D';
        } elseif ($a->scheduled_at) {
            if ($a->scheduled_at->isPast() && !$a->scheduled_at->isToday()) {
                $badgeText = 'Passé';
                $badgeColorHex = 'FEE2E2';
                $badgeTextColorHex = 'DC2626';
            } elseif ($a->scheduled_at->isToday()) {
                $badgeText = "Aujourd'hui";
                $badgeColorHex = 'FEF3C7';
                $badgeTextColorHex = 'D97706';
            } elseif ($a->scheduled_at->isTomorrow()) {
                $badgeText = 'Demain';
                $badgeColorHex = 'FFE4E6';
                $badgeTextColorHex = 'E11D48';
            } else {
                // Whole calendar days between today and the due date — not a precise time
                // diff, which under Carbon 3 (diffInDays() now returns an unrounded float)
                // would show something like "Dans 1.9748 jours" instead of "Dans 2 jours".
                $days = (int) now()->startOfDay()->diffInDays($a->scheduled_at->copy()->startOfDay());
                if ($days > 1 && $days <= 7) {
                    $badgeText = "Dans {$days} jours";
                } elseif ($days > 7 && $days <= 14) {
                    $badgeText = 'Dans 1 sem.';
                } else {
                    $badgeText = 'Pour le ' . $a->scheduled_at->translatedFormat('d M');
                }
                $badgeColorHex = 'EEF2FF';
                $badgeTextColorHex = '2646A6';
            }
        }

        $timeStr = 'À rendre prochainement';
        if ($a->scheduled_at) {
            if ($a->scheduled_at->isToday()) {
                $timeStr = "Aujourd'hui à " . $a->scheduled_at->format('H:i');
            } elseif ($a->scheduled_at->isTomorrow()) {
                $timeStr = "Demain à " . $a->scheduled_at->format('H:i');
            } else {
                $timeStr = 'Remise: ' . $a->scheduled_at->translatedFormat('d M') . ' à ' . $a->scheduled_at->format('H:i');
            }
        }

        return [
            'id' => (string) $a->id,
            'studentId' => (string) $student->id,
            'studentName' => $student->first_name,
            'studentFullName' => "{$student->first_name} {$student->last_name}",
            'studentAvatarUrl' => $student->photo_path ? asset('storage/' . $student->photo_path) : '',
            'studentClassName' => $student->academicClass?->name ?? '',
            'subject' => $a->subject->name ?? 'Matière',
            'description' => $a->title,
            'time' => $timeStr,
            'dueDate' => $a->scheduled_at ? $a->scheduled_at->translatedFormat('d M Y') : '',
            'badgeText' => $badgeText,
            'badgeColorHex' => $badgeColorHex,
            'badgeTextColorHex' => $badgeTextColorHex,
            'isDone' => $isDone,
        ];
    }

    /** Full upcoming and recent assignments list — the "Voir tout" destination for home()'s Devoirs à venir. */
    public function assignments(Request $request)
    {
        $parent = $request->user();
        $children = $this->service->childrenOf($parent);
        abort_if($children->isEmpty(), 404, "Aucun enfant rattaché à votre compte.");

        if ($request->filled('student_id')) {
            $studentId = (int) $request->query('student_id');
            $children = $children->filter(fn($c) => $c->id === $studentId);
        }

        $allAssignments = $children
            ->flatMap(function (Student $student) {
                $hw = $this->service->homework($student);
                return collect($hw['assignments'] ?? [])->map(fn($a) => $this->formatAssignmentItem($a, $student));
            })
            ->sortBy(fn($a) => $a['dueDate'])
            ->values();

        return response()->json(['assignments' => $allAssignments]);
    }

    public function home(Request $request)
    {
        $parent = $request->user();
        $overview = $this->service->overview($parent);
        $children = $overview['children'];

        // A brand-new parent legitimately has zero children until they add one —
        // this must render the normal (empty) home screen with its "Ajouter" entry
        // point, not a hard error that strands them with no way to add a child.
        $students = $children->map(fn(Student $c) => [
            'id' => (string) $c->id,
            'name' => $c->first_name,
            'avatarUrl' => $c->photo_path ? asset('storage/' . $c->photo_path) : '',
        ])->values();

        $averages = $children->pluck('average')->filter(fn($a) => $a !== null);
        $averageScore = $averages->isNotEmpty() ? round($averages->avg(), 1) : 0.0;

        $rates = $children->pluck('attendanceRate')->filter(fn($a) => $a !== null);
        $attendancePercentage = $rates->isNotEmpty() ? (int) round($rates->avg()) : 0;

        $priority = ['late' => 0, 'partial' => 1, 'pending' => 2, 'unconfigured' => 3, 'paid' => 4];
        $labels = ['late' => 'En retard', 'partial' => 'Partiel', 'pending' => 'En attente', 'unconfigured' => 'Non configuré', 'paid' => 'À jour'];
        $worst = $children->pluck('feeStatus')->sortBy(fn($s) => $priority[$s] ?? 99)->first();
        $schoolingStatus = $labels[$worst] ?? '—';

        $nextDue = null;
        foreach ($children as $c) {
            $due = $this->service->fees($c)['nextDueDate'] ?? null;
            if ($due && (!$nextDue || $due->lt($nextDue))) {
                $nextDue = $due;
            }
        }

        $upcomingAssignments = collect($overview['upcoming'])->take(5)->map(function ($a) use ($children) {
            $student = $children->firstWhere('id', $a->studentId) ?? new Student([
                'id' => $a->studentId,
                'first_name' => $a->studentFirstName ?? 'Élève',
                'last_name' => $a->studentLastName ?? '',
                'photo_path' => $a->studentPhotoPath ?? '',
            ]);
            return $this->formatAssignmentItem($a, $student);
        })->values();

        $individualTracking = $children->map(fn(Student $c) => [
            'id' => (string) $c->id,
            'studentId' => (string) $c->id,
            'score' => $c->average !== null ? number_format($c->average, 1) . '/20' : '—',
            'description' => $c->average !== null ? 'Moyenne actuelle' : 'Aucune moyenne publiée',
            'isExcellent' => $c->average !== null && $c->average >= 14,
        ])->values();

        $schoolIds = $children->pluck('school_id')->unique()->values();
        $upcomingEvents = $this->upcomingEventsQuery($schoolIds)->limit(3)->get()->map(fn(Event $e) => $this->formatEvent($e))->values();

        return response()->json([
            'parentName' => $parent->name,
            'parentAvatarUrl' => '',
            'students' => $students,
            'averageScore' => $averageScore,
            'attendancePercentage' => $attendancePercentage,
            'schoolingStatus' => $schoolingStatus,
            'nextPaymentDate' => $nextDue ? $nextDue->translatedFormat('d M') : '—',
            'upcomingAssignments' => $upcomingAssignments,
            'individualTracking' => $individualTracking,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    public function attendance(Request $request)
    {
        $student = $this->resolveStudent($request);
        $data = $this->service->attendance($student, 60);

        $calendarDays = collect($data['records'])->map(fn($r) => [
            'date' => (string) $r->date->day,
            'status' => $r->status === 'present' ? 'present' : ($r->status === 'late' ? 'late' : 'absent'),
        ])->values();

        $recentHistory = collect($data['records'])->take(10)->map(fn($r) => [
            'course' => 'Journée',
            'dateInfo' => $r->date->translatedFormat('d M Y'),
            'status' => $r->status === 'present' ? 'PRÉSENT' : ($r->status === 'late' ? 'RETARD' : 'ABSENT'),
        ])->values();

        return response()->json([
            'studentName' => "{$student->first_name} {$student->last_name}",
            'studentAvatarUrl' => $student->photo_path ? asset('storage/' . $student->photo_path) : '',
            'presencePercentage' => (float) ($data['attendanceRate'] ?? 0),
            'absencesCount' => $data['unjustifiedAbsences'],
            'latesCount' => $data['lateCount'],
            'currentMonthIndex' => (int) now()->format('n') - 1,
            'currentYear' => (int) now()->format('Y'),
            'calendarDays' => $calendarDays,
            'recentHistory' => $recentHistory,
        ]);
    }

    /**
     * `?week_offset=` (default 0) moves a whole week at a time — real dates,
     * so parents can browse past/future weeks, not just "today". `?day=`
     * picks which weekday's slots to show within that week (defaults to
     * today if browsing the current week, else Monday). The underlying
     * Timetable is a fixed weekly-recurring template (no per-week variation
     * in this schema), so a given weekday's real courses are honestly
     * identical every week — browsing weeks changes the real dates shown,
     * not invented course content.
     */
    public function schedule(Request $request)
    {
        $student = $this->resolveStudent($request);

        $semesters = Semester::where('school_id', $student->school_id)
            ->orderBy('start_date')
            ->get();

        $semestersFormatted = $semesters->map(function (Semester $s) {
            $start = $s->start_date ? Carbon::parse($s->start_date) : now();
            $end = $s->end_date ? Carbon::parse($s->end_date) : $start->copy()->addMonths(4);
            $months = [];
            $currentMonth = $start->copy()->startOfMonth();
            $endMonth = $end->copy()->startOfMonth();
            while ($currentMonth->lte($endMonth)) {
                $months[] = [
                    'label' => ucfirst($currentMonth->translatedFormat('F Y')),
                    'shortLabel' => ucfirst($currentMonth->translatedFormat('M')),
                    'value' => $currentMonth->format('Y-m'),
                    'year' => $currentMonth->year,
                    'month' => $currentMonth->month,
                ];
                $currentMonth->addMonth();
            }
            return [
                'id' => (string) $s->id,
                'name' => $s->name,
                'startDate' => $s->start_date ? Carbon::parse($s->start_date)->format('Y-m-d') : null,
                'endDate' => $s->end_date ? Carbon::parse($s->end_date)->format('Y-m-d') : null,
                'isCurrent' => (bool) $s->is_current,
                'months' => $months,
            ];
        })->values();

        $requestedSemesterId = $request->query('semester_id');
        $selectedSemester = ($requestedSemesterId ? $semesters->firstWhere('id', $requestedSemesterId) : null)
            ?? $semesters->firstWhere('is_current', true)
            ?? $semesters->first();

        $selectedSemesterData = $selectedSemester
            ? $semestersFormatted->firstWhere('id', (string) $selectedSemester->id)
            : null;
        $availableMonths = $selectedSemesterData['months'] ?? [];

        $requestedMonth = $request->query('month');
        $selectedMonth = null;
        if ($requestedMonth && collect($availableMonths)->contains('value', $requestedMonth)) {
            $selectedMonth = $requestedMonth;
        } else {
            $nowMonth = now()->format('Y-m');
            if (collect($availableMonths)->contains('value', $nowMonth)) {
                $selectedMonth = $nowMonth;
            } else {
                $selectedMonth = !empty($availableMonths) ? $availableMonths[0]['value'] : $nowMonth;
            }
        }

        $dayNames = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
        $weekOffset = (int) $request->query('week_offset', 0);
        $targetDateStr = $request->query('date');

        if ($targetDateStr) {
            $baseDate = Carbon::parse($targetDateStr);
        } elseif ($request->has('month') || $selectedMonth) {
            $monthCarbon = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
            if ($selectedMonth === now()->format('Y-m') && $weekOffset === 0 && !$request->has('week_offset')) {
                // Current month with no explicit offset → anchor on today
                $baseDate = now();
            } else {
                // If the 1st of the month falls on a weekend, start on the following Monday.
                // Otherwise start on the Monday of the school week containing the 1st.
                if ($monthCarbon->isWeekend()) {
                    $firstMonday = $monthCarbon->copy()->next(Carbon::MONDAY);
                } else {
                    $firstMonday = $monthCarbon->copy()->startOfWeek();
                }
                $baseDate = $firstMonday->copy()->addWeeks($weekOffset);
            }
        } else {
            $baseDate = now()->addWeeks($weekOffset);
        }

        $startOfWeek = $baseDate->startOfWeek();

        $weekDays = collect($dayNames)->map(fn($name, $i) => [
            'dayName' => ucfirst(substr($name, 0, 3)),
            'date' => $startOfWeek->copy()->addDays($i)->day,
        ])->values();

        // Load timetable slots for this semester and week.
        // Respects valid_from versioning (valid_from <= end of displayed school week),
        // and falls back to semester-less / generic entries when needed.
        $targetWeekDate = $startOfWeek->copy()->addDays(4)->format('Y-m-d');

        $rawSlots = $student->academic_class_id
            ? Timetable::where('academic_class_id', $student->academic_class_id)
                ->where('status', 'published')
                ->where(function ($q) use ($selectedSemester) {
                    if ($selectedSemester) {
                        $q->where('semester_id', $selectedSemester->id)
                            ->orWhereNull('semester_id');
                    } else {
                        $q->whereNull('semester_id');
                    }
                })
                ->where(function ($q) use ($targetWeekDate) {
                    $q->whereNull('valid_from')
                        ->orWhere('valid_from', '<=', $targetWeekDate);
                })
                ->with(['subject', 'teacher', 'room'])
                ->orderByDesc('valid_from')
                ->orderByDesc('id')
                ->get()
            : collect();

        if ($selectedSemester && $rawSlots->contains(fn($t) => $t->semester_id == $selectedSemester->id)) {
            $rawSlots = $rawSlots->filter(fn($t) => $t->semester_id == $selectedSemester->id);
        }

        // Deduplicate: pick the newest valid_from per (day_of_week, start_time)
        $slots = $rawSlots->unique(fn($t) => $t->day_of_week . '|' . $t->start_time)->values();


        $todayName = $dayNames[now()->dayOfWeekIso - 1] ?? null;
        $requestedDay = $request->query('day');
        $selectedDayName = in_array($requestedDay, $dayNames, true)
            ? $requestedDay
            : ($weekOffset === 0 && $startOfWeek->isCurrentWeek() && $todayName ? $todayName : 'lundi');
        $selectedDayIndex = array_search($selectedDayName, $dayNames, true);
        if ($selectedDayIndex === false)
            $selectedDayIndex = 0;

        $weekEvents = $slots->where('day_of_week', $selectedDayName)
            ->sortBy('start_time')
            ->values()
            ->map(function (Timetable $slot) {
                $roomName = $slot->room ? trim($slot->room->name) : '';
                $tag = '';
                if ($roomName !== '') {
                    $tag = preg_match('/^salle/i', $roomName) ? $roomName : 'Salle ' . $roomName;
                }
                return [
                    'time' => Carbon::parse($slot->start_time)->format('H:i'),
                    'subject' => $slot->subject->name ?? '—',
                    'teacher' => $slot->teacher ? "{$slot->teacher->first_name} {$slot->teacher->last_name}" : '—',
                    'tag' => $tag,
                    'colorHex' => '0xFF0F3294',
                ];
            })->values();

        $monthEvents = Event::where('school_id', $student->school_id)
            ->where('status', '!=', 'cancelled')
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->limit(5)
            ->get()
            ->map(fn(Event $e) => [
                'time' => $e->start_at->translatedFormat('d M'),
                'subject' => $e->title,
                'teacher' => $e->organizer_name ?? '',
                'tag' => $e->start_at->format('H:i'),
                'colorHex' => '0xFF0F3294',
            ])->values();

        $homework = $this->service->homework($student);

        return response()->json([
            'studentName' => "{$student->first_name} {$student->last_name}",
            'studentAvatarUrl' => $student->photo_path ? asset('storage/' . $student->photo_path) : '',
            'semesters' => $semestersFormatted,
            'selectedSemesterId' => $selectedSemester ? (string) $selectedSemester->id : null,
            'selectedMonth' => $selectedMonth,
            'weekLabel' => $startOfWeek->translatedFormat('d M') . ' – ' . $startOfWeek->copy()->addDays(4)->translatedFormat('d M Y'),
            'selectedDate' => $startOfWeek->copy()->addDays($selectedDayIndex)->day,
            'weekDays' => $weekDays,
            'weekEvents' => $weekEvents,
            'monthEvents' => $monthEvents,
            'aiInsight' => '',
            'summaries' => [
                ['title' => 'COURS', 'value' => (string) $weekEvents->count(), 'subtitle' => 'Cours ce jour'],
                ['title' => 'DEVOIRS', 'value' => (string) count($homework['upcoming']), 'subtitle' => "À rendre"],
            ],
            'hasAbsenceReport' => false,
            'absenceReportDetails' => null,
        ]);
    }

    public function coursesOverview(Request $request)
    {
        $student = $this->resolveStudent($request);
        $bulletin = $this->service->bulletin($student);

        $evolution = '';
        if ($bulletin['currentSemester'] && $bulletin['average'] !== null) {
            $previous = $this->bulletinStats->previousSemester($student->school_id, $bulletin['currentSemester']);
            $previousAverage = $previous ? $this->bulletinStats->studentAverageForSemester($student, $previous) : null;
            if ($previousAverage !== null) {
                $delta = $bulletin['average'] - $previousAverage;
                $evolution = sprintf('%+.1f pts', $delta);
            }
        }

        $currentSemesterId = $bulletin['currentSemester']?->id;
        $syllabuses = ($student->academic_class_id && $currentSemesterId)
            ? Syllabus::where('academic_class_id', $student->academic_class_id)
                ->where('semester_id', $currentSemesterId)
                ->with('lessons')
                ->get()
                ->keyBy('subject_id')
            : collect();

        // Retrieve all subjects taught in the student's class (or all subjects in the school)
        $classSubjectIds = collect();
        if ($student->academic_class_id) {
            $classSubjectIds = Syllabus::where('academic_class_id', $student->academic_class_id)->pluck('subject_id')
                ->merge(Timetable::where('academic_class_id', $student->academic_class_id)->pluck('subject_id'))
                ->unique();
        }

        $allSubjects = $classSubjectIds->isNotEmpty()
            ? Subject::where('school_id', $student->school_id)->whereIn('id', $classSubjectIds)->orderBy('name')->get()
            : Subject::where('school_id', $student->school_id)->orderBy('name')->get();

        $gradesBySubject = collect($bulletin['grades'])->keyBy(fn($g) => $g->subject->id);

        $palette = [
            ['colorHex' => '0xFFEEF2FF', 'iconColorHex' => '0xFF0F3294', 'iconType' => 'functions'],
            ['colorHex' => '0xFFECFDF5', 'iconColorHex' => '0xFF059669', 'iconType' => 'menu_book'],
            ['colorHex' => '0xFFFFFBEB', 'iconColorHex' => '0xFFD97706', 'iconType' => 'science'],
            ['colorHex' => '0xFFFDF2F8', 'iconColorHex' => '0xFFDB2777', 'iconType' => 'public'],
            ['colorHex' => '0xFFF3E8FF', 'iconColorHex' => '0xFF7C3AED', 'iconType' => 'history_edu'],
            ['colorHex' => '0xFFE0F2FE', 'iconColorHex' => '0xFF0284C7', 'iconType' => 'computer'],
        ];

        $subjects = $allSubjects->map(function (Subject $subject, int $idx) use ($syllabuses, $gradesBySubject, $palette) {
            $syllabus = $syllabuses->get($subject->id);
            $chaptersText = '—';
            if ($syllabus && $syllabus->lessons->isNotEmpty()) {
                $completed = $syllabus->lessons->where('progress_status', 'completed')->count();
                $total = $syllabus->lessons->count();
                $chaptersText = "$completed/$total chapitres";
            }

            $grade = $gradesBySubject->get($subject->id);
            $scoreText = $grade && $grade->score !== null ? number_format($grade->score, 1) : '—';

            $style = $palette[$idx % count($palette)];

            return [
                'id' => (string) $subject->id,
                'title' => $subject->name,
                'chapters' => $chaptersText,
                'score' => $scoreText,
                'iconType' => $style['iconType'],
                'colorHex' => $style['colorHex'],
                'iconColorHex' => $style['iconColorHex'],
            ];
        })->values();

        return response()->json([
            'generalAverage' => $bulletin['average'] ?? 0.0,
            'averageEvolution' => $evolution,
            'subjects' => $subjects,
            'resourceImageUrl' => '',
            'resourceTitle' => '',
            'resourceSubtitle' => '',
        ]);
    }

    /**
     * Real tuition summary for the "Scolarité" tab — delegates to the same
     * StudentFeeService the School Dashboard's Finance module uses, so the
     * total/paid/remaining/schedule a parent sees is exactly what the school
     * office would see, not a separately-maintained mobile-only figure.
     */
    public function fees(Request $request, CanteenEnrollmentService $canteenEnrollmentService, TransportEnrollmentService $transportEnrollmentService)
    {
        $type = $request->query('type', 'tuition');
        abort_unless(in_array($type, array_keys(FeeLevel::TYPES), true), 422, 'Type de frais invalide.');

        $student = $this->resolveStudent($request);

        // Cantine/transport tariffs are school-wide, not tied to a specific student —
        // without this check, a student who never enrolled would still see a full
        // billing schedule for a service they don't actually use.
        if (in_array($type, ['cantine', 'transport'], true)) {
            $notEnrolledResponse = $this->feesNotEnrolledResponse($student, $type, $canteenEnrollmentService, $transportEnrollmentService);
            if ($notEnrolledResponse) {
                return $notEnrolledResponse;
            }
        }

        $summary = $this->service->fees($student, $type);

        $lineStatusLabel = fn(string $status) => match ($status) {
            'paid' => 'PAYÉ',
            'due' => 'EN ATTENTE',
            'upcoming' => 'À VENIR',
            default => strtoupper($status),
        };

        $payments = Payment::where('student_id', $student->id)
            ->where('type', $type)
            ->orderByDesc('paid_at')
            ->limit(20)
            ->get();

        return response()->json([
            'academicYear' => $student->academic_year ?? '—',
            'total' => (float) $summary['total'],
            'paid' => (float) $summary['paid'],
            'remaining' => (float) $summary['remaining'],
            // 'unconfigured' means the school hasn't set up a fee structure
            // for this student's level/year yet — shown as an honest empty
            // state rather than invented amounts.
            'status' => $summary['status'],
            'schedule' => collect($summary['schedule'])->map(fn(array $line) => [
                'label' => $line['label'],
                'amount' => (float) $line['amount'],
                'dueDateLabel' => $line['due_date']?->translatedFormat('d M Y') ?? '—',
                'status' => $line['status'],
                'statusLabel' => $lineStatusLabel($line['status']),
            ])->values(),
            'payments' => $payments->map(fn(Payment $payment) => [
                'id' => (string) $payment->id,
                'methodLabel' => Payment::METHODS[$payment->method] ?? $payment->method,
                'amount' => (float) $payment->amount,
                'dateLabel' => $payment->paid_at?->translatedFormat('d M Y') ?? '—',
                'reference' => $payment->reference ?? '',
            ])->values(),
        ]);
    }

    /**
     * Null when the student is actually enrolled (the normal fee summary should be
     * shown). Otherwise a ready-to-return JSON response with an honest empty state
     * and a message telling the family what to do — no invented totals for a
     * service this student was never signed up for.
     */
    private function feesNotEnrolledResponse(Student $student, string $type, CanteenEnrollmentService $canteenEnrollmentService, TransportEnrollmentService $transportEnrollmentService)
    {
        if ($type === 'cantine') {
            if ($canteenEnrollmentService->isEnrolled($student->id)) {
                return null;
            }
            $enrollmentStatus = $this->canteenEnrollmentStatus($student, $canteenEnrollmentService)['status'];
            $serviceLabel = 'la cantine';
        } else {
            if ($transportEnrollmentService->isEnrolled($student->id, 'morning') || $transportEnrollmentService->isEnrolled($student->id, 'evening')) {
                return null;
            }
            $transportStatuses = $this->transportEnrollmentStatus($student, $transportEnrollmentService);
            $enrollmentStatus = $transportStatuses['morning']['status'] !== 'none'
                ? $transportStatuses['morning']['status']
                : $transportStatuses['evening']['status'];
            $serviceLabel = 'le transport scolaire';
        }

        $message = match ($enrollmentStatus) {
            'pending' => "Votre demande d'inscription à {$serviceLabel} est en attente de validation par l'établissement.",
            'rejected' => "Votre demande d'inscription à {$serviceLabel} a été refusée. Contactez l'établissement pour plus d'informations.",
            default => "Cet élève n'est pas encore inscrit à {$serviceLabel}. Inscrivez-vous pour voir les frais et modalités de paiement.",
        };

        return response()->json([
            'academicYear' => $student->academic_year ?? '—',
            'total' => 0.0,
            'paid' => 0.0,
            'remaining' => 0.0,
            'status' => 'not_enrolled',
            'enrollmentStatus' => $enrollmentStatus,
            'enrollmentMessage' => $message,
            'schedule' => [],
            'payments' => [],
        ]);
    }

    /** {status: none|pending|approved|rejected, rejectionReason} — mirrors transportEnrollmentStatus() for the canteen. */
    private function canteenEnrollmentStatus(Student $student, CanteenEnrollmentService $enrollmentService): array
    {
        $latest = $enrollmentService->latestRequestFor($student->id);
        if (!$latest) {
            return ['status' => 'none', 'rejectionReason' => null];
        }

        return ['status' => $latest->status, 'rejectionReason' => $latest->rejection_reason];
    }

    public function canteen(Request $request, CanteenEnrollmentService $enrollmentService)
    {
        $student = $this->resolveStudent($request);
        $data = $this->service->canteen($student);

        // Same week the service just queried the menu for — once Friday has
        // gone by, that's next week, not "this" calendar week.
        $weekStart = MenuItem::currentWeekStart();

        $reservedItemIds = CanteenReservation::where('student_id', $student->id)
            ->whereBetween('date', [$weekStart->toDateString(), $weekStart->copy()->addDays(4)->toDateString()])
            ->pluck('menu_item_id')
            ->all();

        $byDate = collect($data['weekMenu'])->groupBy(fn($item) => $item->date->toDateString());
        $mealOption = fn($item) => [
            'id' => (string) $item->id,
            'name' => $item->title,
            'description' => $item->description ?? '',
            'imageUrl' => '',
            'calories' => 0,
            'tags' => $item->tags ?? [],
            'allergens' => $item->allergens ?? [],
            'isRecommended' => false,
            'warningText' => null,
            'isReserved' => in_array($item->id, $reservedItemIds, true),
        ];

        $weeklyMenu = collect(range(0, 4))->map(function ($i) use ($byDate, $mealOption, $weekStart) {
            $date = $weekStart->copy()->addDays($i);
            $dayItems = $byDate->get($date->toDateString(), collect());
            $isPast = $date->isBefore(now()->startOfDay());

            // Ordering for this day only opens the evening before, 18h00 to
            // 20h00 — e.g. Friday's meals can only be chosen/changed on
            // Thursday between 18h00 and 20h00, not any other evening.
            $windowStart = $date->copy()->subDay()->setTime(18, 0);
            $windowEnd = $date->copy()->subDay()->setTime(20, 0);
            $isOrderingAllowed = !$isPast && now()->between($windowStart, $windowEnd);
            $orderingWindowMessage = match (true) {
                $isOrderingAllowed => "Commandes et modifications ouvertes jusqu'à 20h00",
                $isPast => "Ce jour est déjà passé.",
                now()->isBefore($windowStart) => "Ouvre le " . $windowStart->translatedFormat('l') . " à 18h00",
                default => "Fermé — les choix pour ce jour sont désormais définitifs",
            };

            return [
                'dayName' => strtoupper($date->translatedFormat('D')),
                'date' => (string) $date->day,
                'fullDate' => $date->toDateString(),
                // The parent can only order for today or a future day — a
                // day that's already happened can't have its meal changed,
                // so the app disables ordering for it (still viewable).
                'isPast' => $isPast,
                'isOrderingAllowed' => $isOrderingAllowed,
                'orderingWindowMessage' => $orderingWindowMessage,
                'aiAdvice' => '',
                // Real slot values used by the School Dashboard's menu planner
                // (SchoolDashboard::canteen.planning) are breakfast/starter/main/
                // dessert — not lunch/snack, which never occur in real data.
                'breakfasts' => $dayItems->where('slot', 'breakfast')->map($mealOption)->values(),
                'starters' => $dayItems->where('slot', 'starter')->map($mealOption)->values(),
                'mains' => $dayItems->where('slot', 'main')->map($mealOption)->values(),
                'desserts' => $dayItems->where('slot', 'dessert')->map($mealOption)->values(),
            ];
        });

        // Top-level flag kept for backward compatibility (and as a quick
        // "is anything orderable at all right now" signal) — now that the
        // rule is per-day, the app should drive each day's own button off
        // weeklyMenu[i].isOrderingAllowed instead of this.
        $isOrderingAllowed = $weeklyMenu->contains('isOrderingAllowed', true);

        return response()->json([
            'studentName' => "{$student->first_name} {$student->last_name}",
            'studentClass' => $student->academicClass->name ?? '—',
            'studentAvatarUrl' => $student->photo_path ? asset('storage/' . $student->photo_path) : '',
            'isOrderingAllowed' => $isOrderingAllowed,
            'orderingWindowMessage' => $isOrderingAllowed
                ? "Commandes et modifications ouvertes jusqu'à 20h00"
                : "Les choix et modifications de repas ne sont ouverts que la veille, entre 18h00 et 20h00.",
            'orderingWindowTime' => "18h00 - 20h00",
            'weeklyMenu' => $weeklyMenu,
            'enrollmentStatus' => $this->canteenEnrollmentStatus($student, $enrollmentService),
        ]);
    }

    /** Creates a pending canteen enrollment request for the resolved student — a school staff member must approve it before meals can be reserved or scanned. */
    public function requestCanteenEnrollment(Request $request, CanteenEnrollmentService $enrollmentService)
    {
        $student = $this->resolveStudent($request);

        $pending = $enrollmentService->latestRequestFor($student->id);
        abort_if(
            $pending && $pending->status === CanteenEnrollmentRequest::STATUS_PENDING,
            422,
            "Une demande est déjà en attente."
        );

        $enrollmentService->requestEnrollment($student, $request->user());

        return response()->json([
            'enrollmentStatus' => ['status' => 'pending', 'rejectionReason' => null],
            'message' => "Votre demande d'inscription à la cantine a été envoyée à l'école pour validation.",
        ]);
    }

    /**
     * Confirms the parent's meal choices for one or more menu items — one
     * reservation per (student, date, slot), so re-confirming a day with a
     * different breakfast/lunch choice replaces the previous one rather than
     * stacking duplicates. This is what makes the canteen "aware" of the
     * order: it's a real row the School Dashboard's reservations page reads.
     */
    public function confirmCanteenOrder(Request $request, CanteenEnrollmentService $enrollmentService)
    {
        $request->validate([
            'menu_item_ids' => ['required', 'array', 'min:1'],
            'menu_item_ids.*' => ['integer', 'exists:canteen_menu_items,id'],
        ]);

        $student = $this->resolveStudent($request);

        abort_unless(
            $enrollmentService->isEnrolled($student->id),
            422,
            "Cet élève n'a pas d'inscription cantine valide. Faites une demande d'inscription."
        );

        $items = MenuItem::where('school_id', $student->school_id)
            ->whereIn('id', $request->input('menu_item_ids'))
            ->get();

        // Ordering for a given day only opens the evening before, 18h00 to
        // 20h00 — e.g. Friday's meals can only be chosen or changed on
        // Thursday between 18h00 and 20h00, not any other evening. Checked
        // per menu item's own date (defense in depth: the app disables this
        // client-side, but the API must not trust that) rather than a flat
        // "is it currently 18h-20h" window, since that alone would let a
        // parent order for *any* future day in the published week during
        // any evening, not just the very next one.
        foreach ($items as $item) {
            abort_if(
                $item->date->isBefore(now()->startOfDay()),
                422,
                "Impossible de commander pour un jour déjà passé."
            );

            $windowStart = $item->date->copy()->subDay()->setTime(18, 0);
            $windowEnd = $item->date->copy()->subDay()->setTime(20, 0);
            abort_unless(
                now()->between($windowStart, $windowEnd),
                422,
                "Les choix pour le " . $item->date->translatedFormat('l d F') . " ne sont ouverts que le "
                    . $windowStart->translatedFormat('l') . " entre 18h00 et 20h00."
            );
        }

        foreach ($items as $item) {
            CanteenReservation::updateOrCreate(
                ['student_id' => $student->id, 'date' => $item->date, 'slot' => $item->slot],
                ['school_id' => $student->school_id, 'menu_item_id' => $item->id]
            );
        }

        return response()->json([
            'confirmed' => true,
            'reservedMenuItemIds' => $items->pluck('id')->map(fn($id) => (string) $id)->values(),
        ]);
    }

    /**
     * Day-by-day canteen history — merges two real, independent signals:
     * what the parent reserved (`CanteenReservation`) and whether the child
     * actually ate there, confirmed by the school scanning their badge before
     * the meal (`MealRecord`, created by the School Dashboard's "Pointer un
     * repas" action). A day can have one without the other — a reservation
     * with no matching meal record honestly reads as "not attended", and a
     * meal record with no reservation (a walk-in) still shows up, just
     * without a dish name.
     */
    public function canteenHistory(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $student = $this->resolveStudent($request);

        $from = $request->filled('from') ? $request->query('from') : now()->subMonths(3)->toDateString();
        $to = $request->filled('to') ? $request->query('to') : now()->toDateString();

        $reservationsByDate = CanteenReservation::where('student_id', $student->id)
            ->whereBetween('date', [$from, $to])
            ->with('menuItem')
            ->get()
            ->groupBy(fn(CanteenReservation $r) => $r->date->toDateString());

        $account = CanteenAccount::where('holder_type', 'student')->where('holder_id', $student->id)->first();
        $attendedDates = $account
            ? $account->mealRecords()->whereBetween('date', [$from, $to])->pluck('date')->map(fn($d) => $d->toDateString())->all()
            : [];

        $allDates = $reservationsByDate->keys()->merge($attendedDates)->unique()->sortDesc()->values();

        $page = max(1, $request->integer('page', 1));
        $perPage = 10;
        $pageDates = $allDates->slice(($page - 1) * $perPage, $perPage + 1);
        $hasMore = $pageDates->count() > $perPage;
        $pageDates = $pageDates->take($perPage);

        $dateLabel = function (Carbon $date) {
            if ($date->isToday()) {
                return "Aujourd'hui";
            }
            if ($date->isYesterday()) {
                return 'Hier';
            }
            return $date->translatedFormat('d M Y');
        };

        $history = $pageDates->map(function (string $dateString) use ($reservationsByDate, $attendedDates, $dateLabel) {
            $date = Carbon::parse($dateString);
            $reservations = $reservationsByDate->get($dateString, collect());
            $attended = in_array($dateString, $attendedDates, true);

            if ($attended) {
                $status = 'attended';
                $statusLabel = 'Passé à la cantine';
            } elseif ($date->isFuture() || $date->isToday()) {
                $status = 'upcoming';
                $statusLabel = $date->isToday() ? "Repas prévu aujourd'hui" : 'Repas prévu';
            } else {
                $status = 'absent';
                $statusLabel = 'Non passé à la cantine';
            }

            return [
                'id' => $dateString,
                'date' => $dateString,
                'dateLabel' => $dateLabel($date),
                'items' => $reservations->map(fn(CanteenReservation $r) => [
                    'slot' => CanteenReservation::SLOT_LABELS[$r->slot] ?? $r->slot,
                    'title' => $r->menuItem->title ?? '—',
                ])->values(),
                'status' => $status,
                'statusLabel' => $statusLabel,
            ];
        })->values();

        return response()->json([
            'history' => $history,
            'hasMore' => $hasMore,
        ]);
    }

    /**
     * `visits` are real `Intervention` rows — `indication` maps to `care_notes`,
     * `treatment` to the human label of the real `decision` field. `vaccines` and
     * `allergies` are real, parent-addable rows (see storeVaccine/storeAllergy
     * below), merged for allergies with the legacy free-text `Student::allergies`
     * field set by staff at enrollment (kept read-only here — editing it stays a
     * School Dashboard action). `prescriptions` are real uploaded documents (see
     * storePrescription) — the mobile UI treats them as files, not structured data.
     */
    public function infirmary(Request $request)
    {
        $student = $this->resolveStudent($request);

        $interventions = Intervention::where('student_id', $student->id)
            ->orderByDesc('arrival_time')
            ->limit(20)
            ->get();

        $decisionLabel = fn(?string $decision) => $decision ? (Intervention::DECISIONS[$decision] ?? $decision) : null;

        $visitTime = function (Carbon $date) {
            if ($date->isToday()) {
                return "Aujourd'hui";
            }
            if ($date->isYesterday()) {
                return 'Hier';
            }
            return $date->translatedFormat('d M Y');
        };

        $visits = $interventions->map(fn(Intervention $i) => [
            'id' => (string) $i->id,
            'type' => $i->motive ?? 'Consultation',
            'date' => $i->arrival_time ? $i->arrival_time->translatedFormat('l d M Y') : '',
            'status' => $i->decision ? 'Terminé' : '',
            'indication' => $i->care_notes ?? '',
            'treatment' => $decisionLabel($i->decision),
        ])->values();

        $last = $interventions->first();
        $lastVisit = [
            'title' => $last ? ($last->motive ?? 'Consultation') . ($last->temperature ? ' (' . $last->temperature . '°C)' : '') : '',
            'description' => $last?->care_notes ?? '',
            'timeText' => $last?->arrival_time ? $visitTime($last->arrival_time) : '',
        ];

        $allergiesText = trim((string) ($student->allergies ?? ''));
        $legacyAllergies = $allergiesText === '' ? collect() : collect(explode(',', $allergiesText))
            ->map(fn($name) => trim($name))
            ->filter()
            ->values()
            ->map(fn($name, $index) => [
                'id' => 'al' . $index,
                'name' => $name,
                'severity' => '',
                'notes' => null,
            ]);

        $recordedAllergies = InfirmaryAllergy::where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn(InfirmaryAllergy $a) => $this->formatAllergy($a));

        $vaccines = Vaccine::where('student_id', $student->id)
            ->orderByDesc('administered_at')
            ->get()
            ->map(fn(Vaccine $v) => $this->formatVaccine($v));

        $prescriptions = PrescriptionDocument::where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn(PrescriptionDocument $p) => $this->formatPrescriptionDocument($p));

        return response()->json([
            'lastVisit' => $lastVisit,
            'visits' => $visits,
            'prescriptions' => $prescriptions,
            'vaccines' => $vaccines,
            'allergies' => $legacyAllergies->concat($recordedAllergies)->values(),
        ]);
    }

    private function formatVaccine(Vaccine $v): array
    {
        $status = 'À jour';
        if ($v->next_due_at) {
            $status = $v->next_due_at->isPast() ? 'En retard' : 'À jour';
        }

        return [
            'id' => 'v' . $v->id,
            'name' => $v->name,
            'date' => $v->administered_at->translatedFormat('d M Y'),
            'status' => $status,
        ];
    }

    private function formatAllergy(InfirmaryAllergy $a): array
    {
        return [
            'id' => 'a' . $a->id,
            'name' => $a->name,
            'severity' => $a->severity ?? '',
            'notes' => $a->notes,
        ];
    }

    private function formatPrescriptionDocument(PrescriptionDocument $p): array
    {
        return [
            'id' => (string) $p->id,
            'name' => $p->name,
            'size' => $this->humanFileSize($p->size_bytes),
            'type' => strtoupper(pathinfo($p->file_path, PATHINFO_EXTENSION)),
            'url' => asset('storage/' . $p->file_path),
        ];
    }

    private function humanFileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' o';
        }
        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024) . ' Ko';
        }
        return round($bytes / (1024 * 1024), 1) . ' Mo';
    }

    /** Parent adds a real vaccination record for this child (Carnet de vaccination). */
    public function storeVaccine(Request $request)
    {
        $student = $this->resolveStudent($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'administered_at' => ['required', 'date'],
            'next_due_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $vaccine = Vaccine::create([
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'name' => $data['name'],
            'administered_at' => $data['administered_at'],
            'next_due_at' => $data['next_due_at'] ?? null,
            'notes' => $data['notes'] ?? null,
            'added_by_parent_id' => $request->user()->id,
        ]);

        return response()->json($this->formatVaccine($vaccine), 201);
    }

    /** Parent adds a known allergy for this child. */
    public function storeAllergy(Request $request)
    {
        $student = $this->resolveStudent($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'severity' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $allergy = InfirmaryAllergy::create([
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'name' => $data['name'],
            'severity' => $data['severity'] ?? null,
            'notes' => $data['notes'] ?? null,
            'added_by_parent_id' => $request->user()->id,
        ]);

        return response()->json($this->formatAllergy($allergy), 201);
    }

    /** Parent uploads a prescription/medical document (photo or PDF) for this child. */
    public function storePrescription(Request $request)
    {
        $student = $this->resolveStudent($request);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('file');
        $path = $file->store('infirmary/prescriptions', 'public');

        $document = PrescriptionDocument::create([
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'name' => $data['name'] ?: $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'added_by_parent_id' => $request->user()->id,
        ]);

        return response()->json($this->formatPrescriptionDocument($document), 201);
    }

    /**
     * Real library loans (Library module) for this child — currently borrowed
     * books plus the return history, filterable by a borrowed-date range.
     * Loans don't carry a plain student_id (borrower is polymorphic), so this
     * queries the Loan model directly rather than through the School
     * Dashboard's LoanRepositoryInterface, which assumes a staff `auth()->user()`
     * with a school_id — not true for a ParentAccount.
     */
    public function library(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $student = $this->resolveStudent($request);

        $from = $request->filled('from') ? $request->query('from') : now()->subMonths(6)->toDateString();
        $to = $request->filled('to') ? $request->query('to') : now()->toDateString();

        $statusLabel = fn(string $status) => match ($status) {
            'active' => 'En cours',
            'overdue' => 'En retard',
            'returned' => 'Rendu',
            default => ucfirst($status),
        };

        $loans = Loan::where('school_id', $student->school_id)
            ->where('borrower_type', Student::class)
            ->where('borrower_id', $student->id)
            ->whereBetween('borrowed_at', [$from, $to])
            ->with('book')
            ->orderByDesc('borrowed_at')
            ->get()
            ->map(fn(Loan $loan) => [
                'id' => (string) $loan->id,
                'bookTitle' => $loan->book->title ?? '—',
                'bookAuthor' => $loan->book->author ?? '',
                'coverUrl' => $loan->book?->cover_path ? asset('storage/' . $loan->book->cover_path) : '',
                'borrowedDate' => $loan->borrowed_at?->toDateString(),
                'borrowedDateLabel' => $loan->borrowed_at?->translatedFormat('d M Y') ?? '—',
                'dueDateLabel' => $loan->due_at?->translatedFormat('d M Y') ?? '—',
                'returnedDateLabel' => $loan->returned_at?->translatedFormat('d M Y'),
                'status' => $loan->status,
                'statusLabel' => $statusLabel($loan->status),
            ]);

        return response()->json([
            'current' => $loans->whereIn('status', ['active', 'overdue'])->values(),
            'history' => $loans->where('status', 'returned')->values(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    /** Real RFID access-control logs (Presence module) for this child, today only. */
    public function access(Request $request)
    {
        $student = $this->resolveStudent($request);

        $logs = AccessLog::where('school_id', $student->school_id)
            ->where('holder_type', 'student')
            ->where('holder_id', $student->id)
            ->with('accessPoint')
            ->orderBy('occurred_at')
            ->get();

        $todayLogs = $logs->filter(fn(AccessLog $l) => $l->occurred_at->isToday())->values();

        $lastToday = $todayLogs->last();
        $lastOverall = $logs->last();

        $currentStatus = 'outside';
        $lastScanTime = '';

        if ($lastToday) {
            $currentStatus = $lastToday->action === AccessLog::ACTION_ENTRY ? 'inside' : 'outside';
            $lastScanTime = $lastToday->occurred_at->format('H:i');
        } elseif ($lastOverall) {
            $currentStatus = 'outside';
            $lastScanTime = $lastOverall->occurred_at->translatedFormat('d M H:i');
        }

        $minutesInSchool = 0;
        $openEntry = null;
        foreach ($todayLogs as $log) {
            if ($log->action === AccessLog::ACTION_ENTRY) {
                $openEntry = $log->occurred_at;
            } elseif ($log->action === AccessLog::ACTION_EXIT && $openEntry) {
                // Cast: Carbon 3's diffInMinutes() returns an unrounded float, and intdiv()
                // below requires an int — passing it a float throws a TypeError.
                $minutesInSchool += (int) round($openEntry->diffInMinutes($log->occurred_at, true));
                $openEntry = null;
            }
        }
        if ($openEntry && $currentStatus === 'inside') {
            $minutesInSchool += (int) round($openEntry->diffInMinutes(now(), true));
        }

        $timeInSchool = '';
        if ($minutesInSchool > 0) {
            $hours = intdiv($minutesInSchool, 60);
            $minutes = $minutesInSchool % 60;
            $timeInSchool = $hours > 0 ? ($minutes > 0 ? "{$hours}h{$minutes}" : "{$hours}h") : "{$minutes}min";
        }

        return response()->json([
            'studentRollNumber' => $student->roll_number ?? '',
            'currentStatus' => $currentStatus,
            'lastScanTime' => $lastScanTime,
            'todayEntries' => $todayLogs->where('action', AccessLog::ACTION_ENTRY)->count(),
            'todayExits' => $todayLogs->where('action', AccessLog::ACTION_EXIT)->count(),
            'timeInSchool' => $timeInSchool,
            'history' => $todayLogs->sortByDesc('occurred_at')->values()->map(fn(AccessLog $l) => [
                'id' => (string) $l->id,
                'action' => $l->action,
                'locationName' => $l->accessPoint?->name ?? '—',
                'time' => $l->occurred_at->format('H:i'),
                'authorized' => (bool) $l->authorized,
            ]),
        ]);
    }

    /** Full access-log history for this child (not limited to today), optionally filtered by action. */
    public function accessHistory(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $student = $this->resolveStudent($request);

        $query = AccessLog::where('school_id', $student->school_id)
            ->where('holder_type', 'student')
            ->where('holder_id', $student->id)
            ->with('accessPoint')
            ->orderByDesc('occurred_at');

        if (in_array($request->query('action'), [AccessLog::ACTION_ENTRY, AccessLog::ACTION_EXIT], true)) {
            $query->where('action', $request->query('action'));
        }

        if ($request->filled('from')) {
            $query->whereDate('occurred_at', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('occurred_at', '<=', $request->query('to'));
        }

        $page = max(1, $request->integer('page', 1));
        $perPage = 10;
        $logs = $query->skip(($page - 1) * $perPage)->take($perPage + 1)->get();
        $hasMore = $logs->count() > $perPage;
        $logs = $logs->take($perPage);

        $dateLabel = function (Carbon $date) {
            if ($date->isToday()) {
                return "Aujourd'hui";
            }
            if ($date->isYesterday()) {
                return 'Hier';
            }
            return $date->translatedFormat('d M Y');
        };

        return response()->json([
            'history' => $logs->map(fn(AccessLog $l) => [
                'id' => (string) $l->id,
                'action' => $l->action,
                'locationName' => $l->accessPoint?->name ?? '—',
                'time' => $l->occurred_at->format('H:i'),
                'dateLabel' => $dateLabel($l->occurred_at),
                'authorized' => (bool) $l->authorized,
            ]),
            'hasMore' => $hasMore,
        ]);
    }

    /** {status: none|pending|approved|rejected, rejectionReason} per period — none of this existed before enrollment requests were added; a student with no row anywhere is simply "none". */
    private function transportEnrollmentStatus(Student $student, TransportEnrollmentService $enrollmentService): array
    {
        $statusFor = function (string $period) use ($student, $enrollmentService) {
            if ($enrollmentService->isEnrolled($student->id, $period)) {
                return ['status' => 'approved', 'rejectionReason' => null];
            }
            $latest = $enrollmentService->latestRequestFor($student->id, $period);
            if (!$latest) {
                return ['status' => 'none', 'rejectionReason' => null];
            }
            return ['status' => $latest->status, 'rejectionReason' => $latest->rejection_reason];
        };

        return ['morning' => $statusFor('morning'), 'evening' => $statusFor('evening')];
    }

    public function transport(Request $request, TransportEnrollmentService $enrollmentService)
    {
        $student = $this->resolveStudent($request);
        $data = $this->service->transport($student);
        $morningStop = $data['morningStop'];
        $eveningStop = $data['eveningStop'];

        // Used to just 404 here — now that enrollment needs a request/approval
        // step, "no stop yet" is a normal state the app must render (a
        // "demander l'inscription" screen), not an error.
        if (!$morningStop && !$eveningStop) {
            return response()->json([
                'enrolled' => false,
                'enrollmentStatus' => $this->transportEnrollmentStatus($student, $enrollmentService),
            ]);
        }

        $bus = $data['bus'];
        $driver = $bus?->driver;
        $driverName = $driver ? trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? '')) : '—';
        $primaryStop = $morningStop ?? $eveningStop;
        $school = School::find($student->school_id);

        $stops = [];
        if ($morningStop) {
            $stops[] = ['id' => (string) $morningStop->id, 'type' => 'Matin', 'locationName' => $morningStop->name, 'time' => $morningStop->arrival_time ?? '—'];
        }
        if ($eveningStop) {
            $stops[] = ['id' => (string) $eveningStop->id, 'type' => 'Soir', 'locationName' => $eveningStop->name, 'time' => $eveningStop->arrival_time ?? '—'];
        }

        $knownPoints = [
            'Domicile' => [$morningStop?->address, $eveningStop?->address],
            'École' => [$school?->location],
        ];

        $pickupPoint = $this->tripPoint(
            $this->addressLabel($primaryStop?->address, $knownPoints),
            $primaryStop?->address,
            $primaryStop?->latitude,
            $primaryStop?->longitude
        );
        $dropoffPoint = $this->tripPoint(
            $this->addressLabel($school?->location, $knownPoints),
            $school?->location,
            $school?->latitude,
            $school?->longitude
        );

        // Live tracking only makes sense while the child is actually on the
        // bus — the driver's boarding scan (whichever period) is the source
        // of truth for that, not just "does the bus have a position". The
        // latest scan today tells us which state we're in: no scan yet
        // (hasn't boarded), 'board' with nothing after it (on board right
        // now), or 'alight' (dropped off — tracking for this trip is over).
        $latestScan = TransportBoardingScan::where('student_id', $student->id)
            ->whereDate('scanned_at', Carbon::today())
            ->orderByDesc('scanned_at')
            ->first();

        // Belt-and-suspenders: also require the bus to still have *this*
        // route's trip actively running. If the driver ends the trip
        // without individually scanning every child off (missed scan,
        // ended early), the last scan would otherwise stay stuck on
        // 'board' forever and keep showing a frozen, no-longer-updating
        // position — this cuts tracking the moment the trip itself ends,
        // regardless of whether the alight scan happened.
        $route = $data['route'];
        $tripActive = $bus && $bus->trip_started_at !== null && $bus->active_route_id === $route?->id;

        $childOnBoard = $latestScan?->action === 'board' && $tripActive;
        $childAlighted = $latestScan?->action === 'alight';

        $hasPosition = $childOnBoard && $bus && $bus->current_latitude !== null && $bus->current_longitude !== null;
        $busPosition = $hasPosition ? [
            'latitude' => (float) $bus->current_latitude,
            'longitude' => (float) $bus->current_longitude,
            'updatedAt' => $bus->position_updated_at?->toIso8601String(),
        ] : null;

        $distance = '';
        if ($hasPosition && $dropoffPoint['latitude'] !== null && $dropoffPoint['longitude'] !== null) {
            $km = $this->haversineKm(
                (float) $bus->current_latitude,
                (float) $bus->current_longitude,
                $dropoffPoint['latitude'],
                $dropoffPoint['longitude']
            );
            $distance = number_format($km, 1) . ' km';
        }

        return response()->json([
            'driver' => [
                'name' => $driverName !== '' ? $driverName : '—',
                'phone' => $driver->phone ?? '',
            ],
            'routeInfo' => [
                'busNumber' => $bus->bus_number ?? '—',
                'status' => $hasPosition ? 'EN ROUTE' : 'Programmé',
                'arrivalTime' => $primaryStop->arrival_time ?? '—',
                'distance' => $distance,
                'lastStop' => '',
                'nextStop' => $primaryStop->name ?? '—',
            ],
            'stops' => $stops,
            'pickupPoint' => $pickupPoint,
            'dropoffPoint' => $dropoffPoint,
            'busPosition' => $busPosition,
            'channel' => $childOnBoard && $bus ? "transport.bus.{$bus->id}" : '',
            'childOnBoard' => $childOnBoard,
            'boardedAt' => $childOnBoard ? $latestScan->scanned_at->toIso8601String() : null,
            'alightedAt' => $childAlighted ? $latestScan->scanned_at->toIso8601String() : null,
            'enrolled' => true,
            'enrollmentStatus' => $this->transportEnrollmentStatus($student, $enrollmentService),
        ]);
    }

    /** Great-circle distance in kilometers — used to show a real "distance to school" once a bus position has been reported, instead of the honest-empty placeholder used when no position exists. */
    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadiusKm * $c;
    }

    /**
     * Every zone the school currently serves (from its active routes),
     * priced — the parent picks one of these first, then a stop within it.
     * Price comes from the zone's transport FeeLevel (configured in
     * SchoolDashboard via "Configurer Frais" on a route), not the route itself.
     */
    public function transportZones(Request $request)
    {
        $student = $this->resolveStudent($request);

        $zoneNames = TransportRoute::where('school_id', $student->school_id)
            ->where('status', 'actif')
            ->whereNotNull('zone')
            ->distinct()
            ->orderBy('zone')
            ->pluck('zone');

        $academicYear = now()->month >= 8 ? now()->year . '-' . (now()->year + 1) : (now()->year - 1) . '-' . now()->year;
        $feeLevelsByZone = FeeLevel::where('school_id', $student->school_id)
            ->where('type', 'transport')
            ->where('academic_year', $academicYear)
            ->whereIn('level', $zoneNames)
            ->get()
            ->keyBy('level');

        return response()->json([
            'zones' => $zoneNames->map(fn (string $zone) => [
                'zone' => $zone,
                'price' => isset($feeLevelsByZone[$zone]) ? (float) $feeLevelsByZone[$zone]->total_amount : null,
            ])->values(),
        ]);
    }

    /**
     * Real stop points defined by the school for this student's bus route —
     * used to populate the zone → stop selection when requesting/changing
     * an enrollment. A `zone` query param scopes to that zone's active
     * routes; omitted, falls back to the student's current route (or every
     * stop in the school, for a first-time request before any zone is
     * chosen client-side).
     */
    public function transportStops(Request $request)
    {
        $student = $this->resolveStudent($request);
        $zone = $request->query('zone');
        $period = $request->query('period');

        if ($zone) {
            $stops = RouteStop::whereHas('route', function ($q) use ($student, $zone, $period) {
                $q->where('school_id', $student->school_id)->where('status', 'actif')->where('zone', $zone);
                // A route with no period applies to both (see Route::PERIODS
                // doc comment); only a period-specific route gets excluded
                // from the other period's stop list.
                if ($period) {
                    $q->where(fn ($q2) => $q2->whereNull('period')->orWhere('period', $period));
                }
            })
                ->with('route')
                ->orderBy('sequence')
                ->get();

            return response()->json([
                'stops' => $stops->map(fn (RouteStop $s) => [
                    'id' => (string) $s->id,
                    'name' => $s->name,
                    'address' => $s->address ?? '',
                    'arrivalTime' => $s->arrival_time ?? '—',
                    // Surfaced so the parent can tell 2 routes apart when a
                    // zone/bus has more than one — see stopsForMap() /
                    // findNearbyCrossRoutePair() on the admin side for the
                    // same disambiguation need.
                    'routeName' => $s->route->name ?? null,
                ]),
            ]);
        }

        $data = $this->service->transport($student);
        $route = $data['route'];

        // Same "add/edit a stop" mechanism either way — a student with no
        // route yet just gets every stop the school has defined instead of
        // being limited to one route, so the very same dialog also works for
        // a first-time enrollment request.
        $stops = $route
            ? RouteStop::where('route_id', $route->id)->with('route')->orderBy('sequence')->get()
            : RouteStop::where('school_id', $student->school_id)->with('route')->orderBy('sequence')->get();

        return response()->json([
            'stops' => $stops->map(fn(RouteStop $s) => [
                'id' => (string) $s->id,
                'name' => $s->name,
                'address' => $s->address ?? '',
                'arrivalTime' => $s->arrival_time ?? '—',
                'routeName' => $s->route->name ?? null,
            ]),
        ]);
    }

    /**
     * Requests the student's morning or evening stop on the bus route —
     * used to attach the pivot instantly; now it only creates a pending
     * `TransportEnrollmentRequest` for the school to approve. Works for a
     * brand-new enrollment too (not just switching stops on an existing
     * route), since it resolves the stop by school rather than by the
     * student's current route.
     */
    public function updateTransportStop(Request $request, TransportEnrollmentService $enrollmentService)
    {
        $request->validate([
            'route_stop_id' => ['required', 'integer'],
            'period' => ['required', 'in:morning,evening'],
        ]);

        $student = $this->resolveStudent($request);
        $period = $request->string('period')->toString();

        $stop = RouteStop::where('school_id', $student->school_id)->with('route')->findOrFail($request->integer('route_stop_id'));

        $pending = $enrollmentService->latestRequestFor($student->id, $period);
        abort_if(
            $pending && $pending->status === TransportEnrollmentRequest::STATUS_PENDING,
            422,
            "Une demande est déjà en attente pour cette période."
        );

        // The morning and evening stops must be in the same zone — a school
        // van doesn't cross town twice a day for one student. Checked
        // against whatever the other period is currently pending/approved
        // on, so this catches a mismatch regardless of which period the
        // parent picks first.
        $otherPeriod = $period === 'morning' ? 'evening' : 'morning';
        $otherRequest = $enrollmentService->latestRequestFor($student->id, $otherPeriod);
        if ($otherRequest && in_array($otherRequest->status, [TransportEnrollmentRequest::STATUS_PENDING, TransportEnrollmentRequest::STATUS_APPROVED], true)) {
            $otherZone = $otherRequest->routeStop?->route?->zone;
            if ($otherZone && $stop->route?->zone !== $otherZone) {
                abort(422, "Les arrêts du matin et du soir doivent être dans la même zone ({$otherZone}).");
            }
        }

        $enrollmentRequest = $enrollmentService->requestEnrollment($student, $stop, $period, $request->user());

        return response()->json([
            'enrollmentStatus' => ['status' => 'pending', 'rejectionReason' => null],
            'requestedStop' => [
                'id' => (string) $stop->id,
                'type' => $period === 'morning' ? 'Matin' : 'Soir',
                'locationName' => $stop->name,
                'time' => $stop->arrival_time ?? '—',
            ],
            'message' => "Votre demande d'inscription a été envoyée à l'école pour validation.",
        ]);
    }

    /**
     * Shows the real address only when it doesn't match a point the parent
     * already knows by name (their own stop, or the school) — otherwise the
     * shorthand, per the same rule used for both boarding and alighting.
     */
    private function addressLabel(?string $actualAddress, array $knownPoints): string
    {
        if (!$actualAddress) {
            return '—';
        }
        $normalized = trim(mb_strtolower($actualAddress));
        foreach ($knownPoints as $label => $candidates) {
            foreach (array_filter((array) $candidates) as $knownAddress) {
                if (trim(mb_strtolower($knownAddress)) === $normalized) {
                    return $label;
                }
            }
        }
        return $actualAddress;
    }

    private function tripPoint(?string $label, ?string $address, $latitude, $longitude): array
    {
        return [
            'label' => $label ?? '—',
            'address' => $address ?? '—',
            'latitude' => $latitude !== null ? (float) $latitude : null,
            'longitude' => $longitude !== null ? (float) $longitude : null,
        ];
    }

    /** Real bus trips logged by the school for this student's route, with real boarding/alighting points. */
    public function transportHistory(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $student = $this->resolveStudent($request);

        $data = $this->service->transport($student);
        $route = $data['route'];
        abort_if(!$route, 404, "Aucun trajet de bus assigné à cet élève.");

        $morningStop = $data['morningStop'];
        $eveningStop = $data['eveningStop'];
        $school = School::find($student->school_id);

        $knownPoints = [
            'Domicile' => [$morningStop?->address, $eveningStop?->address],
            'École' => [$school?->location],
        ];

        $query = TripLog::where('route_id', $route->id)->with('bus.driver');

        if ($request->filled('from')) {
            $query->whereDate('trip_date', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('trip_date', '<=', $request->query('to'));
        }

        $query->orderByDesc('trip_date')->orderByDesc('scheduled_start');

        $page = max(1, $request->integer('page', 1));
        $perPage = 10;
        $trips = $query->skip(($page - 1) * $perPage)->take($perPage + 1)->get();
        $hasMore = $trips->count() > $perPage;
        $trips = $trips->take($perPage);

        $dateLabel = function (Carbon $date) {
            if ($date->isToday()) {
                return "Aujourd'hui";
            }
            if ($date->isYesterday()) {
                return 'Hier';
            }
            return $date->translatedFormat('d M Y');
        };

        return response()->json([
            'history' => $trips->map(function (TripLog $trip) use ($morningStop, $eveningStop, $school, $knownPoints, $dateLabel) {
                $isMorning = $trip->shift === 'matin';
                $homeStop = $isMorning ? $morningStop : $eveningStop;

                $boardingAddress = $isMorning ? $homeStop?->address : $school?->location;
                $alightingAddress = $isMorning ? $school?->location : $homeStop?->address;

                $boarding = $isMorning
                    ? $this->tripPoint($this->addressLabel($boardingAddress, $knownPoints), $boardingAddress, $homeStop?->latitude, $homeStop?->longitude)
                    : $this->tripPoint($this->addressLabel($boardingAddress, $knownPoints), $boardingAddress, $school?->latitude, $school?->longitude);

                $alighting = $isMorning
                    ? $this->tripPoint($this->addressLabel($alightingAddress, $knownPoints), $alightingAddress, $school?->latitude, $school?->longitude)
                    : $this->tripPoint($this->addressLabel($alightingAddress, $knownPoints), $alightingAddress, $homeStop?->latitude, $homeStop?->longitude);

                $driver = $trip->bus?->driver;
                $driverName = $driver ? trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? '')) : '—';

                return [
                    'id' => (string) $trip->id,
                    'title' => 'Trajet ' . ($isMorning ? 'Matin' : 'Soir'),
                    'dateLabel' => $dateLabel($trip->trip_date),
                    'time' => $trip->scheduled_start ? substr($trip->scheduled_start, 0, 5) : '—',
                    'status' => TripLog::STATUSES[$trip->status] ?? $trip->status,
                    'driver' => $driverName !== '' ? $driverName : '—',
                    'boarding' => $boarding,
                    'alighting' => $alighting,
                ];
            }),
            'hasMore' => $hasMore,
        ]);
    }

    /**
     * Real chapters/lessons come from the Syllabus module (titles only — there is
     * no per-student lesson-completion tracking anywhere in this schema, so every
     * lesson honestly reports `completed: false` and every chapter `progress: 0`
     * rather than inventing numbers). Homeworks and notes are the real
     * Homework/Bulletin data for this one subject, gated by the same
     * class-level + subject-level publish lock as everywhere else in the portal.
     */
    public function courseDetail(Request $request, int $courseId)
    {
        $student = $this->resolveStudent($request);

        $subject = Subject::where('school_id', $student->school_id)->findOrFail($courseId);

        $currentSemester = $this->bulletinStats->currentSemester($student->school_id);
        $teacher = null;
        $chapters = collect();

        if ($student->academic_class_id && $currentSemester) {
            $slot = Timetable::where('academic_class_id', $student->academic_class_id)
                ->where('subject_id', $subject->id)
                ->with('teacher')
                ->first();
            $teacher = $slot?->teacher;

            if (!$teacher) {
                // Check if any homework was created by a teacher for this subject/class
                $hw = HomeworkAssignment::where('academic_class_id', $student->academic_class_id)
                    ->where('subject_id', $subject->id)
                    ->whereNotNull('teacher_id')
                    ->with('teacher')
                    ->first();
                $teacher = $hw?->teacher;
            }

            if (!$teacher) {
                // Fallback to assigned class teachers
                $academicClass = \App\Modules\Academic\Domain\Models\AcademicClass::with('teachers')->find($student->academic_class_id);
                if ($academicClass && $academicClass->teachers->isNotEmpty()) {
                    $matched = $academicClass->teachers->first(function ($t) use ($subject) {
                        return str_contains(strtolower($t->department ?? ''), strtolower($subject->name))
                            || str_contains(strtolower($t->role ?? ''), strtolower($subject->name));
                    });

                    if ($matched) {
                        $teacher = $matched;
                    } elseif ($academicClass->teachers->count() > 1 && $subject->id == 2) {
                        $teacher = $academicClass->teachers->get(1);
                    } else {
                        $teacher = $academicClass->teachers->first() ?? $academicClass->headTeacher;
                    }
                }
            }

            $syllabus = Syllabus::where('academic_class_id', $student->academic_class_id)
                ->where('subject_id', $subject->id)
                ->where('semester_id', $currentSemester->id)
                ->with('lessons')
                ->first();

            $chapters = $syllabus ? $syllabus->lessons->map(function ($lesson) {
                $subLessons = collect($lesson->sub_lessons);
                $completedCount = $subLessons->where('status', 'completed')->count();
                $totalCount = $subLessons->count();

                $statusLabels = [
                    'completed' => 'Terminé',
                    'in_progress' => 'En cours',
                    'not_started' => 'Non débuté',
                ];

                return [
                    'id' => (string) $lesson->id,
                    'title' => $lesson->title,
                    'subtitle' => $totalCount > 0 ? "$totalCount leçon(s) • $completedCount terminée(s)" : '',
                    'progress' => (float) round($lesson->progress_percentage / 100, 2),
                    'progressPercentage' => (int) $lesson->progress_percentage,
                    'progressText' => $totalCount > 0 ? "{$completedCount}/{$totalCount} leçons" : '',
                    'status' => $lesson->progress_status,
                    'statusLabel' => $statusLabels[$lesson->progress_status] ?? 'Non débuté',
                    'startedAt' => $lesson->started_at?->translatedFormat('d M Y, H:i'),
                    'completedAt' => $lesson->completed_at?->translatedFormat('d M Y, H:i'),
                    'iconType' => 'menu_book',
                    'isLocked' => false,
                    'lessons' => $subLessons->map(function ($sub) {
                        $subStatus = $sub['status'] ?? 'not_started';
                        $subStarted = !empty($sub['started_at']) ? \Carbon\Carbon::parse($sub['started_at']) : null;
                        $subCompleted = !empty($sub['completed_at']) ? \Carbon\Carbon::parse($sub['completed_at']) : null;

                        $durationText = '';
                        if ($subCompleted) {
                            $durationText = 'Terminé le ' . $subCompleted->translatedFormat('d M, H:i');
                        } elseif ($subStarted) {
                            $durationText = 'Débuté le ' . $subStarted->translatedFormat('d M, H:i');
                        }

                        return [
                            'title' => $sub['title'] ?? '',
                            'status' => $subStatus,
                            'statusLabel' => $subStatus === 'completed' ? 'Terminé' : ($subStatus === 'in_progress' ? 'En cours' : 'À faire'),
                            'completed' => $subStatus === 'completed',
                            'inProgress' => $subStatus === 'in_progress',
                            'startedAt' => $subStarted?->translatedFormat('d M Y, H:i'),
                            'completedAt' => $subCompleted?->translatedFormat('d M Y, H:i'),
                            'duration' => $durationText,
                        ];
                    })->values(),
                ];
            })->values() : collect();
        }

        $homeworkAssignments = $student->academic_class_id
            ? HomeworkAssignment::where('academic_class_id', $student->academic_class_id)
                ->where('subject_id', $subject->id)
                ->orderByDesc('scheduled_at')
                ->limit(10)
                ->get()
            : collect();

        $homeworks = $homeworkAssignments->map(function (HomeworkAssignment $assignment) use ($student) {
            $submission = HomeworkSubmission::where('homework_assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->first();

            return [
                'title' => $assignment->title,
                'date' => $assignment->scheduled_at?->translatedFormat('d M Y') ?? '',
                'type' => $assignment->type === HomeworkAssignment::TYPE_TEST ? 'Interrogation' : 'Devoir Maison',
                'isDone' => $submission?->score !== null,
                'attachments' => [],
            ];
        })->values();

        $notes = collect();
        $courseAverage = '—';
        $isPublished = false;

        if ($currentSemester && $student->academic_class_id) {
            $publication = app(\App\Modules\Bulletin\Domain\Repositories\BulletinPublicationRepositoryInterface::class)
                ->findOrCreate($student->academic_class_id, $currentSemester->id);
            $isPublished = $publication->status === BulletinPublication::STATUS_PUBLISHED;
        }

        if ($isPublished) {
            $gradeRepository = app(BulletinGradeRepositoryInterface::class);
            $classGrades = $gradeRepository->forClassAndSemester($student->academic_class_id, $currentSemester->id);
            $classGrades = $this->bulletinStats->mergeHomeworkGrades($classGrades, $student->academic_class_id, $currentSemester->id, $subject->id, $student->school_id);
            $classGrades = $this->bulletinStats->filterPublishedSubjects($classGrades, $student->academic_class_id, $currentSemester->id);

            $rawNotes = $classGrades->where('student_id', $student->id)->where('subject_id', $subject->id);

            $notes = $rawNotes->map(fn($g) => [
                'title' => $g->evaluationType->name ?? 'Note',
                'type' => $g->evaluationType->name ?? 'Note',
                'score' => number_format($g->score, 2),
                'maxScore' => '20',
                'date' => '',
                'coefficient' => (float) ($g->evaluationType->coefficient ?? 1),
            ])->values();

            if ($rawNotes->isNotEmpty()) {
                $aggregated = $this->bulletinStats->aggregateToSubjectGrades($rawNotes);
                $subjectAverage = collect($aggregated)->first()?->score;
                $courseAverage = $subjectAverage !== null ? number_format($subjectAverage, 1) . '/20' : '—';
            }
        }

        $teacherName = $teacher ? trim("{$teacher->first_name} {$teacher->last_name}") : '';
        if (empty($teacherName) || $teacherName === '—') {
            $teacherName = "Professeur de {$subject->name}";
        }
        $teacherRole = $teacher?->role ?: ($teacher?->department ?: "Enseignant {$subject->name}");

        return response()->json([
            'courseTitle' => $subject->name,
            'teacherName' => $teacherName,
            'teacherRole' => $teacherRole,
            'teacherAvatarUrl' => $teacher?->photo_path ? asset('storage/' . $teacher->photo_path) : '',
            'timeSpent' => '',
            'courseAverage' => $courseAverage,
            'coefficient' => (float) ($subject->coefficient ?? 1),
            'chapters' => $chapters,
            'homeworks' => $homeworks,
            'notes' => $notes,
        ]);
    }

    /**
     * Per-child profile drill-down. `averageEvaluation` is a deterministic
     * score-band label (same bands as `BulletinStatsService::suggestedRemark`
     * uses), not AI-generated text. `aiAdvice` honestly stays empty — no real
     * equivalent exists. `recentActivities` merges three real, independent
     * sources (grades, absences, behaviour observations) into one feed sorted
     * by real timestamp.
     */
    public function studentProfile(Request $request, int $studentId)
    {
        $parent = $request->user();
        $student = $this->service->ensureChildBelongsToParent($parent, $studentId);

        $currentSemester = $this->bulletinStats->currentSemester($student->school_id);
        $average = $currentSemester ? $this->bulletinStats->studentAverageForSemester($student, $currentSemester) : null;

        $evaluationLabel = match (true) {
            $average === null => '',
            $average >= 16 => 'EXCELLENT',
            $average >= 14 => 'BIEN',
            $average >= 10 => 'PASSABLE',
            default => 'INSUFFISANT',
        };

        $attendanceData = $this->service->attendance($student, 60);
        $attendancePercentage = (int) round($attendanceData['attendanceRate'] ?? 0);

        $feeSummary = $this->service->fees($student);
        $feeLabels = ['paid' => 'Payé', 'late' => 'En retard', 'partial' => 'Partiel', 'pending' => 'En attente', 'unconfigured' => 'Non configuré'];
        $feeColors = ['paid' => '0xFF10B981', 'late' => '0xFFDC2626', 'partial' => '0xFFF59E0B', 'pending' => '0xFFF59E0B', 'unconfigured' => '0xFF9CA3AF'];
        $feeStatus = $feeSummary['status'] ?? 'unconfigured';

        $performances = collect();
        $publishedSubjectIds = collect();

        if ($currentSemester && $student->academic_class_id) {
            $publishedSubjectIds = app(\App\Modules\Bulletin\Domain\Repositories\BulletinSubjectPublicationRepositoryInterface::class)
                ->publishedSubjectIds($student->academic_class_id, $currentSemester->id);

            $rawGrades = app(BulletinGradeRepositoryInterface::class)
                ->forClassAndSemester($student->academic_class_id, $currentSemester->id)
                ->where('student_id', $student->id);
            $merged = $this->bulletinStats->mergeHomeworkGrades($rawGrades, $student->academic_class_id, $currentSemester->id, null, $student->school_id);
            $merged = $this->bulletinStats->filterPublishedSubjects($merged, $student->academic_class_id, $currentSemester->id);
            $aggregated = $this->bulletinStats->aggregateToSubjectGrades($merged);

            $performances = collect($aggregated)->map(function ($g) {
                $score = $g->score;
                [$color] = match (true) {
                    $score >= 16 => ['0xFF2646A6'],
                    $score >= 12 => ['0xFF10B981'],
                    default => ['0xFF92400E'],
                };

                return [
                    'subject' => $g->subject->name ?? '—',
                    'score' => number_format($score, 1) . '/20',
                    'scoreColorHex' => $color,
                    'comment' => $g->remark ?? '',
                    'progressValue' => round($score / 20, 2),
                    'borderColorHex' => $color,
                ];
            })->values();
        }

        $recentActivities = $this->collectActivities($student)->take(5)->values();
        $lastIndex = $recentActivities->count() - 1;
        $recentActivities = $recentActivities->map(function ($a, $i) use ($lastIndex) {
            unset($a['sortAt']);
            $a['isLast'] = $i === $lastIndex;
            return $a;
        })->values();

        $studentSchool = School::find($student->school_id);

        // Same "which fields does the card show" toggle the school configures
        // in the Cards module (SchoolDashboard::cards) — not read via
        // CardTemplateRepositoryInterface::findOrDefault() because that reads
        // auth()->user()->school_id, which is the *staff* guard and would be
        // null here (this endpoint runs under the parent Sanctum guard).
        $cardTemplate = CardTemplate::where('school_id', $student->school_id)->where('card_type', 'student')->first();
        $cardFields = $cardTemplate->front_fields ?? CardTemplate::defaultsFor('student')['front_fields'];

        return response()->json([
            'studentName' => trim("{$student->first_name} {$student->last_name}"),
            'studentAvatar' => $student->photo_path ? asset('storage/' . $student->photo_path) : '',
            'classInfo' => $student->academicClass->name ?? '—',
            'school' => $studentSchool?->name ?? '—',
            'rollNumber' => $student->roll_number,
            'bloodGroup' => $student->blood_group ?? '—',
            'dateOfBirth' => $student->dob ? Carbon::parse($student->dob)->format('d M Y') : '—',
            'emergencyContact' => optional($student->guardians->first())->phone ?? '—',
            // Which of the fields above the school actually wants shown on
            // the card front — same STUDENT_FIELDS keys as CardTemplate.
            'cardFields' => $cardFields,
            // Matches the QR format already printed on physical cards
            // (CardController::printCards): "{school->code}:{matricule}" —
            // same value RecordAccessCheckInUseCase parses on scan.
            'qrCode' => trim(($studentSchool?->code ?? '') . ':' . $student->roll_number, ':'),
            'gender' => $student->gender === 'female' ? 'Féminin' : 'Masculin',
            'academicYear' => $student->academic_year,
            'generalAverage' => $average !== null ? number_format($average, 1) : '—',
            'averageEvaluation' => $evaluationLabel,
            'attendancePercentage' => $attendancePercentage . '%',
            'feesStatus' => $feeLabels[$feeStatus] ?? '—',
            'feesStatusColorHex' => $feeColors[$feeStatus] ?? '0xFF9CA3AF',
            'aiAdvice' => '',
            'performances' => $performances,
            'recentActivities' => $recentActivities,
        ]);
    }

    /**
     * Full, paginated activity history for the "Voir tout" view on the student
     * profile — same three real sources as the profile summary's preview
     * (grades/absences/observations), just without the tight 5-item cap.
     */
    public function studentActivityHistory(Request $request, int $studentId)
    {
        $request->validate([
            'type' => ['nullable', 'string', 'in:grade,absence,observation'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $parent = $request->user();
        $student = $this->service->ensureChildBelongsToParent($parent, $studentId);

        $sorted = $this->collectActivities($student, $request->query('type'));

        $page = max(1, $request->integer('page', 1));
        $perPage = 10;
        $slice = $sorted->slice(($page - 1) * $perPage, $perPage + 1)->values();
        $hasMore = $slice->count() > $perPage;
        $slice = $slice->take($perPage);
        $lastIndex = $slice->count() - 1;

        $history = $slice->map(function ($a, $i) use ($lastIndex) {
            unset($a['sortAt']);
            $a['isLast'] = $i === $lastIndex;
            return $a;
        })->values();

        return response()->json([
            'history' => $history,
            'hasMore' => $hasMore,
        ]);
    }

    /**
     * Merges the three real activity sources (published grades, absences,
     * behavior observations) for one student, sorted newest-first. Shared by
     * studentProfile()'s 5-item preview and studentActivityHistory()'s full
     * paginated list so both stay in sync with the same underlying data.
     */
    private function collectActivities(Student $student, ?string $type = null): \Illuminate\Support\Collection
    {
        $activities = collect();

        if (!$type || $type === 'grade') {
            $this->recentPublishedGrades($student)->each(fn(BulletinGrade $g) => $activities->push([
                'sortAt' => $g->created_at,
                'iconType' => 'star',
                'iconColorHex' => '0xFFFFFFFF',
                'iconBgColorHex' => '0xFF2646A6',
                'time' => $g->created_at?->translatedFormat('d M, H:i') ?? '',
                'title' => 'Nouvelle note :',
                'description' => ($g->subject->name ?? '—') . ' - ' . ($g->evaluationType->name ?? 'Note'),
                'extraText' => number_format($g->score, 1) . '/20',
                'extraTextColorHex' => '0xFF2646A6',
                'extraTextSize' => '20',
            ]));
        }

        if (!$type || $type === 'absence') {
            AttendanceRecord::where('student_id', $student->id)
                ->where('status', AttendanceRecord::STATUS_ABSENT)
                ->orderByDesc('date')
                ->limit(100)
                ->get()
                ->each(fn(AttendanceRecord $a) => $activities->push([
                    'sortAt' => $a->date,
                    'iconType' => 'calendar_month',
                    'iconColorHex' => '0xFFFFFFFF',
                    'iconBgColorHex' => '0xFFDC2626',
                    'time' => $a->date?->translatedFormat('d M Y') ?? '',
                    'title' => 'Absence signalée :',
                    'description' => $a->notes ?: ($a->justified ? 'Absence justifiée' : 'Absence non justifiée'),
                    'extraText' => $a->justified ? 'Justifiée' : 'Justification requise',
                    'extraTextColorHex' => $a->justified ? '0xFF10B981' : '0xFFDC2626',
                    'extraTextSize' => '12',
                ]));
        }

        if (!$type || $type === 'observation') {
            ReportCardObservation::where('student_id', $student->id)
                ->orderByDesc('created_at')
                ->limit(100)
                ->get()
                ->each(fn(ReportCardObservation $o) => $activities->push([
                    'sortAt' => $o->created_at,
                    'iconType' => 'thumb_up',
                    'iconColorHex' => '0xFFFFFFFF',
                    'iconBgColorHex' => '0xFF10B981',
                    'time' => $o->created_at?->translatedFormat('d M') ?? '',
                    'title' => 'Note de comportement :',
                    'description' => $o->comment,
                    'extraText' => null,
                    'extraTextColorHex' => null,
                    'extraTextSize' => null,
                ]));
        }

        return $activities->sortByDesc('sortAt')->values();
    }

    /** Real recent grades on subjects the head teacher has published — never leaks draft/unpublished grades. */
    private function recentPublishedGrades(Student $student, int $limit = 100): \Illuminate\Support\Collection
    {
        $currentSemester = $this->bulletinStats->currentSemester($student->school_id);

        if (!$currentSemester || !$student->academic_class_id) {
            return collect();
        }

        $publishedSubjectIds = app(\App\Modules\Bulletin\Domain\Repositories\BulletinSubjectPublicationRepositoryInterface::class)
            ->publishedSubjectIds($student->academic_class_id, $currentSemester->id);

        return BulletinGrade::where('student_id', $student->id)
            ->where('semester_id', $currentSemester->id)
            ->whereIn('subject_id', $publishedSubjectIds)
            ->with(['subject', 'evaluationType'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /** Always includes both a date reference and the time — "today"/"yesterday" alone previously hid the time, and a same-day timestamp alone previously hid which day it was. */
    private function relativeNotifTime(Carbon $at): string
    {
        $time = $at->format('H:i');

        if ($at->isToday()) {
            return "Aujourd'hui, $time";
        }
        if ($at->isYesterday()) {
            return "Hier, $time";
        }

        return $at->translatedFormat('d M Y') . ", $time";
    }

    /**
     * Real notifications feed for the "Messagerie" tab: late-fee alerts, recent
     * published grades, upcoming school events and justified absences — all
     * drawn from data that already exists elsewhere in this controller. No
     * fabricated "AI suggestion" card (the mock's `type: 'ia'` item is never
     * emitted here — see the class docblock's no-fabrication policy).
     */
    public function notifications(Request $request)
    {
        $parent = $request->user();
        $children = $this->service->childrenOf($parent);
        abort_if($children->isEmpty(), 404, "Aucun enfant rattaché à votre compte.");
        $schoolIds = $children->pluck('school_id')->unique()->values();

        $items = collect();

        foreach ($children as $child) {
            foreach (FeeLevel::TYPES as $type => $label) {
                $summary = $this->service->fees($child, $type);
                if ($summary['status'] === 'late' && $summary['nextDueDate']) {
                    $daysLate = max(1, (int) floor($summary['nextDueDate']->diffInDays(now(), true)));
                    $items->push([
                        'id' => "fee-{$type}-{$child->id}",
                        'title' => 'Paiement en retard',
                        'description' => "Le règlement de {$label} pour {$child->first_name} est en attente depuis {$daysLate} jour" . ($daysLate > 1 ? 's' : '') . '.',
                        // Ongoing alert re-evaluated on every fetch — sorts as "now", not the original (possibly very old) due date.
                        'sortAt' => now(),
                        'type' => 'urgent',
                        'actionText' => 'Payer maintenant',
                        'studentId' => (string) $child->id,
                        'feeType' => $type,
                    ]);
                }
            }
        }

        foreach ($children as $child) {
            $this->recentPublishedGrades($child, 3)->each(function (BulletinGrade $g) use ($items, $child) {
                $comment = $g->remark ? " :\n\"{$g->remark}\"" : '.';
                $items->push([
                    'id' => "grade-{$g->id}",
                    'title' => "Nouvelle note de {$child->first_name}",
                    'description' => "{$child->first_name} a obtenu " . number_format($g->score, 1) . "/20 en " . ($g->subject->name ?? '—') . $comment,
                    'sortAt' => $g->created_at,
                    'type' => 'pedagogique',
                    'actionText' => 'Voir le bulletin',
                    'studentId' => (string) $child->id,
                    'feeType' => null,
                ]);
            });
        }

        $this->upcomingEventsQuery($schoolIds)->limit(5)->get()->each(function (Event $e) use ($items) {
            $items->push([
                'id' => "event-{$e->id}",
                'title' => $e->title,
                'description' => $e->description ?: '',
                'sortAt' => $e->start_at,
                'type' => 'administratif',
                'actionText' => "Détails de l'annonce",
                'studentId' => null,
                'feeType' => null,
            ]);
        });

        foreach ($children as $child) {
            AttendanceRecord::where('student_id', $child->id)
                ->where('status', AttendanceRecord::STATUS_ABSENT)
                ->where('justified', true)
                ->orderByDesc('updated_at')
                ->limit(2)
                ->get()
                ->each(function (AttendanceRecord $a) use ($items, $child) {
                    $items->push([
                        'id' => "absence-{$a->id}",
                        'title' => 'Absence justifiée',
                        'description' => "Le justificatif pour l'absence de {$child->first_name} du " . ($a->date?->translatedFormat('d/m') ?? '—') . ' a été validé.',
                        'sortAt' => $a->updated_at,
                        'type' => 'normal',
                        'actionText' => null,
                        'studentId' => null,
                        'feeType' => null,
                    ]);
                });
        }

        $notifications = $items->sortByDesc('sortAt')->values()->take(30)->map(function ($n) {
            $n['time'] = $this->relativeNotifTime($n['sortAt']);
            $n['isRead'] = false;
            unset($n['sortAt']);

            return $n;
        });

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * School Track: Filter metadata (real cities, tuition range, active
     * facilities) so the mobile filter sheet doesn't have to guess at
     * hardcoded values that drift from the actual data.
     */
    public function schoolTrackFilters(Request $request)
    {
        $schools = School::where('status', '!=', 'suspendu')->get(['id', 'location']);

        $cities = $schools
            ->map(fn (School $s) => trim(explode(',', $s->location ?? '')[0] ?? ''))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $fraisValues = $schools
            ->map(fn (School $s) => $s->averageAnnualTuitionFee())
            ->filter(fn ($v) => $v !== null);

        $facilities = Facility::where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'slug', 'icon', 'category']);

        return response()->json([
            'cities' => $cities,
            'fraisMin' => $fraisValues->isNotEmpty() ? (int) $fraisValues->min() : 0,
            'fraisMax' => $fraisValues->isNotEmpty() ? (int) $fraisValues->max() : 0,
            'facilities' => $facilities,
        ]);
    }

    /**
     * School Track: Fetch nearby schools based on user geographic position.
     */
    public function schoolTrackNearby(Request $request)
    {
        $userCity = $request->query('city');
        $userLat = $request->has('lat') ? (float) $request->query('lat') : null;
        $userLng = $request->has('lng') ? (float) $request->query('lng') : null;
        $radiusKm = $request->has('radius_km') ? (float) $request->query('radius_km') : null;

        $schools = School::with('facilitiesList')
            ->where('status', '!=', 'suspendu')
            ->get()
            ->map(fn(School $s) => $s->toSchoolTrackArray($userLat, $userLng, $userCity));

        if ($radiusKm !== null) {
            $filtered = $schools->filter(fn($s) => $s['distanceKm'] <= $radiusKm)->values();
            if ($filtered->isNotEmpty()) {
                $schools = $filtered;
            }
        }

        $schools = $schools->sortBy('distanceKm')->values();

        return response()->json(['schools' => $schools]);
    }

    /**
     * School Track: Search schools with query, filters, geographic position and sort.
     */
    public function schoolTrackSearch(Request $request)
    {
        $query = strtolower(trim($request->query('query', '')));
        $userCity = $request->query('city');
        $userLat = $request->has('lat') ? (float) $request->query('lat') : null;
        $userLng = $request->has('lng') ? (float) $request->query('lng') : null;
        $maxDistance = $request->has('max_distance') ? (float) $request->query('max_distance') : null;
        $minFrais = $request->has('min_frais') ? (int) $request->query('min_frais') : null;
        $maxFrais = $request->has('max_frais') ? (int) $request->query('max_frais') : null;
        $requiredFacilities = $request->query('facilities', []);
        if (is_string($requiredFacilities)) {
            $requiredFacilities = array_filter(explode(',', $requiredFacilities));
        }
        $aiOnly = filter_var($request->query('ai_only', false), FILTER_VALIDATE_BOOLEAN);
        $sortBy = $request->query('sort_by', 'rendement');

        $schools = School::with('facilitiesList')
            ->where('status', '!=', 'suspendu')
            ->get()
            ->map(fn(School $s) => $s->toSchoolTrackArray($userLat, $userLng, $userCity));

        if (!empty($query)) {
            $schools = $schools->filter(function ($s) use ($query) {
                return str_contains(strtolower($s['name']), $query)
                    || str_contains(strtolower($s['location']), $query)
                    || str_contains(strtolower($s['city']), $query)
                    || collect($s['tags'])->contains(fn($t) => str_contains(strtolower($t), $query));
            });
        }

        if ($maxDistance !== null) {
            $schools = $schools->filter(fn($s) => $s['distanceKm'] <= $maxDistance);
        }

        // A null fraisAnnuels means the school hasn't reported tuition yet —
        // that's "unknown", not "$0", so it must not be silently excluded
        // by a min/max range filter that's active by default in the app.
        if ($minFrais !== null) {
            $schools = $schools->filter(fn($s) => $s['fraisAnnuels'] === null || $s['fraisAnnuels'] >= $minFrais);
        }

        if ($maxFrais !== null) {
            $schools = $schools->filter(fn($s) => $s['fraisAnnuels'] === null || $s['fraisAnnuels'] <= $maxFrais);
        }

        if (!empty($requiredFacilities)) {
            $schools = $schools->filter(function ($s) use ($requiredFacilities) {
                foreach ($requiredFacilities as $f) {
                    if (empty($s['facilities'][$f]))
                        return false;
                }
                return true;
            });
        }

        if ($aiOnly) {
            $schools = $schools->filter(fn($s) => $s['scoreIA'] >= 8.0);
        }

        $schools = match ($sortBy) {
            'proximite' => $schools->sortBy('distanceKm'),
            'frais_asc' => $schools->sortBy('fraisAnnuels'),
            'frais_desc' => $schools->sortByDesc('fraisAnnuels'),
            'score' => $schools->sortByDesc('scoreIA'),
            default => $schools->sortByDesc('successRate'),
        };

        return response()->json(['schools' => $schools->values()]);
    }

    /**
     * School Track: Fetch single school by ID.
     */
    public function schoolTrackDetail(Request $request, $id)
    {
        $school = School::with('facilitiesList')->find((int) $id);
        if (!$school) {
            $school = School::with('facilitiesList')->where('code', $id)->first();
        }
        if (!$school) {
            $school = School::with('facilitiesList')->first();
        }

        abort_if(!$school, 404, "Établissement non trouvé.");

        return response()->json(['school' => $school->toSchoolTrackArray()]);
    }

    /**
     * School Track: Fetch schools for comparison.
     */
    public function schoolTrackCompare(Request $request)
    {
        $idsParam = $request->query('ids', '');
        $ids = is_array($idsParam) ? $idsParam : array_filter(explode(',', (string) $idsParam));

        $schools = School::with('facilitiesList')
            ->whereIn('id', $ids)
            ->get()
            ->map(fn(School $s) => $s->toSchoolTrackArray());

        if ($schools->isEmpty()) {
            $schools = School::with('facilitiesList')
                ->limit(2)
                ->get()
                ->map(fn(School $s) => $s->toSchoolTrackArray());
        }

        return response()->json(['schools' => $schools->values()]);
    }

    /**
     * Parent Profile & Personal Info.
     */
    public function profile(Request $request)
    {
        $parent = $request->user();
        $children = $this->service->childrenOf($parent);

        $nameParts = explode(' ', trim($parent->name ?? ''));
        $firstName = $nameParts[0] ?? '';
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

        $avatarUrl = 'assets/avatars/avatar_1.png';
        if (!empty($parent->avatar_path)) {
            if (str_starts_with($parent->avatar_path, 'http') || str_starts_with($parent->avatar_path, 'assets/')) {
                $avatarUrl = $parent->avatar_path;
            } else {
                $avatarUrl = asset('storage/' . ltrim($parent->avatar_path, '/'));
            }
        }

        return response()->json([
            'profile' => [
                'id' => (string) $parent->id,
                'name' => $parent->name ?? '',
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $parent->email ?? '',
                'phone' => $parent->phone ?? '',
                'avatarUrl' => $avatarUrl,
                'profession' => $parent->profession ?? 'Parent d\'élève',
                'address' => $parent->address ?? 'Abidjan, Côte d\'Ivoire',
                'city' => $parent->city ?? 'Abidjan',
                'childrenCount' => $children->count(),
                'childrenNames' => $children->map(fn($c) => $c->first_name)->implode(', '),
                'createdAt' => $parent->created_at?->format('d/m/Y') ?? '01/09/2024',
            ]
        ]);
    }

    /**
     * Update Parent Profile & Personal Info.
     */
    public function updateProfile(Request $request)
    {
        $parent = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:191',
            'phone' => 'sometimes|string|max:30',
            'profession' => 'sometimes|nullable|string|max:191',
            'address' => 'sometimes|nullable|string|max:255',
            'city' => 'sometimes|nullable|string|max:100',
            'avatar_url' => 'sometimes|nullable|string|max:500',
        ]);

        if (isset($validated['first_name']) || isset($validated['last_name'])) {
            $fn = $validated['first_name'] ?? '';
            $ln = $validated['last_name'] ?? '';
            $validated['name'] = trim("$fn $ln");
        }

        if (!empty($validated['avatar_url'])) {
            $parent->avatar_path = $validated['avatar_url'];
        }

        $parent->fill($validated);
        $parent->save();

        return $this->profile($request);
    }

    /**
     * Change Parent Account Password.
     */
    public function changePassword(Request $request)
    {
        $parent = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $parent->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.',
                'error' => 'invalid_current_password'
            ], 422);
        }

        $parent->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        $parent->save();

        return response()->json([
            'message' => 'Mot de passe modifié avec succès !',
            'status' => 'success'
        ]);
    }

    /**
     * Registers/refreshes this device's FCM token for push notifications.
     * Called on every app boot (not just from a settings screen), so a
     * reinstalled or token-rotated app always ends up with the current one.
     */
    public function updateDeviceToken(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string|max:255',
        ]);

        $parent = $request->user();
        $parent->update(['fcm_token' => $validated['fcm_token']]);

        return response()->json(['message' => 'Token enregistré.', 'status' => 'success']);
    }
}

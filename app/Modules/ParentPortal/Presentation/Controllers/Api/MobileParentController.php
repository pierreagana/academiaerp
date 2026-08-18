<?php

namespace App\Modules\ParentPortal\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\ParentAccount;
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
use App\Modules\Infirmary\Domain\Models\Intervention;
use App\Modules\ParentPortal\Application\Services\ParentPortalService;
use App\Modules\Presence\Domain\Models\AccessLog;
use App\Modules\Presence\Domain\Models\AttendanceRecord;
use App\Modules\ReportCard\Domain\Models\ReportCardObservation;
use App\Modules\SuperAdmin\Domain\Models\School;
use App\Modules\Transport\Domain\Models\RouteStop;
use App\Modules\Transport\Domain\Models\TripLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
    public function __construct(private ParentPortalService $service, private BulletinStatsService $bulletinStats) {}

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
            return $this->service->ensureChildBelongsToParent($parent, (int) $request->query('student_id'));
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
            'location' => $e->external_address ?: ($e->room?->name ? 'Salle '.$e->room->name : ''),
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

        $events = $this->upcomingEventsQuery($schoolIds)->limit(50)->get()->map(fn (Event $e) => $this->formatEvent($e))->values();

        return response()->json(['events' => $events]);
    }

    public function home(Request $request)
    {
        $parent = $request->user();
        $overview = $this->service->overview($parent);
        $children = $overview['children'];
        abort_if($children->isEmpty(), 404, "Aucun enfant rattaché à votre compte.");

        $students = $children->map(fn (Student $c) => [
            'id' => (string) $c->id,
            'name' => $c->first_name,
            'avatarUrl' => $c->photo_path ? asset('storage/'.$c->photo_path) : '',
        ])->values();

        $averages = $children->pluck('average')->filter(fn ($a) => $a !== null);
        $averageScore = $averages->isNotEmpty() ? round($averages->avg(), 1) : 0.0;

        $rates = $children->pluck('attendanceRate')->filter(fn ($a) => $a !== null);
        $attendancePercentage = $rates->isNotEmpty() ? (int) round($rates->avg()) : 0;

        $priority = ['late' => 0, 'partial' => 1, 'pending' => 2, 'unconfigured' => 3, 'paid' => 4];
        $labels = ['late' => 'En retard', 'partial' => 'Partiel', 'pending' => 'En attente', 'unconfigured' => 'Non configuré', 'paid' => 'À jour'];
        $worst = $children->pluck('feeStatus')->sortBy(fn ($s) => $priority[$s] ?? 99)->first();
        $schoolingStatus = $labels[$worst] ?? '—';

        $nextDue = null;
        foreach ($children as $c) {
            $due = $this->service->fees($c)['nextDueDate'] ?? null;
            if ($due && (!$nextDue || $due->lt($nextDue))) {
                $nextDue = $due;
            }
        }

        $upcomingAssignments = collect($overview['upcoming'])->map(fn ($a) => [
            'id' => (string) $a->id,
            'studentId' => (string) $a->studentId,
            'subject' => $a->subject->name ?? '—',
            'description' => $a->title,
            'time' => $a->scheduled_at ? 'Remise: '.$a->scheduled_at->format('H:i') : '',
            'badgeText' => $a->scheduled_at ? $a->scheduled_at->diffForHumans(null, true) : '',
            'badgeColorHex' => 'EEF2FF',
            'badgeTextColorHex' => '2646A6',
        ])->values();

        $individualTracking = $children->map(fn (Student $c) => [
            'id' => (string) $c->id,
            'studentId' => (string) $c->id,
            'score' => $c->average !== null ? number_format($c->average, 1).'/20' : '—',
            'description' => $c->average !== null ? 'Moyenne actuelle' : 'Aucune moyenne publiée',
            'isExcellent' => $c->average !== null && $c->average >= 14,
        ])->values();

        $schoolIds = $children->pluck('school_id')->unique()->values();
        $upcomingEvents = $this->upcomingEventsQuery($schoolIds)->limit(3)->get()->map(fn (Event $e) => $this->formatEvent($e))->values();

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

        $calendarDays = collect($data['records'])->map(fn ($r) => [
            'date' => (string) $r->date->day,
            'status' => $r->status === 'present' ? 'present' : ($r->status === 'late' ? 'late' : 'absent'),
        ])->values();

        $recentHistory = collect($data['records'])->take(10)->map(fn ($r) => [
            'course' => 'Journée',
            'dateInfo' => $r->date->translatedFormat('d M Y'),
            'status' => $r->status === 'present' ? 'PRÉSENT' : ($r->status === 'late' ? 'RETARD' : 'ABSENT'),
        ])->values();

        return response()->json([
            'studentName' => "{$student->first_name} {$student->last_name}",
            'studentAvatarUrl' => $student->photo_path ? asset('storage/'.$student->photo_path) : '',
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

        $dayNames = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
        $weekOffset = (int) $request->query('week_offset', 0);
        $startOfWeek = now()->addWeeks($weekOffset)->startOfWeek();

        $weekDays = collect($dayNames)->map(fn ($name, $i) => [
            'dayName' => ucfirst(substr($name, 0, 3)),
            'date' => $startOfWeek->copy()->addDays($i)->day,
        ])->values();

        $slots = $student->academic_class_id
            ? Timetable::where('academic_class_id', $student->academic_class_id)
                ->where('status', 'published')
                ->with(['subject', 'teacher', 'room'])
                ->orderBy('start_time')
                ->get()
            : collect();

        $todayName = $dayNames[now()->dayOfWeekIso - 1] ?? null;
        $requestedDay = $request->query('day');
        $selectedDayName = in_array($requestedDay, $dayNames, true)
            ? $requestedDay
            : ($weekOffset === 0 && $todayName ? $todayName : 'lundi');
        $selectedDayIndex = array_search($selectedDayName, $dayNames, true);

        $weekEvents = $slots->where('day_of_week', $selectedDayName)->map(fn (Timetable $slot) => [
            'time' => Carbon::parse($slot->start_time)->format('H:i'),
            'subject' => $slot->subject->name ?? '—',
            'teacher' => $slot->teacher ? "{$slot->teacher->first_name} {$slot->teacher->last_name}" : '—',
            'tag' => $slot->room ? 'Salle '.$slot->room->name : '',
            'colorHex' => '0xFF0F3294',
        ])->values();

        $monthEvents = Event::where('school_id', $student->school_id)
            ->where('status', '!=', 'cancelled')
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->limit(5)
            ->get()
            ->map(fn (Event $e) => [
                'time' => $e->start_at->translatedFormat('d M'),
                'subject' => $e->title,
                'teacher' => $e->organizer_name ?? '',
                'tag' => $e->start_at->format('H:i'),
                'colorHex' => '0xFF0F3294',
            ])->values();

        $homework = $this->service->homework($student);

        return response()->json([
            'studentName' => "{$student->first_name} {$student->last_name}",
            'studentAvatarUrl' => $student->photo_path ? asset('storage/'.$student->photo_path) : '',
            'weekLabel' => $startOfWeek->translatedFormat('d M').' – '.$startOfWeek->copy()->addDays(4)->translatedFormat('d M Y'),
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

        $subjects = collect($bulletin['grades'])->map(fn ($g) => [
            'id' => (string) $g->subject->id,
            'title' => $g->subject->name,
            'chapters' => '—',
            'score' => number_format($g->score, 1),
            'iconType' => 'menu_book',
            'colorHex' => '0xFFE0E7FF',
            'iconColorHex' => '0xFF0F3294',
        ])->values();

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
    public function fees(Request $request)
    {
        $type = $request->query('type', 'tuition');
        abort_unless(in_array($type, array_keys(FeeLevel::TYPES), true), 422, 'Type de frais invalide.');

        $student = $this->resolveStudent($request);
        $summary = $this->service->fees($student, $type);

        $lineStatusLabel = fn (string $status) => match ($status) {
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
            'schedule' => collect($summary['schedule'])->map(fn (array $line) => [
                'label' => $line['label'],
                'amount' => (float) $line['amount'],
                'dueDateLabel' => $line['due_date']?->translatedFormat('d M Y') ?? '—',
                'status' => $line['status'],
                'statusLabel' => $lineStatusLabel($line['status']),
            ])->values(),
            'payments' => $payments->map(fn (Payment $payment) => [
                'id' => (string) $payment->id,
                'methodLabel' => Payment::METHODS[$payment->method] ?? $payment->method,
                'amount' => (float) $payment->amount,
                'dateLabel' => $payment->paid_at?->translatedFormat('d M Y') ?? '—',
                'reference' => $payment->reference ?? '',
            ])->values(),
        ]);
    }

    public function canteen(Request $request)
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

        $byDate = collect($data['weekMenu'])->groupBy(fn ($item) => $item->date->toDateString());
        $mealOption = fn ($item) => [
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

            return [
                'dayName' => strtoupper($date->translatedFormat('D')),
                'date' => (string) $date->day,
                'fullDate' => $date->toDateString(),
                // The parent can only order for today or a future day — a
                // day that's already happened can't have its meal changed,
                // so the app disables ordering for it (still viewable).
                'isPast' => $date->isBefore(now()->startOfDay()),
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

        return response()->json([
            'studentName' => "{$student->first_name} {$student->last_name}",
            'studentClass' => $student->academicClass->name ?? '—',
            'studentAvatarUrl' => $student->photo_path ? asset('storage/'.$student->photo_path) : '',
            'weeklyMenu' => $weeklyMenu,
        ]);
    }

    /**
     * Confirms the parent's meal choices for one or more menu items — one
     * reservation per (student, date, slot), so re-confirming a day with a
     * different breakfast/lunch choice replaces the previous one rather than
     * stacking duplicates. This is what makes the canteen "aware" of the
     * order: it's a real row the School Dashboard's reservations page reads.
     */
    public function confirmCanteenOrder(Request $request)
    {
        $request->validate([
            'menu_item_ids' => ['required', 'array', 'min:1'],
            'menu_item_ids.*' => ['integer', 'exists:canteen_menu_items,id'],
        ]);

        $student = $this->resolveStudent($request);

        $items = MenuItem::where('school_id', $student->school_id)
            ->whereIn('id', $request->input('menu_item_ids'))
            ->get();

        // Defense in depth: the app disables ordering for past days, but the
        // API must not trust that — reject regardless of what the client sent.
        abort_if(
            $items->contains(fn (MenuItem $item) => $item->date->isBefore(now()->startOfDay())),
            422,
            "Impossible de commander pour un jour déjà passé."
        );

        foreach ($items as $item) {
            CanteenReservation::updateOrCreate(
                ['student_id' => $student->id, 'date' => $item->date, 'slot' => $item->slot],
                ['school_id' => $student->school_id, 'menu_item_id' => $item->id]
            );
        }

        return response()->json([
            'confirmed' => true,
            'reservedMenuItemIds' => $items->pluck('id')->map(fn ($id) => (string) $id)->values(),
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
            ->groupBy(fn (CanteenReservation $r) => $r->date->toDateString());

        $account = CanteenAccount::where('holder_type', Student::class)->where('holder_id', $student->id)->first();
        $attendedDates = $account
            ? $account->mealRecords()->whereBetween('date', [$from, $to])->pluck('date')->map(fn ($d) => $d->toDateString())->all()
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
                'items' => $reservations->map(fn (CanteenReservation $r) => [
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
     * The Infirmary module has no vaccine model, no per-visit document/prescription
     * attachments, and no structured allergy list — `Student::allergies` is a single
     * free-text field. `prescriptions`/`vaccines` honestly come back empty (no real
     * equivalent), and `allergies` is the real free-text field split into the mock's
     * list-of-objects shape (comma-separated entries, no severity since none is tracked).
     * `visits` are real `Intervention` rows — `indication` maps to `care_notes`,
     * `treatment` to the human label of the real `decision` field.
     */
    public function infirmary(Request $request)
    {
        $student = $this->resolveStudent($request);

        $interventions = Intervention::where('student_id', $student->id)
            ->orderByDesc('arrival_time')
            ->limit(20)
            ->get();

        $decisionLabel = fn (?string $decision) => $decision ? (Intervention::DECISIONS[$decision] ?? $decision) : null;

        $visitTime = function (Carbon $date) {
            if ($date->isToday()) {
                return "Aujourd'hui";
            }
            if ($date->isYesterday()) {
                return 'Hier';
            }
            return $date->translatedFormat('d M Y');
        };

        $visits = $interventions->map(fn (Intervention $i) => [
            'id' => (string) $i->id,
            'type' => $i->motive ?? 'Consultation',
            'date' => $i->arrival_time ? $i->arrival_time->translatedFormat('l d M Y') : '',
            'status' => $i->decision ? 'Terminé' : '',
            'indication' => $i->care_notes ?? '',
            'treatment' => $decisionLabel($i->decision),
        ])->values();

        $last = $interventions->first();
        $lastVisit = [
            'title' => $last ? ($last->motive ?? 'Consultation').($last->temperature ? ' ('.$last->temperature.'°C)' : '') : '',
            'description' => $last?->care_notes ?? '',
            'timeText' => $last?->arrival_time ? $visitTime($last->arrival_time) : '',
        ];

        $allergiesText = trim((string) ($student->allergies ?? ''));
        $allergies = $allergiesText === '' ? collect() : collect(explode(',', $allergiesText))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->values()
            ->map(fn ($name, $index) => [
                'id' => 'al'.$index,
                'name' => $name,
                'severity' => '',
                'notes' => null,
            ]);

        return response()->json([
            'lastVisit' => $lastVisit,
            'visits' => $visits,
            'prescriptions' => [],
            'vaccines' => [],
            'allergies' => $allergies,
        ]);
    }

    /** Real RFID access-control logs (Presence module) for this child, today only. */
    public function access(Request $request)
    {
        $student = $this->resolveStudent($request);

        $logs = AccessLog::where('school_id', $student->school_id)
            ->where('holder_type', Student::class)
            ->where('holder_id', $student->id)
            ->with('accessPoint')
            ->orderBy('occurred_at')
            ->get();

        $todayLogs = $logs->filter(fn (AccessLog $l) => $l->occurred_at->isToday())->values();

        $last = $logs->last();
        $currentStatus = 'unknown';
        if ($last) {
            $currentStatus = $last->action === AccessLog::ACTION_ENTRY ? 'inside' : 'outside';
        }

        $minutesInSchool = 0;
        $openEntry = null;
        foreach ($todayLogs as $log) {
            if ($log->action === AccessLog::ACTION_ENTRY) {
                $openEntry = $log->occurred_at;
            } elseif ($log->action === AccessLog::ACTION_EXIT && $openEntry) {
                $minutesInSchool += $openEntry->diffInMinutes($log->occurred_at, true);
                $openEntry = null;
            }
        }
        if ($openEntry && $currentStatus === 'inside') {
            $minutesInSchool += $openEntry->diffInMinutes(now(), true);
        }

        $timeInSchool = '';
        if ($minutesInSchool > 0) {
            $hours = intdiv($minutesInSchool, 60);
            $minutes = $minutesInSchool % 60;
            $timeInSchool = $hours > 0 ? ($minutes > 0 ? "{$hours}h{$minutes}" : "{$hours}h") : "{$minutes}min";
        }

        return response()->json([
            'currentStatus' => $currentStatus,
            'lastScanTime' => $last?->occurred_at->format('H:i') ?? '',
            'todayEntries' => $todayLogs->where('action', AccessLog::ACTION_ENTRY)->count(),
            'todayExits' => $todayLogs->where('action', AccessLog::ACTION_EXIT)->count(),
            'timeInSchool' => $timeInSchool,
            'history' => $todayLogs->sortByDesc('occurred_at')->values()->map(fn (AccessLog $l) => [
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
            ->where('holder_type', Student::class)
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
            'history' => $logs->map(fn (AccessLog $l) => [
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

    public function transport(Request $request)
    {
        $student = $this->resolveStudent($request);
        $data = $this->service->transport($student);
        $morningStop = $data['morningStop'];
        $eveningStop = $data['eveningStop'];
        abort_if(!$morningStop && !$eveningStop, 404, "Aucun trajet de bus assigné à cet élève.");

        $bus = $data['bus'];
        $driver = $bus?->driver;
        $driverName = $driver ? trim(($driver->first_name ?? '').' '.($driver->last_name ?? '')) : '—';
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

        $hasPosition = $bus && $bus->current_latitude !== null && $bus->current_longitude !== null;
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
            $distance = number_format($km, 1).' km';
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
            'channel' => "transport.student.{$student->id}",
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

    /** Real stop points defined by the school for this student's bus route — used to populate the "modifier/ajouter un arrêt" autocomplete instead of a hardcoded address list. */
    public function transportStops(Request $request)
    {
        $student = $this->resolveStudent($request);

        $data = $this->service->transport($student);
        $route = $data['route'];
        abort_if(!$route, 404, "Aucun trajet de bus assigné à cet élève.");

        $stops = RouteStop::where('route_id', $route->id)->orderBy('sequence')->get();

        return response()->json([
            'stops' => $stops->map(fn (RouteStop $s) => [
                'id' => (string) $s->id,
                'name' => $s->name,
                'address' => $s->address ?? '',
                'arrivalTime' => $s->arrival_time ?? '—',
            ]),
        ]);
    }

    /** Assigns (or reassigns) the student's morning or evening stop on their bus route. */
    public function updateTransportStop(Request $request)
    {
        $request->validate([
            'route_stop_id' => ['required', 'integer'],
            'period' => ['required', 'in:morning,evening'],
        ]);

        $student = $this->resolveStudent($request);
        $period = $request->string('period')->toString();

        $data = $this->service->transport($student);
        $route = $data['route'];
        abort_if(!$route, 404, "Aucun trajet de bus assigné à cet élève.");

        $newStop = RouteStop::where('route_id', $route->id)->findOrFail($request->integer('route_stop_id'));

        DB::table('transport_route_stop_student')
            ->where('student_id', $student->id)
            ->where('period', $period)
            ->delete();
        $newStop->students()->attach($student->id, ['period' => $period]);

        return response()->json([
            'stop' => [
                'id' => (string) $newStop->id,
                'type' => $period === 'morning' ? 'Matin' : 'Soir',
                'locationName' => $newStop->name,
                'time' => $newStop->arrival_time ?? '—',
            ],
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
                $driverName = $driver ? trim(($driver->first_name ?? '').' '.($driver->last_name ?? '')) : '—';

                return [
                    'id' => (string) $trip->id,
                    'title' => 'Trajet '.($isMorning ? 'Matin' : 'Soir'),
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
                ->where('status', 'published')
                ->with('teacher')
                ->first();
            $teacher = $slot?->teacher;

            $syllabus = Syllabus::where('academic_class_id', $student->academic_class_id)
                ->where('subject_id', $subject->id)
                ->where('semester_id', $currentSemester->id)
                ->with('lessons')
                ->first();

            $chapters = $syllabus ? $syllabus->lessons->map(function ($lesson) {
                $subLessons = collect($lesson->lesson_titles ?? []);

                return [
                    'title' => $lesson->title,
                    'subtitle' => $subLessons->isNotEmpty() ? $subLessons->count().' leçon(s)' : '',
                    'progress' => 0.0,
                    'progressText' => '',
                    'iconType' => 'menu_book',
                    'isLocked' => false,
                    'lessons' => $subLessons->map(fn ($title) => [
                        'title' => $title,
                        'completed' => false,
                        'duration' => '',
                    ])->values(),
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

            $notes = $rawNotes->map(fn ($g) => [
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
                $courseAverage = $subjectAverage !== null ? number_format($subjectAverage, 1).'/20' : '—';
            }
        }

        return response()->json([
            'courseTitle' => $subject->name,
            'teacherName' => $teacher ? "{$teacher->first_name} {$teacher->last_name}" : '—',
            'teacherRole' => $teacher->role ?? '',
            'teacherAvatarUrl' => $teacher?->photo_path ? asset('storage/'.$teacher->photo_path) : '',
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
                    'score' => number_format($score, 1).'/20',
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

        return response()->json([
            'studentName' => trim("{$student->first_name} {$student->last_name}"),
            'studentAvatar' => $student->photo_path ? asset('storage/'.$student->photo_path) : '',
            'classInfo' => $student->academicClass->name ?? '—',
            'school' => School::find($student->school_id)?->name ?? '—',
            'rollNumber' => $student->roll_number,
            'gender' => $student->gender === 'female' ? 'Féminin' : 'Masculin',
            'academicYear' => $student->academic_year,
            'generalAverage' => $average !== null ? number_format($average, 1) : '—',
            'averageEvaluation' => $evaluationLabel,
            'attendancePercentage' => $attendancePercentage.'%',
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
            $this->recentPublishedGrades($student)->each(fn (BulletinGrade $g) => $activities->push([
                'sortAt' => $g->created_at,
                'iconType' => 'star',
                'iconColorHex' => '0xFFFFFFFF',
                'iconBgColorHex' => '0xFF2646A6',
                'time' => $g->created_at?->translatedFormat('d M, H:i') ?? '',
                'title' => 'Nouvelle note :',
                'description' => ($g->subject->name ?? '—').' - '.($g->evaluationType->name ?? 'Note'),
                'extraText' => number_format($g->score, 1).'/20',
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
                ->each(fn (AttendanceRecord $a) => $activities->push([
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
                ->each(fn (ReportCardObservation $o) => $activities->push([
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

    /** Same today/yesterday/date convention already used for every other history list in this controller, except "today" shows a time (these are same-day timestamps, not date-only entries). */
    private function relativeNotifTime(Carbon $at): string
    {
        if ($at->isToday()) {
            return $at->format('H:i');
        }
        if ($at->isYesterday()) {
            return 'Hier';
        }

        return $at->translatedFormat('d M Y');
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
                        'description' => "Le règlement de {$label} pour {$child->first_name} est en attente depuis {$daysLate} jour".($daysLate > 1 ? 's' : '').'.',
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
                    'description' => "{$child->first_name} a obtenu ".number_format($g->score, 1)."/20 en ".($g->subject->name ?? '—').$comment,
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
                        'description' => "Le justificatif pour l'absence de {$child->first_name} du ".($a->date?->translatedFormat('d/m') ?? '—').' a été validé.',
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
}

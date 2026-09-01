<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface;
use App\Modules\Academic\Domain\Repositories\RoomRepositoryInterface;
use App\Modules\Academic\Domain\Repositories\TeacherRepositoryInterface;
use App\Modules\Communication\Application\DTOs\CreateEventDTO;
use App\Modules\Communication\Application\DTOs\UpdateEventDTO;
use App\Modules\Communication\Application\Services\EventStatsService;
use App\Modules\Communication\Application\UseCases\CreateEventUseCase;
use App\Modules\Communication\Application\UseCases\DeleteEventUseCase;
use App\Modules\Communication\Application\UseCases\UpdateEventUseCase;
use App\Modules\Communication\Domain\Models\Event;
use App\Modules\Communication\Domain\Repositories\EventRegistrationRepositoryInterface;
use App\Modules\Communication\Domain\Repositories\EventRepositoryInterface;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function dashboard(EventRepositoryInterface $repository, EventStatsService $statsService)
    {
        $schoolId = auth()->user()->school_id;

        $stats = $statsService->dashboardStats($schoolId);
        $typeBreakdown = $statsService->typeBreakdown($schoolId);
        $upcoming = $repository->upcoming(5);

        // Real scheduling gap from the actual upcoming events — replaces a
        // fabricated "15-25 novembre, +22% participation" claim with
        // nothing behind it.
        $scheduleGap = null;
        $sortedUpcoming = $upcoming->sortBy('start_at')->values();
        $cursor = now();
        $largestGapDays = 0;
        $largestGapStart = null;

        foreach ($sortedUpcoming as $event) {
            $gapDays = $cursor->diffInDays($event->start_at, false);
            if ($gapDays > $largestGapDays) {
                $largestGapDays = $gapDays;
                $largestGapStart = $cursor->copy();
            }
            $cursor = $event->end_at ?? $event->start_at;
        }

        if ($largestGapDays >= 7) {
            $scheduleGap = [
                'days' => (int) $largestGapDays,
                'from' => $largestGapStart->format('d/m/Y'),
            ];
        }

        return view('SchoolDashboard::communication.events.dashboard', compact('stats', 'typeBreakdown', 'upcoming', 'scheduleGap'));
    }

    public function calendar(Request $request, EventRepositoryInterface $repository, AcademicClassRepositoryInterface $classRepository)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $current = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $filters = [
            'type' => $request->get('type'),
            'academic_class_id' => $request->get('academic_class_id'),
            'organizer_name' => $request->get('organizer_name'),
        ];

        $events = $repository->forMonth($current->year, $current->month, $filters);
        $classes = $classRepository->all();

        $eventsByDay = $events->groupBy(fn (Event $e) => $e->start_at->format('Y-m-d'));

        $prevMonth = $current->copy()->subMonthNoOverflow()->format('Y-m');
        $nextMonth = $current->copy()->addMonthNoOverflow()->format('Y-m');

        return view('SchoolDashboard::communication.events.calendar', compact(
            'current', 'events', 'eventsByDay', 'classes', 'filters', 'prevMonth', 'nextMonth'
        ));
    }

    public function show($id, EventRepositoryInterface $repository)
    {
        $event = $repository->find($id);
        return view('SchoolDashboard::communication.events.show', compact('event'));
    }

    public function create(AcademicClassRepositoryInterface $classRepository, RoomRepositoryInterface $roomRepository, TeacherRepositoryInterface $teacherRepository)
    {
        $classes = $classRepository->all();
        $rooms = $roomRepository->all();
        $teachers = $teacherRepository->all();

        return view('SchoolDashboard::communication.events.create', compact('classes', 'rooms', 'teachers'));
    }

    /**
     * Real budget/equipment suggestion from this school's own past events of
     * the same type — replaces a static claim that "EduAfrica AI" would
     * pre-fill the form, which no code anywhere actually did.
     */
    public function aiLogisticsSuggestion(\Illuminate\Http\Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $type = $request->get('type');

        $pastEvents = \App\Modules\Communication\Domain\Models\Event::where('school_id', $schoolId)
            ->where('type', $type)
            ->whereNotNull('estimated_budget')
            ->latest('start_at')
            ->limit(10)
            ->get();

        if ($pastEvents->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Pas encore d'événement de ce type enregistré pour proposer une estimation.",
            ]);
        }

        $avgBudget = round($pastEvents->avg('estimated_budget'));
        $commonEquipment = $pastEvents->pluck('equipment_needs')->filter()->countBy()->sortDesc()->keys()->first();

        return response()->json([
            'success' => true,
            'count' => $pastEvents->count(),
            'avg_budget' => $avgBudget,
            'common_equipment' => $commonEquipment,
        ]);
    }

    public function store(Request $request, CreateEventUseCase $useCase)
    {
        $data = $this->validateEvent($request);
        $data['created_by'] = auth()->id();

        $useCase->execute(new CreateEventDTO($data));

        return redirect()->route('school.communication.events.calendar')->with('success', 'Événement créé avec succès !');
    }

    public function edit($id, EventRepositoryInterface $repository, AcademicClassRepositoryInterface $classRepository, RoomRepositoryInterface $roomRepository, TeacherRepositoryInterface $teacherRepository)
    {
        $event = $repository->find($id);
        $classes = $classRepository->all();
        $rooms = $roomRepository->all();
        $teachers = $teacherRepository->all();

        return view('SchoolDashboard::communication.events.create', compact('event', 'classes', 'rooms', 'teachers'));
    }

    public function update(Request $request, $id, UpdateEventUseCase $useCase)
    {
        $data = $this->validateEvent($request);

        $useCase->execute($id, new UpdateEventDTO($data));

        return redirect()->route('school.communication.events.show', $id)->with('success', 'Événement mis à jour avec succès !');
    }

    public function destroy($id, DeleteEventUseCase $useCase)
    {
        $useCase->execute($id);
        return redirect()->route('school.communication.events.calendar')->with('success', 'Événement supprimé avec succès.');
    }

    public function updateAuthorization(Request $request, $eventId, $studentId, EventRepositoryInterface $repository, EventRegistrationRepositoryInterface $registrationRepository)
    {
        $repository->find($eventId);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:pending,validated,refused'],
        ]);

        $registrationRepository->updateAuthorization($eventId, $studentId, $data['status']);

        return back()->with('success', 'Autorisation mise à jour.');
    }

    public function updatePayment(Request $request, $eventId, $studentId, EventRepositoryInterface $repository, EventRegistrationRepositoryInterface $registrationRepository)
    {
        $repository->find($eventId);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:paid,unpaid,na'],
        ]);

        $registrationRepository->updatePayment($eventId, $studentId, $data['status']);

        return back()->with('success', 'Statut de paiement mis à jour.');
    }

    public function exportAttendance($id, EventRepositoryInterface $repository)
    {
        $event = $repository->find($id);

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=appel_' . $id . '_' . date('Y-m-d') . '.csv',
        ];

        $callback = function () use ($event) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Élève', 'Classe', 'Autorisation Parentale', 'Paiement'], ',', '"', '\\');

            foreach ($event->registrations as $registration) {
                $student = $registration->student;
                fputcsv($file, [
                    $student ? $student->first_name . ' ' . $student->last_name : 'N/A',
                    $student && $student->academicClass ? $student->academicClass->name : '-',
                    ['pending' => 'En attente', 'validated' => 'Validé', 'refused' => 'Refusé'][$registration->parental_authorization] ?? $registration->parental_authorization,
                    ['paid' => 'Réglé', 'unpaid' => 'Non réglé', 'na' => 'N/A'][$registration->payment_status] ?? $registration->payment_status,
                ], ',', '"', '\\');
            }

            fclose($file);
        };

        return response()->streamDownload($callback, 'appel_' . $id . '_' . date('Y-m-d') . '.csv', $headers);
    }

    private function validateEvent(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:scolaire,sportif,culturel,administratif'],
            'organizer_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'location_type' => ['required', 'string', 'in:internal,external'],
            'room_id' => ['nullable', 'required_if:location_type,internal', 'exists:rooms,id'],
            'external_address' => ['nullable', 'required_if:location_type,external', 'string', 'max:255'],
            'audience_type' => ['required', 'string', 'in:all,specific_classes,parents_only'],
            'academic_class_ids' => ['nullable', 'required_if:audience_type,specific_classes', 'array'],
            'academic_class_ids.*' => ['exists:academic_classes,id'],
            'estimated_budget' => ['nullable', 'numeric', 'min:0'],
            'equipment_needs' => ['nullable', 'array'],
            'equipment_needs.*' => ['string', 'max:255'],
            'catering_notes' => ['nullable', 'string'],
            'participation_fee' => ['nullable', 'numeric', 'min:0'],
            'bus_count' => ['nullable', 'integer', 'min:0'],
            'bus_capacity' => ['nullable', 'integer', 'min:0'],
            'bus_status' => ['nullable', 'string', 'in:confirmed,pending'],
            'catering_count' => ['nullable', 'integer', 'min:0'],
            'catering_status' => ['nullable', 'string', 'in:confirmed,pending,awaiting'],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => ['exists:teachers,id'],
            'status' => ['required', 'string', 'in:draft,confirmed,pending,cancelled'],
        ]);

        if ($data['location_type'] === 'external') {
            $data['room_id'] = null;
        } else {
            $data['external_address'] = null;
        }

        return $data;
    }
}

<?php

namespace App\Modules\ParentPortal\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Application\Services\ParentPortalAccountService;
use App\Modules\ParentPortal\Application\Services\ParentPortalService;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function addChild(Request $request, ParentPortalAccountService $service)
    {
        $data = $request->validate([
            'school_code' => ['required', 'string'],
            'matricule' => ['required', 'string'],
        ]);

        $claimed = $service->claimChild($request->user(), trim($data['school_code']), trim($data['matricule']));

        if (!$claimed) {
            return response()->json([
                'message' => "Aucune correspondance trouvée. Vérifiez le code établissement et le matricule, ou contactez l'établissement.",
            ], 422);
        }

        return response()->json(['message' => 'Enfant ajouté avec succès.']);
    }

    public function children(Request $request, ParentPortalService $service)
    {
        $children = $service->childrenOf($request->user())->map(fn ($student) => [
            'id' => $student->id,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'roll_number' => $student->roll_number,
            'class' => $student->academicClass?->name,
            'school' => $student->school?->name,
        ]);

        return response()->json(['children' => $children]);
    }

    public function bulletin(Request $request, int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent($request->user(), $student);
        $data = $service->bulletin($child);

        return response()->json([
            'semester' => $data['currentSemester']?->name,
            'published' => $data['isPublished'],
            'average' => $data['average'],
            'rank' => $data['rank'],
            'class_size' => $data['classSize'],
            'subjects' => collect($data['grades'])->map(fn ($g) => [
                'subject' => $g->subject->name,
                'score' => $g->score,
                'remark' => $g->remark,
            ]),
        ]);
    }

    public function attendance(Request $request, int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent($request->user(), $student);
        $data = $service->attendance($child);

        return response()->json([
            'unjustified_absences' => $data['unjustifiedAbsences'],
            'late_count' => $data['lateCount'],
            'attendance_rate' => $data['attendanceRate'],
            'records' => collect($data['records'])->map(fn ($r) => [
                'date' => $r->date->toDateString(),
                'status' => $r->status,
                'justified' => $r->justified,
                'late_minutes' => $r->late_minutes,
            ]),
        ]);
    }

    public function homework(Request $request, int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent($request->user(), $student);
        $data = $service->homework($child);

        return response()->json([
            'assignments' => collect($data['assignments'])->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'type' => $a->type,
                'subject' => $a->subject?->name,
                'scheduled_at' => $a->scheduled_at?->toIso8601String(),
                'status' => $a->submission?->status ?? 'non_remis',
                'score' => $a->submission?->score,
                'max_score' => $a->max_score,
            ]),
        ]);
    }

    public function fees(Request $request, int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent($request->user(), $student);
        $data = $service->fees($child);

        return response()->json([
            'total' => $data['total'],
            'paid' => $data['paid'],
            'remaining' => $data['remaining'],
            'status' => $data['status'],
            'next_due_date' => $data['nextDueDate']?->toDateString(),
        ]);
    }

    public function canteen(Request $request, int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent($request->user(), $student);
        $data = $service->canteen($child);

        return response()->json([
            'balance' => $data['account']?->balance,
            'recent_meals' => collect($data['recentMeals'])->map(fn ($m) => ['date' => $m->date->toDateString(), 'price' => $m->price]),
            'week_menu' => collect($data['weekMenu'])->map(fn ($m) => ['date' => $m->date->toDateString(), 'slot' => $m->slot, 'title' => $m->title]),
        ]);
    }

    public function transport(Request $request, int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent($request->user(), $student);
        $data = $service->transport($child);

        return response()->json([
            'morning_stop' => $data['morningStop']?->name,
            'morning_arrival_time' => $data['morningStop']?->arrival_time,
            'evening_stop' => $data['eveningStop']?->name,
            'evening_arrival_time' => $data['eveningStop']?->arrival_time,
            'route' => $data['route']?->name,
            'bus' => $data['bus']?->bus_number,
        ]);
    }
}

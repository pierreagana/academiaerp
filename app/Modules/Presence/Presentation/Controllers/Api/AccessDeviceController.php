<?php

namespace App\Modules\Presence\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Canteen\Application\Services\CanteenEnrollmentService;
use App\Modules\Presence\Application\UseCases\RecordAccessCheckInUseCase;
use App\Modules\Presence\Domain\Models\AccessDevice;
use App\Modules\SuperAdmin\Domain\Models\School;
use App\Modules\Transport\Application\Services\TransportEnrollmentService;
use App\Modules\Transport\Domain\Models\TransportBoardingScan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * API for the standalone "academia_access_scanner" terminal app — a device
 * that is not a staff User nor a ParentAccount, authenticated the same way
 * (Sanctum token) but scoped to exactly one fixed purpose set by the school
 * admin at creation (see AccessDeviceController in SchoolDashboard for that
 * admin CRUD). The device never chooses or edits its own access_type/gate —
 * it only ever reflects what its token resolves to.
 *
 * The app scans offline too, queuing locally and replaying through /sync
 * once connectivity returns. Every scan (live or queued) carries a
 * client-generated `client_scan_id`; both the single-scan and bulk-sync
 * paths route through processOne() below, which checks that id first —
 * a replayed scan (retry after a dropped response, or a queued scan that
 * made it through some other way in the meantime) returns the original
 * result instead of creating a second log/boarding row.
 */
class AccessDeviceController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'school_code' => ['required', 'string'],
            'device_name' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $school = School::where('code', trim($data['school_code']))->first();

        if (!$school) {
            return response()->json(['message' => "Code établissement introuvable."], 401);
        }

        $device = AccessDevice::where('school_id', $school->id)
            ->where('name', trim($data['device_name']))
            ->first();

        if (!$device || !Hash::check($data['password'], $device->password)) {
            return response()->json(['message' => 'Identifiants appareil incorrects.'], 401);
        }

        if (!$device->is_active) {
            return response()->json(['message' => 'Cet appareil a été désactivé par l\'établissement.'], 403);
        }

        $token = $device->createToken($data['device_name'])->plainTextToken;
        $device->update(['last_used_at' => now()]);

        return response()->json([
            'token' => $token,
            'device' => [
                'id' => (string) $device->id,
                'device_name' => $device->name,
                'access_type' => $device->access_type,
                'access_type_label' => AccessDevice::TYPES[$device->access_type] ?? $device->access_type,
                'gate_name' => $device->label,
            ],
        ]);
    }

    public function scan(
        Request $request,
        RecordAccessCheckInUseCase $checkInUseCase,
        CanteenEnrollmentService $canteenEnrollment,
        TransportEnrollmentService $transportEnrollment
    ) {
        $data = $request->validate([
            'qr_code' => ['required', 'string'],
            'client_scan_id' => ['nullable', 'string', 'max:100'],
        ]);

        /** @var AccessDevice $device */
        $device = $request->user();
        $device->update(['last_used_at' => now()]);

        $result = $this->processOne(
            $device,
            $data['qr_code'],
            $data['client_scan_id'] ?? null,
            null,
            $checkInUseCase,
            $canteenEnrollment,
            $transportEnrollment
        );

        return response()->json($result['body'], $result['status']);
    }

    /**
     * Bulk replay for the offline queue. One HTTP round trip for the whole
     * batch — if the request itself never reaches the server (still
     * offline, or the connection drops mid-upload), nothing in the batch is
     * marked synced and the app retries it whole next time; there's no
     * partial-batch state to reconcile.
     */
    public function sync(
        Request $request,
        RecordAccessCheckInUseCase $checkInUseCase,
        CanteenEnrollmentService $canteenEnrollment,
        TransportEnrollmentService $transportEnrollment
    ) {
        $data = $request->validate([
            'scans' => ['required', 'array', 'max:200'],
            'scans.*.client_scan_id' => ['required', 'string', 'max:100'],
            'scans.*.qr_code' => ['required', 'string'],
            'scans.*.scanned_at' => ['required', 'date'],
        ]);

        /** @var AccessDevice $device */
        $device = $request->user();
        $device->update(['last_used_at' => now()]);

        $results = [];
        foreach ($data['scans'] as $scan) {
            $result = $this->processOne(
                $device,
                $scan['qr_code'],
                $scan['client_scan_id'],
                Carbon::parse($scan['scanned_at']),
                $checkInUseCase,
                $canteenEnrollment,
                $transportEnrollment
            );
            $results[] = array_merge(['client_scan_id' => $scan['client_scan_id']], $result['body']);
        }

        return response()->json(['results' => $results]);
    }

    /** @return array{status:int, body:array} */
    private function processOne(
        AccessDevice $device,
        string $qrCode,
        ?string $clientScanId,
        ?Carbon $occurredAt,
        RecordAccessCheckInUseCase $checkInUseCase,
        CanteenEnrollmentService $canteenEnrollment,
        TransportEnrollmentService $transportEnrollment
    ): array {
        $raw = trim($qrCode);
        $matricule = str_contains($raw, ':') ? substr($raw, strrpos($raw, ':') + 1) : $raw;

        $student = Student::where('school_id', $device->school_id)
            ->where('roll_number', $matricule)
            ->where('status', 'active')
            ->first();

        if (!$student) {
            return ['status' => 422, 'body' => ['message' => 'Élève introuvable pour ce code.']];
        }

        $studentName = trim($student->first_name . ' ' . $student->last_name);

        return match ($device->access_type) {
            'portal_entry', 'portal_exit' => $this->handlePortal($device, $checkInUseCase, $raw, $clientScanId, $occurredAt, $studentName),
            'canteen' => $this->handleCanteen($device, $checkInUseCase, $canteenEnrollment, $student, $raw, $clientScanId, $occurredAt, $studentName),
            'bus_board', 'bus_alight' => $this->handleBus($device, $transportEnrollment, $student, $clientScanId, $occurredAt, $studentName),
            default => ['status' => 500, 'body' => ['message' => "Type d'appareil non configuré."]],
        };
    }

    /** @return array{status:int, body:array} */
    private function handlePortal(AccessDevice $device, RecordAccessCheckInUseCase $useCase, string $raw, ?string $clientScanId, ?Carbon $occurredAt, string $studentName): array
    {
        $action = $device->access_type === 'portal_entry' ? 'entry' : 'exit';
        $log = $useCase->execute($device->school_id, $raw, $action, $device->access_point_id, $device->branch_id, $clientScanId, $occurredAt);

        if (!$log->authorized) {
            return ['status' => 422, 'body' => ['message' => "Accès refusé — élève non reconnu."]];
        }

        return [
            'status' => 200,
            'body' => [
                'message' => $action === 'entry' ? 'Entrée validée' : 'Sortie validée',
                'log' => ['student_name' => $studentName, 'authorized' => true, 'action' => $action],
            ],
        ];
    }

    /** @return array{status:int, body:array} */
    private function handleCanteen(AccessDevice $device, RecordAccessCheckInUseCase $useCase, CanteenEnrollmentService $canteenEnrollment, Student $student, string $raw, ?string $clientScanId, ?Carbon $occurredAt, string $studentName): array
    {
        if (!$canteenEnrollment->isEnrolled($student->id)) {
            $useCase->execute($device->school_id, $raw, 'entry', $device->access_point_id, $device->branch_id, $clientScanId, $occurredAt);
            return ['status' => 422, 'body' => ['message' => "{$studentName} n'est pas inscrit à la cantine."]];
        }

        $useCase->execute($device->school_id, $raw, 'entry', $device->access_point_id, $device->branch_id, $clientScanId, $occurredAt);

        return [
            'status' => 200,
            'body' => [
                'message' => 'Accès cantine validé',
                'log' => ['student_name' => $studentName, 'authorized' => true, 'action' => 'entry'],
            ],
        ];
    }

    /** @return array{status:int, body:array} */
    private function handleBus(AccessDevice $device, TransportEnrollmentService $transportEnrollment, Student $student, ?string $clientScanId, ?Carbon $occurredAt, string $studentName): array
    {
        if ($clientScanId !== null) {
            $existing = TransportBoardingScan::where('client_scan_id', $clientScanId)->first();
            if ($existing) {
                return [
                    'status' => 200,
                    'body' => [
                        'message' => $existing->action === 'board' ? 'Montée enregistrée' : 'Descente enregistrée',
                        'log' => ['student_name' => $studentName, 'authorized' => true, 'action' => $existing->action],
                    ],
                ];
            }
        }

        $scanTime = $occurredAt ?? now();
        $period = $scanTime->hour < 12 ? 'morning' : 'evening';

        if (!$transportEnrollment->isEnrolled($student->id, $period)) {
            return ['status' => 422, 'body' => ['message' => "{$studentName} n'a pas d'inscription bus valide pour ce trajet."]];
        }

        $action = $device->access_type === 'bus_board' ? 'board' : 'alight';

        TransportBoardingScan::create([
            'student_id' => $student->id,
            'bus_id' => $device->bus_id,
            'route_id' => $device->route_id,
            'period' => $period,
            'action' => $action,
            'client_scan_id' => $clientScanId,
            'scanned_at' => $scanTime,
            'scanned_by_device_id' => $device->id,
        ]);

        return [
            'status' => 200,
            'body' => [
                'message' => $action === 'board' ? 'Montée enregistrée' : 'Descente enregistrée',
                'log' => ['student_name' => $studentName, 'authorized' => true, 'action' => $action],
            ],
        ];
    }

    /**
     * A downloadable snapshot of "who's authorized" for this device's exact
     * access_type — the offline answer to "how does the operator know
     * whether to let someone through with no connection?" The app caches
     * this and, when a scan fails to reach the server, looks the matricule
     * up here instead of just queuing blind. Same enrollment services as
     * the live path, so the offline verdict is never a different rule —
     * only ever a stale snapshot of the same one.
     */
    public function roster(Request $request, CanteenEnrollmentService $canteenEnrollment, TransportEnrollmentService $transportEnrollment)
    {
        /** @var AccessDevice $device */
        $device = $request->user();

        $entries = match ($device->access_type) {
            'portal_entry', 'portal_exit' => $this->portalRoster($device->school_id),
            'canteen' => $this->canteenRoster($device->school_id, $canteenEnrollment),
            'bus_board', 'bus_alight' => $this->busRoster($device->school_id, $transportEnrollment),
            default => [],
        };

        return response()->json([
            'accessType' => $device->access_type,
            'generatedAt' => now()->toIso8601String(),
            'entries' => $entries,
        ]);
    }

    private function portalRoster(int $schoolId): array
    {
        $entries = collect();

        $entries = $entries->merge(
            Student::where('school_id', $schoolId)->where('status', 'active')
                ->get(['roll_number', 'first_name', 'last_name'])
                ->map(fn(Student $s) => ['matricule' => $s->roll_number, 'name' => trim($s->first_name . ' ' . $s->last_name)])
        );

        $entries = $entries->merge(
            Teacher::where('school_id', $schoolId)->where('status', 'active')
                ->get(['employee_id', 'first_name', 'last_name'])
                ->map(fn(Teacher $t) => ['matricule' => $t->employee_id, 'name' => trim($t->first_name . ' ' . $t->last_name)])
        );

        $entries = $entries->merge(
            Staff::where('school_id', $schoolId)->where('status', 'active')
                ->get(['employee_id', 'first_name', 'last_name'])
                ->map(fn(Staff $s) => ['matricule' => $s->employee_id, 'name' => trim($s->first_name . ' ' . $s->last_name)])
        );

        return $entries->values()->all();
    }

    private function canteenRoster(int $schoolId, CanteenEnrollmentService $canteenEnrollment): array
    {
        return Student::where('school_id', $schoolId)->where('status', 'active')
            ->get(['id', 'roll_number', 'first_name', 'last_name'])
            ->filter(fn(Student $s) => $canteenEnrollment->isEnrolled($s->id))
            ->map(fn(Student $s) => ['matricule' => $s->roll_number, 'name' => trim($s->first_name . ' ' . $s->last_name)])
            ->values()->all();
    }

    private function busRoster(int $schoolId, TransportEnrollmentService $transportEnrollment): array
    {
        return Student::where('school_id', $schoolId)->where('status', 'active')
            ->get(['id', 'roll_number', 'first_name', 'last_name'])
            ->map(function (Student $s) use ($transportEnrollment) {
                $morning = $transportEnrollment->isEnrolled($s->id, 'morning');
                $evening = $transportEnrollment->isEnrolled($s->id, 'evening');
                if (!$morning && !$evening) {
                    return null;
                }
                return ['matricule' => $s->roll_number, 'name' => trim($s->first_name . ' ' . $s->last_name), 'morning' => $morning, 'evening' => $evening];
            })
            ->filter()
            ->values()->all();
    }

    public function history(Request $request)
    {
        /** @var AccessDevice $device */
        $device = $request->user();

        if (in_array($device->access_type, ['bus_board', 'bus_alight'], true)) {
            $items = TransportBoardingScan::with('student')
                ->where('scanned_by_device_id', $device->id)
                ->latest('scanned_at')
                ->limit(30)
                ->get()
                ->map(fn(TransportBoardingScan $s) => [
                    'student_name' => trim(($s->student->first_name ?? '') . ' ' . ($s->student->last_name ?? '')),
                    'action' => $s->action,
                    'authorized' => true,
                    'occurred_at' => $s->scanned_at?->toIso8601String(),
                ]);

            return response()->json(['history' => $items]);
        }

        $items = \App\Modules\Presence\Domain\Models\AccessLog::where('school_id', $device->school_id)
            ->when($device->access_point_id, fn($q) => $q->where('access_point_id', $device->access_point_id))
            ->latest('occurred_at')
            ->limit(30)
            ->get()
            ->map(fn($log) => [
                'student_name' => $log->person_name,
                'action' => $log->action,
                'authorized' => $log->authorized,
                'occurred_at' => $log->occurred_at?->toIso8601String(),
            ]);

        return response()->json(['history' => $items]);
    }
}

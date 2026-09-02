<?php

namespace App\Support\Notifications;

use App\Modules\Academic\Domain\Models\NotificationLog;
use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\Academic\Domain\Models\Student;

/**
 * Single fan-out point used by every real trigger (attendance, bus, infirmary,
 * fee reminders, canteen window): resolves a student's guardians once, then
 * (a) writes a persisted NotificationLog row per parent — this is what the
 * web bell/list reads, works with zero Firebase configuration — and
 * (b) attempts a real push via FirebasePushService for whatever devices that
 * parent has registered.
 */
class NotificationDispatcher
{
    public function __construct(private FirebasePushService $pushService)
    {
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Modules\Academic\Domain\Models\ParentAccount>
     */
    public function notifyStudentGuardians(Student $student, string $type, string $title, string $body, array $data = [])
    {
        $parents = $student->guardians->load('parentAccount')
            ->pluck('parentAccount')
            ->filter()
            ->unique('id')
            ->values();

        foreach ($parents as $parent) {
            NotificationLog::create([
                'parent_id' => $parent->id,
                'student_id' => $student->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
            ]);
        }

        // $parents came from ->pluck() so it's a plain Support\Collection, not
        // an Eloquent one — no ->load() available, but this only ever fans out
        // to a handful of guardians per student event, so per-parent lazy
        // loading inside sendToParents() is fine (no bulk N+1 concern here).
        $this->pushService->sendToParents($parents, $title, $body, $data);

        return $parents;
    }

    /**
     * Same fan-out as notifyStudentGuardians(), but each guardian's own
     * NotificationPreference is checked via $prefCheck first — a parent who
     * opted out of this specific event type (Paramètres de Notification)
     * is skipped instead of always being notified.
     *
     * @param callable(\App\Modules\Transport\Domain\Models\NotificationPreference):bool $prefCheck
     */
    public function notifyStudentGuardiansIfPreferred(Student $student, callable $prefCheck, string $type, string $title, string $body): void
    {
        $parents = $student->guardians->load('parentAccount')
            ->pluck('parentAccount')
            ->filter()
            ->unique('id');

        foreach ($parents as $parent) {
            if (!$prefCheck($parent->getOrCreateNotificationPreference())) {
                continue;
            }

            $this->notifyParent($parent, $type, $title, $body, [], $student->id);
        }
    }

    /**
     * Same fan-out as notifyStudentGuardians(), but for a single parent
     * directly rather than resolving guardians from a Student — used when
     * the caller has already decided (e.g. per-parent preference gating)
     * which one parent to notify. $studentId is optional context (e.g. a
     * bus-proximity alert still concerns one specific child) purely for the
     * NotificationLog row — it does not change who gets notified.
     */
    public function notifyParent(ParentAccount $parent, string $type, string $title, string $body, array $data = [], ?int $studentId = null): void
    {
        NotificationLog::create([
            'parent_id' => $parent->id,
            'student_id' => $studentId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
        ]);

        $this->pushService->sendToParents([$parent], $title, $body, $data);
    }
}

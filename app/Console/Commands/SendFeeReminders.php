<?php

namespace App\Console\Commands;

use App\Modules\Academic\Domain\Models\NotificationLog;
use App\Modules\Finance\Application\Services\StudentFeeService;
use App\Modules\Finance\Domain\Models\FeeLevel;
use App\Modules\SuperAdmin\Domain\Models\School;
use App\Support\Notifications\NotificationDispatcher;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-fee-reminders')]
#[Description('Notifies parents of students with a late fee payment (tuition, cantine, transport)')]
class SendFeeReminders extends Command
{
    // Reminders repeat daily while overdue, but this stops a second run on
    // the same day (or a manual re-trigger) from double-notifying.
    private const DEDUPE_WINDOW_HOURS = 20;

    public function handle(StudentFeeService $feeService, NotificationDispatcher $notifications): int
    {
        $sent = 0;

        School::whereIn('status', ['actif', 'active'])->select('id')->chunk(50, function ($schools) use ($feeService, $notifications, &$sent) {
            foreach ($schools as $school) {
                foreach (array_keys(FeeLevel::TYPES) as $type) {
                    foreach ($feeService->summaries($school->id, $type) as $summary) {
                        if ($summary['status'] !== 'late' || !$summary['nextDueDate']) {
                            continue;
                        }

                        $student = $summary['student'];

                        $alreadySent = NotificationLog::where('student_id', $student->id)
                            ->where('type', 'fee')
                            ->where('created_at', '>=', now()->subHours(self::DEDUPE_WINDOW_HOURS))
                            ->exists();

                        if ($alreadySent) {
                            continue;
                        }

                        $label = FeeLevel::TYPES[$type];
                        $daysLate = max(1, (int) floor($summary['nextDueDate']->diffInDays(now(), true)));

                        $parents = $notifications->notifyStudentGuardians(
                            $student,
                            'fee',
                            'Paiement en retard',
                            "Le règlement de {$label} pour {$student->first_name} est en attente depuis {$daysLate} jour" . ($daysLate > 1 ? 's' : '') . '.'
                        );

                        $sent += $parents->count();
                    }
                }
            }
        });

        $this->info("{$sent} rappel(s) de scolarité envoyé(s).");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Canteen\Domain\Models\Account;
use App\Support\Notifications\NotificationDispatcher;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-canteen-window-notifications {phase : open or close}')]
#[Description('Notifies parents when the next-day canteen menu selection window opens (18h00) or closes (20h00) — same fixed window already enforced in MobileParentController::confirmCanteenOrder()')]
class SendCanteenWindowNotifications extends Command
{
    public function handle(NotificationDispatcher $notifications): int
    {
        $phase = $this->argument('phase');
        if (!in_array($phase, ['open', 'close'], true)) {
            $this->error("phase doit être 'open' ou 'close'.");
            return self::FAILURE;
        }

        $studentIds = Account::where('holder_type', 'student')->pluck('holder_id')->unique();
        $sent = 0;

        Student::whereIn('id', $studentIds)->chunk(100, function ($students) use ($notifications, $phase, &$sent) {
            foreach ($students as $student) {
                $title = $phase === 'open' ? 'Menu cantine — sélection ouverte' : 'Menu cantine — sélection fermée';
                $body = $phase === 'open'
                    ? "La sélection du menu cantine de demain pour {$student->first_name} est ouverte jusqu'à 20h00."
                    : "La sélection du menu cantine de demain pour {$student->first_name} est maintenant fermée.";

                $parents = $notifications->notifyStudentGuardians($student, 'canteen', $title, $body);
                $sent += $parents->count();
            }
        });

        $this->info("{$sent} notification(s) cantine ({$phase}) envoyée(s).");

        return self::SUCCESS;
    }
}

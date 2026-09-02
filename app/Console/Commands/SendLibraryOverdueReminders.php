<?php

namespace App\Console\Commands;

use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Library\Domain\Models\Loan;
use App\Modules\SuperAdmin\Domain\Models\School;
use App\Support\Notifications\NotificationDispatcher;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('app:send-library-overdue-reminders')]
#[Description('Notifies parents of students with an overdue library loan not yet reminded')]
class SendLibraryOverdueReminders extends Command
{
    public function handle(NotificationDispatcher $notifications): int
    {
        $sent = 0;

        School::whereIn('status', ['actif', 'active'])->select('id')->chunk(50, function ($schools) use ($notifications, &$sent) {
            $schoolIds = $schools->pluck('id');

            Loan::whereIn('school_id', $schoolIds)
                ->whereNull('returned_at')
                ->where('due_at', '<', Carbon::today())
                ->whereNull('reminded_at')
                ->where('borrower_type', 'student')
                // withTrashed: a loan can still be overdue for a book that
                // was since soft-deleted from the catalog (SoftDeletes on Book).
                ->with(['book' => fn ($q) => $q->withTrashed()])
                ->chunk(100, function ($loans) use ($notifications, &$sent) {
                    foreach ($loans as $loan) {
                        $student = Student::find($loan->borrower_id);
                        if (!$student) {
                            continue;
                        }

                        $title = $loan->book?->title ?? 'un livre';
                        $parents = $notifications->notifyStudentGuardians(
                            $student, 'library', 'Livre en retard',
                            "Le livre « {$title} » emprunté par {$student->first_name} devait être rendu le " . $loan->due_at->translatedFormat('d/m/Y') . '.'
                        );

                        $loan->update(['reminded_at' => now()]);
                        $sent += $parents->count();
                    }
                });
        });

        $this->info("{$sent} rappel(s) de bibliothèque envoyé(s).");

        return self::SUCCESS;
    }
}

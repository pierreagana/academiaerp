<?php

namespace App\Modules\Library\Application\UseCases;

use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Library\Domain\Repositories\LoanRepositoryInterface;
use App\Support\Notifications\NotificationDispatcher;

class RemindOverdueLoansUseCase
{
    private LoanRepositoryInterface $repository;

    public function __construct(LoanRepositoryInterface $repository, private NotificationDispatcher $notifications)
    {
        $this->repository = $repository;
    }

    public function execute(): int
    {
        $overdueLoans = $this->repository->overdue();
        $ids = $overdueLoans->pluck('id')->all();

        if (!empty($ids)) {
            $this->repository->markReminded($ids);
        }

        foreach ($overdueLoans as $loan) {
            if ($loan->borrower_type !== 'student') {
                continue;
            }

            $student = Student::find($loan->borrower_id);
            if (!$student) {
                continue;
            }

            // ->book can be null if the book was later soft-deleted from the
            // catalog while still out on loan (SoftDeletes on Book).
            $title = $loan->book()->withTrashed()->first()?->title ?? 'un livre';
            $this->notifications->notifyStudentGuardians(
                $student, 'library', 'Livre en retard',
                "Le livre « {$title} » emprunté par {$student->first_name} devait être rendu le " . $loan->due_at->translatedFormat('d/m/Y') . '.'
            );
        }

        return count($ids);
    }
}

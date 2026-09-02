<?php

namespace App\Modules\Homework\Application\UseCases;

use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Homework\Application\DTOs\CreateHomeworkAssignmentDTO;
use App\Modules\Homework\Domain\Models\HomeworkAssignment;
use App\Modules\Homework\Domain\Repositories\HomeworkAssignmentRepositoryInterface;
use App\Support\Notifications\NotificationDispatcher;

class CreateHomeworkAssignmentUseCase
{
    public function __construct(
        private HomeworkAssignmentRepositoryInterface $repository,
        private NotificationDispatcher $notifications
    ) {}

    public function execute(CreateHomeworkAssignmentDTO $dto): HomeworkAssignment
    {
        $assignment = $this->repository->create($dto->data);

        $label = $assignment->type === HomeworkAssignment::TYPE_TEST ? 'Interrogation' : 'Devoir maison';
        $subject = $assignment->subject?->name ?? '—';
        $title = "Nouveau : {$assignment->title}";
        $body = "{$label} de {$subject}" . ($assignment->scheduled_at ? ' pour le ' . $assignment->scheduled_at->translatedFormat('d/m/Y') : '') . '.';

        Student::where('academic_class_id', $assignment->academic_class_id)
            ->get()
            ->each(fn (Student $student) => $this->notifications->notifyStudentGuardians($student, 'homework', $title, $body));

        return $assignment;
    }
}

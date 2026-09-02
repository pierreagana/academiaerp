<?php

namespace App\Modules\Bulletin\Application\UseCases;

use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Bulletin\Domain\Models\BulletinPublication;
use App\Modules\Bulletin\Domain\Repositories\BulletinPublicationRepositoryInterface;
use App\Support\Notifications\NotificationDispatcher;

class PublishClassUseCase
{
    public function __construct(
        private BulletinPublicationRepositoryInterface $repository,
        private NotificationDispatcher $notifications
    ) {}

    public function execute(int $academicClassId, int $semesterId)
    {
        $publication = $this->repository->updateStatus($academicClassId, $semesterId, [
            'status' => BulletinPublication::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        // wasChanged() guards against re-notifying every parent again if this
        // is a redundant re-publish call (already published, no real transition).
        if ($publication->wasChanged('status')) {
            Student::where('academic_class_id', $academicClassId)
                ->get()
                ->each(fn (Student $student) => $this->notifications->notifyStudentGuardians(
                    $student, 'bulletin', 'Bulletin disponible',
                    "Le bulletin de {$student->first_name} est maintenant disponible."
                ));
        }

        return $publication;
    }
}

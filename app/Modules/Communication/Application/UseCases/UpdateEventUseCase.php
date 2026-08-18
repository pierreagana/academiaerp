<?php

namespace App\Modules\Communication\Application\UseCases;

use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Communication\Application\DTOs\UpdateEventDTO;
use App\Modules\Communication\Domain\Repositories\EventRegistrationRepositoryInterface;
use App\Modules\Communication\Domain\Repositories\EventRepositoryInterface;

class UpdateEventUseCase
{
    private EventRepositoryInterface $repository;
    private EventRegistrationRepositoryInterface $registrationRepository;

    public function __construct(EventRepositoryInterface $repository, EventRegistrationRepositoryInterface $registrationRepository)
    {
        $this->repository = $repository;
        $this->registrationRepository = $registrationRepository;
    }

    public function execute($id, UpdateEventDTO $dto)
    {
        $event = $this->repository->update($id, $dto->data);

        if (isset($dto->data['academic_class_ids']) && is_array($dto->data['academic_class_ids'])) {
            $this->repository->syncClasses($event->id, $dto->data['academic_class_ids']);
        } else {
            $this->repository->syncClasses($event->id, []);
        }

        if (isset($dto->data['teacher_ids']) && is_array($dto->data['teacher_ids'])) {
            $this->repository->syncTeachers($event->id, $dto->data['teacher_ids']);
        } else {
            $this->repository->syncTeachers($event->id, []);
        }

        $this->syncRegistrations($event, $dto->data);

        return $event;
    }

    private function syncRegistrations($event, array $data): void
    {
        $audienceType = $data['audience_type'] ?? 'all';

        if ($audienceType === 'parents_only') {
            $this->registrationRepository->syncForEvent($event, []);
            return;
        }

        $query = Student::where('school_id', $event->school_id);

        if ($audienceType === 'specific_classes') {
            $query->whereIn('academic_class_id', $data['academic_class_ids'] ?? []);
        }

        $this->registrationRepository->syncForEvent($event, $query->pluck('id')->all());
    }
}

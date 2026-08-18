<?php

namespace App\Modules\Academic\Application\UseCases;

use App\Modules\Academic\Application\DTOs\CreateTeacherDTO;
use App\Modules\Academic\Application\Services\PortalAccountService;
use App\Modules\Academic\Domain\Repositories\TeacherRepositoryInterface;

class CreateTeacherUseCase
{
    private TeacherRepositoryInterface $repository;
    private PortalAccountService $portalAccountService;

    public function __construct(TeacherRepositoryInterface $repository, PortalAccountService $portalAccountService)
    {
        $this->repository = $repository;
        $this->portalAccountService = $portalAccountService;
    }

    public function execute(CreateTeacherDTO $dto)
    {
        $plainPassword = $dto->data['password'] ?? null;

        if (!empty($dto->data['password'])) {
            $dto->data['password'] = bcrypt($dto->data['password']);
        }

        $teacher = $this->repository->create($dto->data);

        $this->portalAccountService->sync(
            $teacher,
            'teacher_id',
            [
                'first_name' => $dto->data['first_name'] ?? null,
                'last_name' => $dto->data['last_name'] ?? null,
                'email' => $dto->data['email'] ?? null,
                'login_id' => $dto->data['login_id'] ?? null,
                'role_id' => $dto->data['role_id'] ?? null,
            ],
            $plainPassword,
            $teacher->school_id
        );

        if (isset($dto->data['academic_class_ids']) && is_array($dto->data['academic_class_ids'])) {
            $this->repository->syncClasses($teacher->id, $dto->data['academic_class_ids']);
        }

        if (isset($dto->data['subject_ids']) && is_array($dto->data['subject_ids'])) {
            $this->repository->syncSubjects($teacher->id, $dto->data['subject_ids']);
        }

        if (isset($dto->data['head_teacher_class_ids']) && is_array($dto->data['head_teacher_class_ids'])) {
            \App\Modules\Academic\Domain\Models\AcademicClass::whereIn('id', $dto->data['head_teacher_class_ids'])->update(['head_teacher_id' => $teacher->id]);
        }

        return $teacher;
    }
}

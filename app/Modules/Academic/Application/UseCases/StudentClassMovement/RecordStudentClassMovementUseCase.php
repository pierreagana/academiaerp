<?php

namespace App\Modules\Academic\Application\UseCases\StudentClassMovement;

use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\StudentClassMovement;
use App\Modules\Academic\Domain\Repositories\StudentClassMovementRepositoryInterface;
use App\Modules\Academic\Domain\Repositories\StudentRepositoryInterface;

class RecordStudentClassMovementUseCase
{
    private $movementRepository;
    private $studentRepository;

    public function __construct(
        StudentClassMovementRepositoryInterface $movementRepository,
        StudentRepositoryInterface $studentRepository
    ) {
        $this->movementRepository = $movementRepository;
        $this->studentRepository = $studentRepository;
    }

    public function execute(Student $student, AcademicClass $toClass, string $type, ?string $toAcademicYear = null, ?string $reason = null): StudentClassMovement
    {
        $fromClass = $student->academicClass;

        if (empty($fromClass->level) || empty($toClass->level)) {
            throw new \InvalidArgumentException("Le niveau de la classe d'origine ou de destination n'est pas défini.");
        }

        if ($type === StudentClassMovement::TYPE_TRANSFER) {
            if ($fromClass->id === $toClass->id) {
                throw new \InvalidArgumentException("L'élève est déjà dans cette classe.");
            }
            if ($fromClass->level !== $toClass->level) {
                throw new \InvalidArgumentException("Un transfert ne peut se faire qu'entre classes du même niveau.");
            }
        } elseif ($type === StudentClassMovement::TYPE_PROMOTION) {
            if ($fromClass->level === $toClass->level) {
                throw new \InvalidArgumentException("Une promotion doit se faire vers un niveau différent.");
            }
        } else {
            throw new \InvalidArgumentException('Type de mouvement invalide.');
        }

        $fromAcademicYear = $student->academic_year;
        $newAcademicYear = $toAcademicYear ?: $fromAcademicYear;

        $movement = $this->movementRepository->create([
            'student_id' => $student->id,
            'type' => $type,
            'from_class_id' => $fromClass->id,
            'to_class_id' => $toClass->id,
            'from_academic_year' => $fromAcademicYear,
            'to_academic_year' => $newAcademicYear,
            'reason' => $reason,
            'moved_by' => auth()->id(),
        ]);

        $this->studentRepository->update($student->id, [
            'academic_class_id' => $toClass->id,
            'academic_year' => $newAcademicYear,
            'branch_id' => $toClass->branch_id,
        ]);

        return $movement;
    }
}

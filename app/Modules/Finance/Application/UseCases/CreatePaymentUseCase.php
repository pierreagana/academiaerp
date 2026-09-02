<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Finance\Application\DTOs\CreatePaymentDTO;
use App\Modules\Finance\Domain\Models\FeeLevel;
use App\Modules\Finance\Domain\Repositories\PaymentRepositoryInterface;
use App\Support\Notifications\NotificationDispatcher;

class CreatePaymentUseCase
{
    private PaymentRepositoryInterface $repository;

    public function __construct(PaymentRepositoryInterface $repository, private NotificationDispatcher $notifications)
    {
        $this->repository = $repository;
    }

    public function execute(CreatePaymentDTO $dto)
    {
        $payment = $this->repository->create($dto->data);

        $student = Student::find($dto->data['student_id']);
        if ($student) {
            $label = FeeLevel::TYPES[$dto->data['type']] ?? $dto->data['type'];
            $amount = number_format((float) $dto->data['amount'], 0, ',', ' ');
            $this->notifications->notifyStudentGuardians(
                $student, 'payment', 'Paiement reçu',
                "Un paiement de {$amount} FCFA pour {$label} de {$student->first_name} a été enregistré."
            );
        }

        return $payment;
    }
}

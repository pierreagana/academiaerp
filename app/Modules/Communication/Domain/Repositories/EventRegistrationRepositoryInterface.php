<?php

namespace App\Modules\Communication\Domain\Repositories;

interface EventRegistrationRepositoryInterface
{
    public function syncForEvent($event, array $studentIds);

    public function updateAuthorization($eventId, $studentId, string $status);

    public function updatePayment($eventId, $studentId, string $status);
}

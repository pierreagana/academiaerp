<?php

namespace App\Modules\Communication\Infrastructure\Repositories;

use App\Modules\Communication\Domain\Models\EventRegistration;
use App\Modules\Communication\Domain\Repositories\EventRegistrationRepositoryInterface;

class EloquentEventRegistrationRepository implements EventRegistrationRepositoryInterface
{
    public function syncForEvent($event, array $studentIds)
    {
        $existingStudentIds = $event->registrations()->pluck('student_id')->all();

        $toRemove = array_diff($existingStudentIds, $studentIds);
        if (!empty($toRemove)) {
            $event->registrations()->whereIn('student_id', $toRemove)->delete();
        }

        $toAdd = array_diff($studentIds, $existingStudentIds);
        foreach ($toAdd as $studentId) {
            $event->registrations()->create([
                'student_id' => $studentId,
                'parental_authorization' => 'pending',
                'payment_status' => $event->participation_fee ? 'unpaid' : 'na',
            ]);
        }
    }

    public function updateAuthorization($eventId, $studentId, string $status)
    {
        EventRegistration::where('event_id', $eventId)
            ->where('student_id', $studentId)
            ->update(['parental_authorization' => $status]);
    }

    public function updatePayment($eventId, $studentId, string $status)
    {
        EventRegistration::where('event_id', $eventId)
            ->where('student_id', $studentId)
            ->update(['payment_status' => $status]);
    }
}

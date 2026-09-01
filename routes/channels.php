<?php

use App\Models\User;
use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\ParentPortal\Application\Services\ParentPortalService;
use App\Modules\Transport\Domain\Models\Bus;
use App\Modules\Transport\Domain\Models\Driver;
use App\Modules\Transport\Domain\Models\RouteStop;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * One private channel per bus — not per student, since a bus usually
 * carries several students and the school dashboard also watches it. Three
 * kinds of principal can authenticate here, depending on which guard
 * resolved the request (Sanctum for the parent/driver Flutter apps, the
 * `web` session guard for the staff dashboard): the bus's own driver (who
 * needs to *publish* client events here, not just listen), any parent with
 * a child currently enrolled on this bus, or staff at the bus's school.
 */
Broadcast::channel('transport.bus.{busId}', function ($principal, int $busId) {
    $bus = Bus::find($busId);
    if (!$bus) {
        return false;
    }

    if ($principal instanceof Driver) {
        return $principal->id === $bus->driver_id && $bus->driver_type === 'driver';
    }

    if ($principal instanceof User) {
        return $principal->school_id === $bus->school_id;
    }

    if ($principal instanceof ParentAccount) {
        $childIds = app(ParentPortalService::class)->childrenOf($principal)->pluck('id');

        return RouteStop::whereHas('route', fn ($q) => $q->where('bus_id', $busId))
            ->whereHas('students', fn ($q) => $q->whereIn('students.id', $childIds))
            ->exists();
    }

    return false;
});

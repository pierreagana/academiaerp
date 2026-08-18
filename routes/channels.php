<?php

use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\ParentPortal\Application\Services\ParentPortalService;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/** Only the parent(s) actually linked to this student may listen for their bus's live position. */
Broadcast::channel('transport.student.{studentId}', function (ParentAccount $parent, int $studentId) {
    return app(ParentPortalService::class)->childrenOf($parent)->contains('id', $studentId);
});

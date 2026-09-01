<?php

use App\Modules\SuperAdmin\Domain\Models\School;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Every school needs a `code` — it's half of what the ID-card QR codes
     * encode ("{code}:{matricule}", see CardController::printCards /
     * MobileParentController::studentProfile). School::boot() now generates
     * one for every newly created school, but schools created before that
     * hook existed were left with a null code, silently dropping the school
     * half of the QR content.
     */
    public function up(): void
    {
        School::withTrashed()->whereNull('code')->orWhere('code', '')->each(function (School $school) {
            $school->code = School::generateUniqueCode($school->name ?? '');
            $school->saveQuietly();
        });
    }

    public function down(): void
    {
        // Not reversible — we don't know which codes were auto-generated
        // versus already set before this migration ran.
    }
};

<?php

namespace App\Modules\Academic\Application\Services;

use App\Modules\Academic\Domain\Models\Branch;
use App\Modules\SuperAdmin\Domain\Models\School;
use Illuminate\Support\Facades\DB;

/**
 * Materializes the school's own record as its first ("Principale") branch the
 * moment it becomes multi-campus (founder), reusing what's already on the
 * School row instead of leaving the admin to re-type it. Idempotent — a
 * school that already has a branch (e.g. one created manually before
 * upgrading) is left untouched.
 */
class ProvisionMainBranchService
{
    public function handle(School $school): ?Branch
    {
        if (Branch::where('school_id', $school->id)->exists()) {
            return null;
        }

        $branch = Branch::create([
            'school_id' => $school->id,
            'name' => $school->name,
            // Same code as the school, not a freshly generated one: this branch
            // *is* the school's own record materialized as its main campus, not
            // a distinct additional site — a manually-added second branch still
            // gets its own independent code via BranchController::store().
            'code' => $school->code,
            'type' => $school->type,
            'sector' => $school->sector,
            'language_regime' => $school->language_regime,
            'city' => $school->city,
            'country' => $school->country,
            'contact_email' => $school->contact_email,
            'contact_phone' => $school->contact_phone,
            'address' => $school->location,
            'latitude' => $school->latitude,
            'longitude' => $school->longitude,
            'slogan' => $school->slogan,
            'is_main' => true,
        ]);

        foreach (['academic_classes', 'students', 'teachers', 'school_staff'] as $table) {
            DB::table($table)->where('school_id', $school->id)->whereNull('branch_id')->update(['branch_id' => $branch->id]);
        }

        return $branch;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // `cycle` used to be picked independently of `level` (with placeholder
        // "Cycle 1/2/3" values that were never actually set on real classes) — it's
        // now always derived from `level`. Backfill existing rows so classes created
        // before this change immediately get real, usable Livret Scolaire filtering.
        foreach (\App\Modules\Academic\Domain\Models\AcademicClass::LEVELS_BY_CYCLE as $cycle => $levels) {
            \Illuminate\Support\Facades\DB::table('academic_classes')
                ->whereIn('level', $levels)
                ->update(['cycle' => $cycle]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('academic_classes')->update(['cycle' => null]);
    }
};

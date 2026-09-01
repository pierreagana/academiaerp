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
        Schema::table('schools', function (Blueprint $table) {
            // Self-reported year-over-year progression (e.g. results, admissions),
            // -100 to 100. Same "self-filled via School Dashboard, null until the
            // school sets it" pattern as `success_rate` — never fabricated.
            $table->smallInteger('progression_annuelle')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('progression_annuelle');
        });
    }
};

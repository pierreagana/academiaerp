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
        Schema::table('homework_assignments', function (Blueprint $table) {
            $table->decimal('max_score', 5, 2)->default(20)->after('duration_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homework_assignments', function (Blueprint $table) {
            $table->dropColumn('max_score');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Existing rows default to 'morning' — they predate the morning/evening
     * distinction and were displayed as "Matin" in the app.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('transport_route_stop_student', 'period')) {
            Schema::table('transport_route_stop_student', function (Blueprint $table) {
                $table->string('period')->default('morning')->after('student_id');
            });
        }

        // MySQL needs a plain index on route_stop_id to keep enforcing its FK
        // once the composite unique index (which currently covers that role) is dropped.
        Schema::table('transport_route_stop_student', function (Blueprint $table) {
            $table->index('route_stop_id', 'trss_route_stop_id_index');
        });

        Schema::table('transport_route_stop_student', function (Blueprint $table) {
            $table->dropUnique('transport_route_stop_student_route_stop_id_student_id_unique');
            $table->unique(['student_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::table('transport_route_stop_student', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'period']);
            $table->unique(['route_stop_id', 'student_id']);
            $table->dropIndex('trss_route_stop_id_index');
            $table->dropColumn('period');
        });
    }
};

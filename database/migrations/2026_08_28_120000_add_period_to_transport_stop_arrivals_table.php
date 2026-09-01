<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A route's stops are shared between its morning and evening trip, but
     * "arrived" is a per-trip fact — without this, confirming arrival in
     * the morning made the evening trip's stops show as already arrived
     * too (same route_stop_id, same day, no way to tell the runs apart).
     */
    public function up(): void
    {
        Schema::table('transport_stop_arrivals', function (Blueprint $table) {
            $table->string('period')->nullable()->after('driver_id');
        });
    }

    public function down(): void
    {
        Schema::table('transport_stop_arrivals', function (Blueprint $table) {
            $table->dropColumn('period');
        });
    }
};

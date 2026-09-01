<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_routes', function (Blueprint $table) {
            // Null = applies to both periods (today's implicit behavior for
            // every existing route). Only set when a school adds a second,
            // period-specific route on the same bus.
            $table->string('period')->nullable()->after('bus_id');
        });
    }

    public function down(): void
    {
        Schema::table('transport_routes', function (Blueprint $table) {
            $table->dropColumn('period');
        });
    }
};

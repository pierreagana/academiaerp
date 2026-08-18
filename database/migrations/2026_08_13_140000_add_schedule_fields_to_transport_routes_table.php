<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_routes', function (Blueprint $table) {
            $table->time('first_stop_time')->nullable()->after('distance_km');
            $table->unsignedInteger('stop_interval_minutes')->nullable()->after('first_stop_time');
            $table->string('schedule_type')->default('recurring')->after('stop_interval_minutes');
            $table->json('recurring_days')->nullable()->after('schedule_type');
        });
    }

    public function down(): void
    {
        Schema::table('transport_routes', function (Blueprint $table) {
            $table->dropColumn(['first_stop_time', 'stop_interval_minutes', 'schedule_type', 'recurring_days']);
        });
    }
};

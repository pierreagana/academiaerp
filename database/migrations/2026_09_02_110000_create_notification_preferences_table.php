<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per parent. Defaults reproduce the reference bus-tracking
     * app's own on/off states exactly (near_pickup off, next-stop/arrived-
     * pickup on, everything else off) — not a considered product default,
     * just faithful replication of what was asked for.
     */
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->unique()->constrained('parent_accounts')->cascadeOnDelete();
            // null = Off; otherwise one of 100/500/1000/1500/2000 (meters).
            $table->unsignedInteger('near_pickup_distance_m')->nullable();
            $table->boolean('next_stop_is_pickup')->default(true);
            $table->boolean('bus_arrived_pickup')->default(true);
            $table->boolean('student_picked_up')->default(false);
            $table->boolean('student_missed_pickup')->default(false);
            // No distance picker for dropoff in the reference — a fixed
            // threshold applies when this is on (see BusProximityService).
            $table->boolean('near_dropoff_enabled')->default(false);
            $table->boolean('bus_arrived_dropoff')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A per-tick history of a bus's real position, written alongside the
     * durable path of DriverController::updatePosition — the source for
     * "replay a bus's day" on the school dashboard. Reverb client events
     * (the fast websocket path) are never persisted, so this table only
     * ever gets one row per HTTP position update, not one per GPS tick that
     * only went out over the websocket.
     */
    public function up(): void
    {
        Schema::create('transport_bus_position_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')->constrained('transport_buses')->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['bus_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_bus_position_logs');
    }
};

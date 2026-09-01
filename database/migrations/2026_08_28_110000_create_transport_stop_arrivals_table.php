<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per bus reaching a stop — the "next stop" signal for the
     * driver app's running-trip screen, distinct from
     * transport_boarding_scans (which tracks which students got on/off, not
     * when the bus itself arrived).
     */
    public function up(): void
    {
        Schema::create('transport_stop_arrivals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_stop_id')->constrained('transport_route_stops')->cascadeOnDelete();
            $table->foreignId('route_id')->constrained('transport_routes')->cascadeOnDelete();
            $table->foreignId('bus_id')->constrained('transport_buses')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('transport_drivers')->nullOnDelete();
            $table->timestamp('arrived_at');
            $table->timestamps();

            $table->index(['route_stop_id', 'arrived_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_stop_arrivals');
    }
};

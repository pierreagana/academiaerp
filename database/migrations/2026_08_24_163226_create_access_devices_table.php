<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A fixed-purpose scanner terminal (gate tablet, canteen reader, bus
     * driver's phone). Its access_type/access_point/bus/route are set once
     * by the school admin at creation and are read-only from the scanner
     * app itself — the app only ever sees what its token resolves to.
     */
    public function up(): void
    {
        Schema::create('access_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('password');
            $table->enum('access_type', ['portal_entry', 'portal_exit', 'canteen', 'bus_board', 'bus_alight']);
            $table->foreignId('access_point_id')->nullable()->constrained('access_points')->nullOnDelete();
            $table->foreignId('bus_id')->nullable()->constrained('transport_buses')->nullOnDelete();
            $table->foreignId('route_id')->nullable()->constrained('transport_routes')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_devices');
    }
};

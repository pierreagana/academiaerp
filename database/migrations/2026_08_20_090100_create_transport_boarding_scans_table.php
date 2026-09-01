<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A per-student boarding event, created by the new bus "Scanner" screen.
     * Distinct from `transport_trip_logs`, which only ever recorded a
     * per-trip aggregate count — nothing before this tracked which specific
     * student boarded.
     */
    public function up(): void
    {
        Schema::create('transport_boarding_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bus_id')->nullable()->constrained('transport_buses')->nullOnDelete();
            $table->foreignId('route_id')->nullable()->constrained('transport_routes')->nullOnDelete();
            $table->string('period'); // morning|evening
            $table->timestamp('scanned_at');
            $table->foreignId('scanned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_boarding_scans');
    }
};

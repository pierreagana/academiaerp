<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_trip_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('route_id')->nullable()->constrained('transport_routes')->nullOnDelete();
            $table->foreignId('bus_id')->nullable()->constrained('transport_buses')->nullOnDelete();
            $table->string('shift')->default('matin');
            $table->date('trip_date');
            $table->time('scheduled_start')->nullable();
            $table->string('status')->default('complete');
            $table->unsignedInteger('attendance_count')->nullable();
            $table->unsignedInteger('expected_count')->nullable();
            $table->decimal('distance_km', 6, 2)->nullable();
            $table->text('incident_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_trip_logs');
    }
};

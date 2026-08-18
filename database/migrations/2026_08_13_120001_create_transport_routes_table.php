<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('zone')->nullable();
            $table->decimal('distance_km', 6, 2)->nullable();
            $table->foreignId('bus_id')->nullable()->constrained('transport_buses')->nullOnDelete();
            $table->string('status')->default('actif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_routes');
    }
};

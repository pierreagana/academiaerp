<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_buses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('bus_number');
            $table->string('plate_number')->nullable();
            $table->string('status')->default('disponible');
            $table->unsignedInteger('capacity')->default(0);
            $table->string('driver_type')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_buses');
    }
};

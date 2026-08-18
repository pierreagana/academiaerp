<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_route_stop_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_stop_id')->constrained('transport_route_stops')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['route_stop_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_route_stop_student');
    }
};

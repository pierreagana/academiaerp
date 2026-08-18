<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_session_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('timetable_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->dateTime('checked_in_at');
            $table->unsignedInteger('late_minutes')->default(0);
            $table->timestamps();

            $table->unique(['teacher_id', 'timetable_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_session_checkins');
    }
};

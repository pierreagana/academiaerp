<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('timetable_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            // Recess/lunch times differ per class and per day (mirrors how `timetables`
            // itself is scoped) — a break is a concrete (class, day) placement, not a
            // single school-wide slot.
            $table->foreignId('academic_class_id')->constrained()->onDelete('cascade');
            $table->string('day_of_week');
            $table->string('name');
            $table->string('color')->default('slate');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_breaks');
    }
};

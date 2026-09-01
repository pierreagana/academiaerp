<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An exam session is one school's real, validated result set for one
     * official exam (bac/bepc/cepe/bts) in one academic year: which classes
     * sat it, and — via `exam_session_students` — exactly who passed.
     * `presented_count`/`admitted_count` are denormalized snapshots taken at
     * validation time so historical rates stay stable even if the class
     * roster changes afterward.
     */
    public function up(): void
    {
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('exam_type'); // bac | bepc | cepe | bts
            $table->string('academic_year'); // e.g. "2025-2026"
            $table->unsignedInteger('presented_count')->default(0);
            $table->unsignedInteger('admitted_count')->default(0);
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['school_id', 'exam_type', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};

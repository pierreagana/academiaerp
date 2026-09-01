<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per student presented for an exam session — every student in
     * the session's selected classes gets a row, `is_admitted` marks the
     * ones the school picked as having passed. `presented_count` /
     * `admitted_count` on `exam_sessions` are just counts of these rows,
     * kept denormalized for cheap reads.
     */
    public function up(): void
    {
        Schema::create('exam_session_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_admitted')->default(false);
            $table->timestamps();

            $table->unique(['exam_session_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_session_students');
    }
};

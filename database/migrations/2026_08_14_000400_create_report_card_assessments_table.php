<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_card_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained('report_card_competencies')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('level', ['acquis', 'en_cours', 'non_acquis']);
            $table->foreignId('assessed_by')->nullable()->constrained('teachers')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'competency_id', 'semester_id'], 'rc_assessments_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_assessments');
    }
};

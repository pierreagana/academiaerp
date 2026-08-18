<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulletin_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('score', 4, 2);
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'semester_id'], 'bulletin_grades_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletin_grades');
    }
};

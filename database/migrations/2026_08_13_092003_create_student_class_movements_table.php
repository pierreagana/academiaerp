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
        Schema::create('student_class_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['transfer', 'promotion']);
            $table->foreignId('from_class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->foreignId('to_class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->string('from_academic_year');
            $table->string('to_academic_year');
            $table->text('reason')->nullable();
            $table->foreignId('moved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_class_movements');
    }
};

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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('dob');
            $table->enum('gender', ['male', 'female']);
            $table->string('email')->nullable();
            $table->string('photo_path')->nullable();
            $table->foreignId('academic_class_id')->constrained()->cascadeOnDelete();
            $table->string('academic_year');
            $table->string('roll_number')->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

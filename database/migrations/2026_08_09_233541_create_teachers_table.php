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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('employee_id')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('main_subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->enum('employment_type', ['full_time', 'part_time'])->default('full_time');
            $table->enum('status', ['active', 'on_leave', 'inactive'])->default('active');
            $table->string('photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};

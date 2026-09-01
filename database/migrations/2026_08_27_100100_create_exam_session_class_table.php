<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Which classes were selected as "sitting this exam" for a given session. */
    public function up(): void
    {
        Schema::create('exam_session_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_class_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['exam_session_id', 'academic_class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_session_class');
    }
};

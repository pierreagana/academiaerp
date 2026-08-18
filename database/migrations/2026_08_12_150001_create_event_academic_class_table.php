<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_academic_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_class_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['event_id', 'academic_class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_academic_class');
    }
};

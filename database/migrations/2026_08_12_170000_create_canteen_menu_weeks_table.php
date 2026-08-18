<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canteen_menu_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->date('week_start_date');
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'week_start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canteen_menu_weeks');
    }
};

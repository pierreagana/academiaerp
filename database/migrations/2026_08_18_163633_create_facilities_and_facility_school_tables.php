<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('facilities')) {
            Schema::create('facilities', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('icon')->default('ph-buildings');
                $table->string('category')->default('Général');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('facility_school')) {
            Schema::create('facility_school', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['school_id', 'facility_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_school');
        Schema::dropIfExists('facilities');
    }
};

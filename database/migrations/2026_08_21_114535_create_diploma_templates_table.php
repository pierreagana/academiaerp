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
        Schema::create('diploma_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('title')->default('DIPLÔME');
            $table->string('subtitle')->default('Décerné à');
            $table->text('body_text')->nullable();
            $table->string('orientation')->default('landscape');
            $table->string('border_style')->default('classic');
            $table->string('primary_color')->default('#031C5B');
            $table->string('background_color')->default('#FFFFFF');
            $table->string('text_color')->default('#0F172A');
            $table->string('logo_path')->nullable();
            $table->string('seal_path')->nullable();
            $table->string('signature_1_name')->nullable();
            $table->string('signature_1_title')->nullable();
            $table->string('signature_2_name')->nullable();
            $table->string('signature_2_title')->nullable();
            $table->timestamps();

            $table->unique('school_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diploma_templates');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('card_type');
            $table->string('logo_path')->nullable();
            $table->string('primary_color')->default('#031C5B');
            $table->string('photo_position')->default('left');
            $table->json('front_fields')->nullable();
            $table->json('back_fields')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'card_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_templates');
    }
};

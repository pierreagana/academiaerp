<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulletin_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name')->default('Modèle Standard');
            $table->boolean('show_coefficient')->default(true);
            $table->boolean('show_class_average')->default(true);
            $table->boolean('show_highest_lowest')->default(false);
            $table->boolean('show_ranking')->default(false);
            $table->boolean('show_signature_area')->default(true);
            $table->boolean('suggested_remarks_enabled')->default(false);
            $table->timestamps();

            $table->unique('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletin_templates');
    }
};

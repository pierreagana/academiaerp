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
        Schema::table('diploma_templates', function (Blueprint $table) {
            $table->string('layout')->default('classic')->after('border_style');
            $table->string('background_image_path')->nullable()->after('seal_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diploma_templates', function (Blueprint $table) {
            $table->dropColumn(['layout', 'background_image_path']);
        });
    }
};

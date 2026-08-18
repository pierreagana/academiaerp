<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_templates', function (Blueprint $table) {
            $table->string('orientation')->default('portrait')->after('photo_position');
            $table->string('background_color')->default('#FFFFFF')->after('primary_color');
            $table->string('text_color')->default('#0F172A')->after('background_color');
            $table->string('watermark_path')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('card_templates', function (Blueprint $table) {
            $table->dropColumn(['orientation', 'background_color', 'text_color', 'watermark_path']);
        });
    }
};

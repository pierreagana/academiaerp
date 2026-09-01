<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (!Schema::hasColumn('schools', 'sector')) {
                $table->string('sector')->nullable()->default('Privé')->after('type');
            }
            if (!Schema::hasColumn('schools', 'is_bilingual')) {
                $table->boolean('is_bilingual')->default(false)->after('sector');
            }
            if (!Schema::hasColumn('schools', 'language_regime')) {
                $table->string('language_regime')->nullable()->default('Monolingue (Français)')->after('is_bilingual');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['sector', 'is_bilingual', 'language_regime']);
        });
    }
};

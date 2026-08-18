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
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('language_id')->nullable()->after('school_id')->constrained('languages')->onDelete('set null');
            $table->decimal('coefficient', 4, 1)->default(1.0)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->dropColumn(['language_id', 'coefficient']);
        });
    }
};

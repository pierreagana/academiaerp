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
            $table->dropForeign(['school_id']);
            $table->dropUnique('diploma_templates_school_id_unique');
        });

        Schema::table('diploma_templates', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreignId('award_type_id')->nullable()->after('school_id')->constrained()->cascadeOnDelete();
            $table->unique(['school_id', 'award_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diploma_templates', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'award_type_id']);
            $table->dropConstrainedForeignId('award_type_id');
            $table->dropForeign(['school_id']);
        });

        Schema::table('diploma_templates', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->unique('school_id');
        });
    }
};

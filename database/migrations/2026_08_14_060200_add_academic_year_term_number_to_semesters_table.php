<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->string('academic_year')->nullable()->after('school_id');
            $table->unsignedTinyInteger('term_number')->nullable()->after('academic_year');
        });
    }

    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropColumn(['academic_year', 'term_number']);
        });
    }
};

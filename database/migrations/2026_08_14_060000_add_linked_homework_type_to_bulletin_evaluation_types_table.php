<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulletin_evaluation_types', function (Blueprint $table) {
            $table->string('linked_homework_type')->nullable()->after('coefficient');
        });
    }

    public function down(): void
    {
        Schema::table('bulletin_evaluation_types', function (Blueprint $table) {
            $table->dropColumn('linked_homework_type');
        });
    }
};

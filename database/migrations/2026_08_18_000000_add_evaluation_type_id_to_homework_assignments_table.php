<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homework_assignments', function (Blueprint $table) {
            $table->foreignId('evaluation_type_id')
                ->nullable()
                ->after('semester_id')
                ->constrained('bulletin_evaluation_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('homework_assignments', function (Blueprint $table) {
            $table->dropForeign(['evaluation_type_id']);
            $table->dropColumn('evaluation_type_id');
        });
    }
};

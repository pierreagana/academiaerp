<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // bulletin_grades_unique (student_id, subject_id, semester_id) is the only index
        // currently backing student_id's FK — the new composite unique (added first, below)
        // also leads with student_id and so can take over that role before the old one is
        // dropped, otherwise MySQL refuses to drop an index still required by a FK.
        Schema::table('bulletin_grades', function (Blueprint $table) {
            $table->foreignId('evaluation_type_id')->after('teacher_id')->nullable()->constrained('bulletin_evaluation_types')->cascadeOnDelete();
        });

        Schema::table('bulletin_grades', function (Blueprint $table) {
            $table->unique(['student_id', 'subject_id', 'semester_id', 'evaluation_type_id'], 'bulletin_grades_type_unique');
        });

        Schema::table('bulletin_grades', function (Blueprint $table) {
            $table->dropUnique('bulletin_grades_unique');
        });

        Schema::table('bulletin_grades', function (Blueprint $table) {
            $table->foreignId('evaluation_type_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('bulletin_grades', function (Blueprint $table) {
            $table->unique(['student_id', 'subject_id', 'semester_id'], 'bulletin_grades_unique');
        });

        Schema::table('bulletin_grades', function (Blueprint $table) {
            $table->dropUnique('bulletin_grades_type_unique');
            $table->dropConstrainedForeignId('evaluation_type_id');
        });
    }
};

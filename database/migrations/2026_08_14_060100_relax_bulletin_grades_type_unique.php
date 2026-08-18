<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A subject can now have several grades of the same evaluation type (multiple
     * interrogations, multiple devoirs...). bulletin_grades_type_unique is the only
     * index leading with student_id (backing that FK) — add a non-unique replacement
     * with the same column order first, then drop the unique, same ordering trick as
     * the previous migration on this table (MySQL refuses to drop an index a FK still
     * needs with no other index to fall back on).
     */
    public function up(): void
    {
        Schema::table('bulletin_grades', function (Blueprint $table) {
            $table->index(['student_id', 'subject_id', 'semester_id', 'evaluation_type_id'], 'bulletin_grades_lookup');
        });

        Schema::table('bulletin_grades', function (Blueprint $table) {
            $table->dropUnique('bulletin_grades_type_unique');
        });
    }

    public function down(): void
    {
        Schema::table('bulletin_grades', function (Blueprint $table) {
            $table->unique(['student_id', 'subject_id', 'semester_id', 'evaluation_type_id'], 'bulletin_grades_type_unique');
        });

        Schema::table('bulletin_grades', function (Blueprint $table) {
            $table->dropIndex('bulletin_grades_lookup');
        });
    }
};

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
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('academic_class_id')->constrained('branches')->nullOnDelete();
        });

        Schema::table('access_logs', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('school_id')->constrained('branches')->nullOnDelete();
        });

        // Backfill from the class's branch (attendance) / the school's main branch (access logs, since
        // there is no retroactive way to know which branch recorded a past scan).
        $records = \Illuminate\Support\Facades\DB::table('attendance_records')->get(['id', 'academic_class_id']);
        foreach ($records as $record) {
            $branchId = \Illuminate\Support\Facades\DB::table('academic_classes')->where('id', $record->academic_class_id)->value('branch_id');
            if ($branchId) {
                \Illuminate\Support\Facades\DB::table('attendance_records')->where('id', $record->id)->update(['branch_id' => $branchId]);
            }
        }

        $schoolIds = \Illuminate\Support\Facades\DB::table('access_logs')->distinct()->pluck('school_id');
        foreach ($schoolIds as $schoolId) {
            $mainBranchId = \Illuminate\Support\Facades\DB::table('branches')->where('school_id', $schoolId)->where('is_main', true)->value('id');
            if ($mainBranchId) {
                \Illuminate\Support\Facades\DB::table('access_logs')->where('school_id', $schoolId)->update(['branch_id' => $mainBranchId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('access_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};

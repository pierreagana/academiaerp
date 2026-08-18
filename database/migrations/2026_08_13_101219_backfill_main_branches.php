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
        $schoolIds = \Illuminate\Support\Facades\DB::table('schools')->pluck('id');

        foreach ($schoolIds as $schoolId) {
            $branchId = \Illuminate\Support\Facades\DB::table('branches')->insertGetId([
                'school_id' => $schoolId,
                'name' => 'Branche Principale',
                'is_main' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach (['academic_classes', 'students', 'teachers', 'school_staff'] as $table) {
                \Illuminate\Support\Facades\DB::table($table)->where('school_id', $schoolId)->update(['branch_id' => $branchId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['academic_classes', 'students', 'teachers', 'school_staff'] as $table) {
            \Illuminate\Support\Facades\DB::table($table)->update(['branch_id' => null]);
        }

        \Illuminate\Support\Facades\DB::table('branches')->where('is_main', true)->where('name', 'Branche Principale')->delete();
    }
};

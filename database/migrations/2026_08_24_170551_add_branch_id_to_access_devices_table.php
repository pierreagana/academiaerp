<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Without this, scanner-created AccessLog rows all had branch_id=null and
     * silently disappeared from the admin's journal whenever they weren't on
     * "Vue Globale" — whereBranch() matches null branch_id only when the
     * filter itself is null. Devices now inherit the branch the admin was on
     * when creating them, same as every other whereBranch()-scoped resource.
     */
    public function up(): void
    {
        Schema::table('access_devices', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('school_id')->constrained('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('access_devices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};

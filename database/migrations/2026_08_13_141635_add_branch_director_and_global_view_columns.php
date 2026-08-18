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
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('is_branch_director')->default(false)->after('name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('view_all_branches')->default(false)->after('current_branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('is_branch_director');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('view_all_branches');
        });
    }
};

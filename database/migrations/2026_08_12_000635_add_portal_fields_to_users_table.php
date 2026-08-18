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
        Schema::table('users', function (Blueprint $table) {
            $table->string('login_id')->nullable()->unique()->after('email');
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->after('role_id')->constrained('teachers')->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->after('teacher_id')->constrained('school_staff')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('staff_id');
            $table->dropConstrainedForeignId('teacher_id');
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn('login_id');
        });
    }
};

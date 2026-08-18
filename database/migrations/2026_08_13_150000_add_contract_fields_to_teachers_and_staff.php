<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('contract_type')->default('cdi')->after('employment_type');
            $table->date('contract_end_date')->nullable()->after('contract_type');
        });

        Schema::table('school_staff', function (Blueprint $table) {
            $table->string('contract_type')->default('cdi')->after('role');
            $table->date('contract_end_date')->nullable()->after('contract_type');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['contract_type', 'contract_end_date']);
        });

        Schema::table('school_staff', function (Blueprint $table) {
            $table->dropColumn(['contract_type', 'contract_end_date']);
        });
    }
};

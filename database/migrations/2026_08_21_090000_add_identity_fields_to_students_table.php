<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('birthplace')->nullable()->after('dob');
            $table->string('nationality')->nullable()->after('gender');
            $table->string('phone')->nullable()->after('email');
            $table->string('address')->nullable()->after('phone');
            $table->enum('regime', ['interne', 'externe'])->nullable()->after('status');
            $table->enum('enrollment_type', ['new', 'returning', 'transferred'])->default('new')->after('regime');
            $table->string('dossier_number')->nullable()->after('roll_number');
            $table->date('enrollment_date')->nullable()->after('enrollment_type');
            $table->date('entry_date')->nullable()->after('enrollment_date');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'birthplace', 'nationality', 'phone', 'address',
                'regime', 'enrollment_type', 'dossier_number', 'enrollment_date', 'entry_date',
            ]);
        });
    }
};

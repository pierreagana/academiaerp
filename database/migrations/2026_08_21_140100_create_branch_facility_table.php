<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_facility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['branch_id', 'facility_id']);
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->dropColumn('facilities');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('contact_phone');
            $table->text('facilities')->nullable();
        });

        Schema::dropIfExists('branch_facility');
    }
};

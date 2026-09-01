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
        Schema::table('transport_boarding_scans', function (Blueprint $table) {
            $table->string('action')->default('board')->after('period');
            $table->foreignId('scanned_by_device_id')->nullable()->after('scanned_by_user_id')->constrained('access_devices')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_boarding_scans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('scanned_by_device_id');
            $table->dropColumn('action');
        });
    }
};

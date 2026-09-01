<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets the offline-capable scanner app replay a sync batch safely: each
     * scan carries a client-generated UUID, and if a row with that id
     * already exists we return it instead of creating a duplicate — covers
     * both "device queued it while offline, uploads it later" and "device
     * got no response and retries the same batch."
     */
    public function up(): void
    {
        Schema::table('access_logs', function (Blueprint $table) {
            $table->string('client_scan_id')->nullable()->unique()->after('scanned_code');
        });

        Schema::table('transport_boarding_scans', function (Blueprint $table) {
            $table->string('client_scan_id')->nullable()->unique()->after('action');
        });
    }

    public function down(): void
    {
        Schema::table('access_logs', function (Blueprint $table) {
            $table->dropUnique(['client_scan_id']);
            $table->dropColumn('client_scan_id');
        });

        Schema::table('transport_boarding_scans', function (Blueprint $table) {
            $table->dropUnique(['client_scan_id']);
            $table->dropColumn('client_scan_id');
        });
    }
};

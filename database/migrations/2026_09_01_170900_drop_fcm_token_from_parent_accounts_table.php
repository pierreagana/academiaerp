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
        // Superseded by device_tokens (a parent can now have a web session
        // and a mobile app registered at the same time; a single column
        // can't represent that). Added minutes earlier in this same batch
        // of work, no real data depends on it.
        Schema::table('parent_accounts', function (Blueprint $table) {
            $table->dropColumn('fcm_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parent_accounts', function (Blueprint $table) {
            $table->string('fcm_token')->nullable()->after('email');
        });
    }
};

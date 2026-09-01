<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Storage paths for the establishment's own photo catalog (up to 6 —
     * enforced in DashboardController@updateEstablishment), distinct from
     * School Track's `gallery_paths` which is the parent-facing marketing
     * profile's photo set.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->json('catalog_paths')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('catalog_paths');
        });
    }
};

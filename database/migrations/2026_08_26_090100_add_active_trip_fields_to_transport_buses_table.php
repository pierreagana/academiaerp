<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_buses', function (Blueprint $table) {
            $table->foreignId('active_route_id')->nullable()->after('driver_id')->constrained('transport_routes')->nullOnDelete();
            $table->string('active_shift')->nullable()->after('active_route_id');
            $table->timestamp('trip_started_at')->nullable()->after('active_shift');
        });
    }

    public function down(): void
    {
        Schema::table('transport_buses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('active_route_id');
            $table->dropColumn(['active_shift', 'trip_started_at']);
        });
    }
};

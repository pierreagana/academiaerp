<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!DB::table('payment_gateways')->where('slug', 'academia_pay')->exists()) {
            DB::table('payment_gateways')->insert([
                'slug' => 'academia_pay',
                'name' => 'Academia Pay',
                // Native to the platform — enabled by default, unlike third-party
                // gateways which start inactive until credentials are configured.
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('payment_gateways')->where('slug', 'academia_pay')->delete();
    }
};

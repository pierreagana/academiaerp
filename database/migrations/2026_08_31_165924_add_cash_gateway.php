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
        if (!DB::table('payment_gateways')->where('slug', 'cash')->exists()) {
            DB::table('payment_gateways')->insert([
                'slug' => 'cash',
                'name' => 'Espèces',
                'status' => 'inactive',
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
        DB::table('payment_gateways')->where('slug', 'cash')->delete();
    }
};

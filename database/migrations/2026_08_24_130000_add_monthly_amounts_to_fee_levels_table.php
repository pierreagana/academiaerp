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
        Schema::table('fee_levels', function (Blueprint $table) {
            // Null (the default, existing behavior): every installment is the flat
            // `monthly_fee`. A JSON array of `installments_count` amounts: a custom
            // per-month breakdown instead — their sum must stay within
            // monthly_fee * installments_count (enforced in FeeController).
            $table->json('monthly_amounts')->nullable()->after('monthly_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_levels', function (Blueprint $table) {
            $table->dropColumn('monthly_amounts');
        });
    }
};

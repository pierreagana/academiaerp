<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('status')->default('inactive'); // 'active' | 'inactive'
            $table->text('api_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->timestamps();
        });

        DB::table('payment_gateways')->insert([
            ['slug' => 'stripe', 'name' => 'Stripe', 'status' => 'inactive', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'razorpay', 'name' => 'Razorpay', 'status' => 'inactive', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'paystack', 'name' => 'PayStack', 'status' => 'inactive', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'flutterwave', 'name' => 'Flutterwave', 'status' => 'inactive', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};

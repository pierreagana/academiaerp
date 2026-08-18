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
        Schema::create('school_track_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('parent_accounts')->cascadeOnDelete();
            $table->string('plan'); // 'mensuel' | 'annuel'
            $table->decimal('amount_paid', 10, 2);
            $table->string('payment_method');
            $table->string('status')->default('active'); // active | expired | cancelled
            $table->timestamp('subscribed_at');
            $table->timestamp('expires_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['parent_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_track_subscriptions');
    }
};

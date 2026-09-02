<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks the request/confirmation workflow for a parent topping up their
     * Academia Pay wallet. No real external gateway is active today, so
     * every recharge currently goes through 'cash' (school staff confirms a
     * physical cash deposit) — the `method` column stays a payment_gateways
     * slug so a future active gateway (Wave, etc.) slots in without a schema
     * change: same table, same approve action, just credited automatically
     * by the webhook instead of manually by staff.
     */
    public function up(): void
    {
        Schema::create('wallet_recharge_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('parent_accounts')->cascadeOnDelete();
            // A parent's wallet isn't tied to one school, but staff review it
            // per-school like everything else here — defaults to the school
            // of the parent's first linked child at request time (same
            // "first child" default already used across MobileParentController).
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method'); // payment_gateways.slug
            $table->string('status')->default('pending'); // pending|approved|rejected
            $table->string('reference')->nullable()->index();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['parent_id', 'status']);
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_recharge_requests');
    }
};

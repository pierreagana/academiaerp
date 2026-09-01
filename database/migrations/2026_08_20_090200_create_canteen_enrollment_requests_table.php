<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Request/approval workflow for canteen enrollment. Unlike Transport,
     * there's no existing pivot to reuse as the "is enrolled" signal (every
     * student already gets an auto-created `canteen_accounts` wallet
     * regardless — see SyncRosterUseCase) — so here the latest row per
     * student IS the signal: `approved` means enrolled.
     */
    public function up(): void
    {
        Schema::create('canteen_enrollment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending|approved|rejected
            $table->string('source'); // parent|school
            $table->foreignId('requested_by_parent_id')->nullable()->constrained('parent_accounts')->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canteen_enrollment_requests');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks a school's requests to activate a paid module beyond what its
     * subscribed package already includes. Approval is a manual superadmin
     * action (billing happens outside the app), not instant self-service.
     */
    public function up(): void
    {
        Schema::create('school_extension_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('module_name');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'module_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_extension_requests');
    }
};

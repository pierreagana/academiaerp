<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks the request/approval workflow for a student's bus enrollment.
     * The actual "is this student allowed on the bus" signal stays the
     * existing `transport_route_stop_student` pivot — this table only
     * records who asked, who reviewed, and why, and is what an approval
     * writes into the pivot.
     */
    public function up(): void
    {
        Schema::create('transport_enrollment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('route_stop_id')->constrained('transport_route_stops')->cascadeOnDelete();
            $table->string('period')->default('morning'); // morning|evening
            $table->string('status')->default('pending'); // pending|approved|rejected
            $table->string('source'); // parent|school
            $table->foreignId('requested_by_parent_id')->nullable()->constrained('parent_accounts')->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'period', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_enrollment_requests');
    }
};

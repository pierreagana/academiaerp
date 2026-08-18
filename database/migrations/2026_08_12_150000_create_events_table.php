<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type');
            $table->string('organizer_name')->nullable();
            $table->text('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->string('location_type')->default('internal');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->string('external_address')->nullable();
            $table->string('audience_type')->default('all');
            $table->decimal('estimated_budget', 12, 2)->nullable();
            $table->json('equipment_needs')->nullable();
            $table->text('catering_notes')->nullable();
            $table->decimal('participation_fee', 10, 2)->nullable();
            $table->unsignedInteger('bus_count')->nullable();
            $table->unsignedInteger('bus_capacity')->nullable();
            $table->string('bus_status')->nullable();
            $table->unsignedInteger('catering_count')->nullable();
            $table->string('catering_status')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

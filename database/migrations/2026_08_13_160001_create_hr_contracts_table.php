<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('holder_type');
            $table->unsignedBigInteger('holder_id');
            $table->string('contract_type');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedInteger('reminder_days_before')->default(30);
            $table->timestamp('reminder_acknowledged_at')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['holder_type', 'holder_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_contracts');
    }
};

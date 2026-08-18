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
        Schema::create('fee_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('level');
            $table->string('academic_year');
            $table->decimal('registration_fee', 12, 2)->default(0);
            $table->decimal('monthly_fee', 12, 2)->default(0);
            $table->unsignedInteger('installments_count')->default(0);
            $table->date('start_date');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'level', 'academic_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_levels');
    }
};

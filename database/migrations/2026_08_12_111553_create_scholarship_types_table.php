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
        Schema::create('scholarship_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('min_average', 4, 2)->nullable();
            $table->decimal('min_attendance_rate', 5, 2)->nullable();
            $table->decimal('max_family_income', 14, 2)->nullable();
            $table->string('min_competition_level')->nullable();
            $table->text('required_documents')->nullable();
            $table->decimal('default_monthly_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarship_types');
    }
};

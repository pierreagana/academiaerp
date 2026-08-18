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
        Schema::create('registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');
            $table->string('applicant_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('region')->nullable();
            $table->string('status')->default('en attente'); // en attente, en revue, approuvée, rejetée
            $table->string('plan_requested')->default('Basic');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_requests');
    }
};

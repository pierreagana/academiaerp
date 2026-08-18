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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('set null');
            $table->string('school_name'); // For quick access or if school is deleted
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // paid, pending, failed
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->string('plan_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

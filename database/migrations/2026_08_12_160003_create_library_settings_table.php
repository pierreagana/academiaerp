<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('max_books_per_student')->default(4);
            $table->unsignedInteger('loan_duration_days')->default(14);
            $table->decimal('late_fee_per_day', 8, 2)->default(0);
            $table->boolean('enforce_fees')->default(false);
            $table->string('card_format')->default('digital_qr');
            $table->boolean('auto_renew_staff_cards')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_settings');
    }
};

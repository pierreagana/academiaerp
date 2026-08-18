<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('borrower_type');
            $table->unsignedBigInteger('borrower_id');
            $table->date('borrowed_at');
            $table->date('due_at');
            $table->date('returned_at')->nullable();
            $table->dateTime('reminded_at')->nullable();
            $table->timestamps();
            $table->index(['borrower_type', 'borrower_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};

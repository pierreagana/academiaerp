<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canteen_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('holder_type');
            $table->unsignedBigInteger('holder_id');
            $table->string('status')->default('externe');
            $table->decimal('balance', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['school_id', 'holder_type', 'holder_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canteen_accounts');
    }
};

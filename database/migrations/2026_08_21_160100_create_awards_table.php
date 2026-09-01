<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('award_type_id')->constrained()->cascadeOnDelete();
            $table->enum('recipient_type', ['student', 'teacher', 'staff']);
            $table->unsignedBigInteger('recipient_id');
            $table->string('material_reward')->nullable();
            $table->text('reason')->nullable();
            $table->date('awarded_date');
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['recipient_type', 'recipient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('awards');
    }
};

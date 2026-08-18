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
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('holder');
            $table->string('scanned_code');
            $table->string('person_name');
            $table->string('role_label');
            $table->enum('action', ['entry', 'exit']);
            $table->foreignId('access_point_id')->nullable()->constrained('access_points')->nullOnDelete();
            $table->boolean('authorized')->default(true);
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_logs');
    }
};

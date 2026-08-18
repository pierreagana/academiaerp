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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->constrained('syllabuses')->onDelete('cascade');
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('video_url')->nullable();
            $table->integer('order')->default(1);
            $table->enum('status', ['draft', 'published'])->default('published');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};

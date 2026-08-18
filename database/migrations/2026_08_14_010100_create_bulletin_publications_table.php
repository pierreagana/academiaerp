<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulletin_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['draft', 'validated', 'published'])->default('draft');
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['academic_class_id', 'semester_id'], 'bulletin_publications_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletin_publications');
    }
};

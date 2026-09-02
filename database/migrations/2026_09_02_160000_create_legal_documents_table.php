<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('legal_document_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('parent_accounts')->cascadeOnDelete();
            $table->timestamp('signed_at');
            $table->timestamps();

            $table->unique(['legal_document_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_document_signatures');
        Schema::dropIfExists('legal_documents');
    }
};

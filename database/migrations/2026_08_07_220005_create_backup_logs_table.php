<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('size');
            $table->string('type')->default('Automatique'); // Automatique, Manuel
            $table->string('status')->default('success'); // success, in_progress, failed
            $table->string('storage_location')->default('AWS S3 (eu-west-3)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
    }
};

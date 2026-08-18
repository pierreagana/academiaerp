<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_card_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subdomain_id')->constrained('report_card_subdomains')->cascadeOnDelete();
            $table->string('statement');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_competencies');
    }
};

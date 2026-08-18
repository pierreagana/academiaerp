<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_catalog_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('addon'); // addon, sla, module
            $table->text('description')->nullable();
            $table->string('price_tag')->nullable();
            $table->string('price_color')->default('text-slate-900');
            $table->string('icon')->default('ph-cube');
            $table->string('icon_bg')->default('bg-indigo-50 text-indigo-600');
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_beta')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_catalog_items');
    }
};

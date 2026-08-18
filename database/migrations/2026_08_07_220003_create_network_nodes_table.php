<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('network_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('region');
            $table->string('ip_address');
            $table->string('status')->default('online'); // online, degraded, offline
            $table->integer('latency_ms')->default(15);
            $table->integer('load_pct')->default(45);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_nodes');
    }
};

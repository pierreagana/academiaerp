<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_alert_rules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('severity')->default('warning'); // critical, warning, info
            $table->string('metric');
            $table->string('threshold');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_alert_rules');
    }
};

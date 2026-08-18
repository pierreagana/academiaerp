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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable(); // e.g. Lycée, Collège, Primary
            $table->string('status')->default('active'); // active, inactive, suspended
            $table->string('plan_name')->default('Basic');
            $table->integer('students_count')->default(0);
            $table->decimal('storage_used_gb', 8, 2)->default(0);
            $table->string('location')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->date('founded_date')->nullable();
            $table->string('domain')->nullable()->unique();
            $table->string('theme_color')->nullable();
            $table->string('logo_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canteen_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('slot');
            $table->string('title');
            $table->string('description')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'date', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canteen_menu_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canteen_menu_items', function (Blueprint $table) {
            $table->index(['school_id', 'date', 'slot'], 'canteen_menu_items_school_date_slot_idx');
        });

        Schema::table('canteen_menu_items', function (Blueprint $table) {
            $table->dropUnique('canteen_menu_items_school_id_date_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::table('canteen_menu_items', function (Blueprint $table) {
            $table->unique(['school_id', 'date', 'slot']);
        });

        Schema::table('canteen_menu_items', function (Blueprint $table) {
            $table->dropIndex('canteen_menu_items_school_date_slot_idx');
        });
    }
};

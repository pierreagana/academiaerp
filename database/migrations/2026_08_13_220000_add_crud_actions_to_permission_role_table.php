<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permission_role', function (Blueprint $table) {
            $table->boolean('can_show')->default(true)->after('permission_id');
            $table->boolean('can_create')->default(true)->after('can_show');
            $table->boolean('can_edit')->default(true)->after('can_create');
            $table->boolean('can_update')->default(true)->after('can_edit');
            $table->boolean('can_delete')->default(true)->after('can_update');
        });
    }

    public function down(): void
    {
        Schema::table('permission_role', function (Blueprint $table) {
            $table->dropColumn(['can_show', 'can_create', 'can_edit', 'can_update', 'can_delete']);
        });
    }
};

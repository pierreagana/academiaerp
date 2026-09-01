<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
            $table->string('sector')->nullable()->after('type');
            $table->string('status')->default('active')->after('sector');
            $table->string('language_regime')->nullable()->after('status');
            $table->json('levels')->nullable()->after('language_regime');
            $table->string('contact_email')->nullable()->after('country');
            $table->string('address')->nullable()->after('contact_email');
            $table->text('facilities')->nullable()->after('address');
            $table->decimal('latitude', 10, 7)->nullable()->after('facilities');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('slogan')->nullable()->after('longitude');
            $table->string('logo_path')->nullable()->after('slogan');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'code', 'sector', 'status', 'language_regime', 'levels',
                'contact_email', 'address', 'facilities', 'latitude', 'longitude', 'slogan', 'logo_path',
            ]);
        });
    }
};

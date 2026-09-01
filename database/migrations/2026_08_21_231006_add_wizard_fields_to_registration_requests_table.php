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
        Schema::table('registration_requests', function (Blueprint $table) {
            // Everything the public "Demande de Démo" wizard collects beyond
            // the original lead fields (school_name/applicant_name/email/phone/region/
            // plan_requested) — held here until a SuperAdmin approves the request,
            // at which point RegistrationRequestController::approve() copies it
            // onto the real School row. Kept nullable/optional since a request
            // can still be entered manually by SuperAdmin without this detail.
            $table->string('type')->nullable()->after('plan_requested');
            $table->string('sector')->nullable()->after('type');
            $table->string('language_regime')->nullable()->after('sector');
            $table->json('levels')->nullable()->after('language_regime');
            $table->unsignedInteger('students_count')->nullable()->after('levels');
            $table->string('slogan')->nullable()->after('students_count');
            $table->string('city')->nullable()->after('slogan');
            $table->string('country')->nullable()->after('city');
            $table->string('address')->nullable()->after('country');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('logo_path')->nullable()->after('longitude');
            $table->json('facilities')->nullable()->after('logo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_requests', function (Blueprint $table) {
            $table->dropColumn([
                'type', 'sector', 'language_regime', 'levels', 'students_count', 'slogan',
                'city', 'country', 'address', 'latitude', 'longitude', 'logo_path', 'facilities',
            ]);
        });
    }
};

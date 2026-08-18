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
        Schema::table('schools', function (Blueprint $table) {
            // School Track discovery profile — self-filled by the school via
            // the School Dashboard, distinct from `type` (institution kind,
            // e.g. "Lycée") which already exists. All nullable: a school only
            // becomes discoverable in School Track once School::isSchoolTrackProfileComplete()
            // is true (computed on the fly from these columns, see School model).
            $table->text('description')->nullable();
            $table->json('levels')->nullable(); // taught levels: Maternelle/Primaire/Collège/Lycée
            $table->json('tags')->nullable(); // free-form self-declared tags
            $table->json('facilities')->nullable(); // {wifi,energie_solaire,laboratoire,informatique,piscine,internat,sport,cantine}: bool
            $table->json('gallery_paths')->nullable(); // storage paths of uploaded photos
            $table->unsignedTinyInteger('success_rate')->nullable(); // self-reported exam pass rate, 0-100
            $table->json('academic_radar')->nullable(); // {Sciences,Maths,Lettres,Langues}: 0-100 self-rated
            $table->json('nearby_places')->nullable(); // [{emoji,label,distance}], self-reported
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'description', 'levels', 'tags', 'facilities', 'gallery_paths',
                'success_rate', 'academic_radar', 'nearby_places',
            ]);
        });
    }
};

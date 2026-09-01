<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('award_types', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('name');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        $categories = [
            'Récompenses académiques' => [
                "Tableau d'honneur", 'Félicitations', 'Encouragements', "Prix d'excellence",
                'Prix du meilleur élève', 'Prix du meilleur étudiant', 'Prix de la meilleure moyenne',
                'Prix de la meilleure progression', 'Prix de la meilleure note dans une matière',
                'Prix de major de classe', 'Prix de major de promotion', 'Prix du meilleur mémoire',
                'Prix du meilleur projet', 'Prix de la meilleure soutenance', 'Prix du meilleur résultat aux examens',
            ],
            'Récompenses de comportement' => [
                'Prix de discipline', 'Prix du meilleur comportement', 'Prix du respect', 'Prix de la ponctualité',
                "Prix de l'assiduité", 'Prix de la persévérance', "Prix de l'esprit de responsabilité",
                "Prix de l'entraide", 'Prix du leadership', 'Prix du civisme', 'Prix du mérite',
            ],
            'Récompenses sportives' => [
                'Meilleur sportif', 'Meilleur joueur', 'Meilleure joueuse', 'Meilleur athlète', 'Meilleur buteur',
                'Meilleur gardien', "Prix de l'équipe championne", 'Prix du fair-play',
                "Médaille d'or", "Médaille d'argent", 'Médaille de bronze',
            ],
            'Récompenses culturelles et artistiques' => [
                'Prix artistique', 'Prix de musique', 'Prix de danse', 'Prix de théâtre', 'Prix de dessin',
                'Prix de peinture', 'Prix de littérature', 'Prix de poésie', "Prix d'éloquence",
                'Prix de meilleur orateur', 'Prix de créativité',
            ],
            'Récompenses technologiques et scientifiques' => [
                "Prix d'innovation", 'Prix scientifique', 'Prix de programmation', 'Prix de robotique',
                'Prix de technologie', 'Prix de recherche', "Prix de l'innovation numérique",
                'Prix du meilleur projet scientifique',
            ],
            'Récompenses citoyennes et sociales' => [
                'Prix du meilleur délégué', 'Prix du leadership étudiant', 'Prix de solidarité', 'Prix de bénévolat',
                "Prix d'engagement communautaire", 'Prix de responsabilité sociale',
                'Prix de protection de l\'environnement', "Prix du meilleur ambassadeur de l'établissement",
            ],
            'Récompenses spéciales' => [
                "Élève de l'année", "Étudiant de l'année", "Promotion de l'année", 'Prix du directeur',
                "Prix du président de l'établissement", 'Prix spécial du jury', 'Prix du mérite exceptionnel',
                "Prix d'honneur", "Diplôme d'honneur", 'Certificat de mérite',
            ],
        ];

        $rows = [];
        $order = 0;
        $now = now();
        foreach ($categories as $category => $names) {
            foreach ($names as $name) {
                $rows[] = [
                    'category' => $category,
                    'name' => $name,
                    'order' => $order++,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        \Illuminate\Support\Facades\DB::table('award_types')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('award_types');
    }
};

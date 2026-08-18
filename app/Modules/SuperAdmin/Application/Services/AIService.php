<?php

namespace App\Modules\SuperAdmin\Application\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class AIService
{
    /**
     * Génère un brouillon de réponse pour un ticket de support en utilisant OpenAI.
     *
     * @param string $subject
     * @param string $description
     * @param string $schoolName
     * @return string
     */
    public function generateSupportDraft(string $subject, string $description, string $schoolName): string
    {
        try {
            // Check if API key is configured
            if (empty(config('openai.api_key'))) {
                return "L'API Key n'est pas configurée dans les Paramètres Globaux. Veuillez la définir dans le menu 'IA & Analytics > Configuration'.";
            }

            $prompt = <<<EOT
Tu es un agent de support technique expert et empathique pour "AcademiaERP", un logiciel de gestion scolaire SaaS (ERP).
L'établissement "$schoolName" a ouvert un ticket de support.

Sujet du ticket : $subject
Description du problème : $description

Rédige une réponse professionnelle, polie et rassurante au client. 
- Remercie-les d'avoir signalé le problème.
- Confirme que nous avons bien pris en compte leur demande et que notre équipe technique est sur le coup.
- Si le problème semble complexe (API, synchronisation, notes), précise que nous faisons des investigations.
- Reste concis (maximum 3-4 phrases).
- Ne mets pas de variables (comme [Nom] ou [Signature]), signe simplement par "L'équipe Support AcademiaERP".
EOT;

            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'Tu es un assistant de support de niveau 1 pour un SaaS éducatif francophone.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 250,
            ]);

            return trim($response->choices[0]->message->content ?? 'Impossible de générer le brouillon.');
        } catch (\Exception $e) {
            Log::error('Erreur AIService generateSupportDraft : ' . $e->getMessage());
            return "Erreur de connexion à l'IA : " . $e->getMessage();
        }
    }
}

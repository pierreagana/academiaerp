<?php

namespace App\Modules\SuperAdmin\Application\Services;

use App\Modules\SuperAdmin\Domain\Models\GlobalSetting;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use Anthropic\Laravel\Facades\Anthropic;

class AIService
{
    public const PROVIDER_OPENAI = 'openai';
    public const PROVIDER_CLAUDE = 'claude';

    /**
     * Which provider is currently selected in Paramètres Globaux (défaut: OpenAI).
     */
    public function activeProvider(): string
    {
        $value = GlobalSetting::where('key', 'ai_provider')->value('value');

        return $value === self::PROVIDER_CLAUDE ? self::PROVIDER_CLAUDE : self::PROVIDER_OPENAI;
    }

    public function isConfigured(string $provider): bool
    {
        return $provider === self::PROVIDER_CLAUDE
            ? !empty(config('anthropic.api_key'))
            : !empty(config('openai.api_key'));
    }

    /**
     * Generic text generation used by every AI-powered feature in the app —
     * routes to whichever provider is currently selected in Global Settings.
     *
     * @return array{success: bool, text: ?string, error: ?string}
     */
    public function generateText(string $systemPrompt, string $userPrompt, int $maxTokens = 300): array
    {
        $provider = $this->activeProvider();

        if (!$this->isConfigured($provider)) {
            $label = $provider === self::PROVIDER_CLAUDE ? 'Anthropic Claude' : 'OpenAI';

            return [
                'success' => false,
                'text' => null,
                'error' => "La clé API {$label} n'est pas configurée. Définissez-la dans Paramètres Globaux > Configuration IA.",
            ];
        }

        try {
            $text = $provider === self::PROVIDER_CLAUDE
                ? $this->callClaude($systemPrompt, $userPrompt, $maxTokens)
                : $this->callOpenAi($systemPrompt, $userPrompt, $maxTokens);

            if ($text === '') {
                return ['success' => false, 'text' => null, 'error' => 'Réponse vide reçue du modèle IA.'];
            }

            return ['success' => true, 'text' => $text, 'error' => null];
        } catch (\Throwable $e) {
            Log::error('Erreur AIService generateText (' . $provider . ') : ' . $e->getMessage());

            return [
                'success' => false,
                'text' => null,
                'error' => "Erreur de connexion à l'IA : " . $e->getMessage(),
            ];
        }
    }

    private function callOpenAi(string $systemPrompt, string $userPrompt, int $maxTokens): string
    {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => $maxTokens,
        ]);

        return trim($response->choices[0]->message->content ?? '');
    }

    private function callClaude(string $systemPrompt, string $userPrompt, int $maxTokens): string
    {
        $response = Anthropic::messages()->create([
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => $maxTokens,
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ]);

        foreach ($response->content as $block) {
            if (($block->type ?? null) === 'text' && !empty($block->text)) {
                return trim($block->text);
            }
        }

        return '';
    }

    /**
     * Real connectivity check for the "Tester connexion" buttons — actually
     * calls the provider instead of pretending.
     *
     * @return array{success: bool, message: string}
     */
    public function testConnection(string $provider): array
    {
        if (!$this->isConfigured($provider)) {
            $label = $provider === self::PROVIDER_CLAUDE ? 'Anthropic Claude' : 'OpenAI';

            return ['success' => false, 'message' => "Aucune clé API {$label} configurée."];
        }

        try {
            if ($provider === self::PROVIDER_CLAUDE) {
                Anthropic::models()->list();
                $label = 'Anthropic Claude';
            } else {
                OpenAI::models()->list();
                $label = 'OpenAI';
            }

            return ['success' => true, 'message' => "Connexion {$label} réussie."];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @return array{success: bool, draft: ?string, error: ?string}
     */
    public function generateSupportDraft(string $subject, string $description, string $schoolName): array
    {
        $systemPrompt = 'Tu es un assistant de support de niveau 1 pour un SaaS éducatif francophone.';

        $userPrompt = <<<EOT
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

        $result = $this->generateText($systemPrompt, $userPrompt, 250);

        return [
            'success' => $result['success'],
            'draft' => $result['text'],
            'error' => $result['error'],
        ];
    }
}

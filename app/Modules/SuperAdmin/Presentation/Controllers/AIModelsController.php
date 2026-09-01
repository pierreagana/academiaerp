<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\SuperAdmin\Application\UseCases\ListAIModelsUseCase;
use App\Modules\SuperAdmin\Application\Services\AIService;
use App\Modules\SuperAdmin\Domain\Models\AIModel;
use App\Modules\SuperAdmin\Domain\Models\GlobalSetting;

class AIModelsController extends Controller
{
    public function __construct(
        private ListAIModelsUseCase $listAIModelsUseCase,
        private AIService $aiService,
    ) {}

    public function index()
    {
        // 1. Fetch AI Models from SQL database table `ai_models`
        $modelsData = AIModel::all();
        $models = [];

        foreach ($modelsData as $aiModel) {
            $models[] = [
                'id'           => $aiModel->id,
                'name'         => $aiModel->name ?? 'Gemini Pro',
                'provider'     => $aiModel->provider ?? 'Google Cloud AI',
                'status'       => $aiModel->status ?? 'active',
                'status_label' => $aiModel->status_label ?? 'Actif (LLM)',
                'latency'      => $aiModel->latency ?? '45ms',
                'color'        => $aiModel->color ?? 'emerald',
                'is_real'      => true,
            ];
        }

        if (empty($models)) {
            $models = [
                [
                    'id'           => 1,
                    'name'         => 'Gemini 1.5 Pro',
                    'provider'     => 'Google AI Studio',
                    'status'       => 'active',
                    'status_label' => 'Actif (Tuteur Virtuel)',
                    'latency'      => '42ms',
                    'color'        => 'emerald',
                    'is_real'      => false,
                ],
                [
                    'id'           => 2,
                    'name'         => 'Gemini 1.5 Flash',
                    'provider'     => 'Google Cloud Vertex',
                    'status'       => 'active',
                    'status_label' => 'Haute Vitesse',
                    'latency'      => '18ms',
                    'color'        => 'violet',
                    'is_real'      => false,
                ],
                [
                    'id'           => 3,
                    'name'         => 'Academia OCR LLM',
                    'provider'     => 'Analyse Notes & PDF',
                    'status'       => 'active',
                    'status_label' => 'Saisie Auto Bulletins',
                    'latency'      => '65ms',
                    'color'        => 'emerald',
                    'is_real'      => false,
                ],
            ];
        }

        // 2. Fetch Global Parameters from SQL database table `global_settings`
        $fallbackSetting = GlobalSetting::where('key', 'ai_fallback_enabled')->first();
        $ecoSetting      = GlobalSetting::where('key', 'ai_eco_mode')->first();
        $cacheSetting    = GlobalSetting::where('key', 'ai_cache_responses')->first();

        $globalParams = [
            [
                'key'         => 'ai_fallback_enabled',
                'label'       => 'Fallback Automatique',
                'description' => 'Bascule vers un modèle secondaire en cas d\'erreur de l\'API principale',
                'enabled'     => $fallbackSetting ? ($fallbackSetting->value === 'true') : true
            ],
            [
                'key'         => 'ai_eco_mode',
                'label'       => 'Mode Économie d\'Énergie',
                'description' => 'Redirige les requêtes simples vers Gemini Flash pour réduire les coûts',
                'enabled'     => $ecoSetting ? ($ecoSetting->value === 'true') : true
            ],
            [
                'key'         => 'ai_cache_responses',
                'label'       => 'Cache de Réponses',
                'description' => 'Stocke les réponses fréquentes pendant 24h pour limiter les appels réseau',
                'enabled'     => $cacheSetting ? ($cacheSetting->value === 'true') : true
            ],
        ];

        // 3. Real API key status for both providers the app can call through
        // AIService. Keys themselves are configured in Global Settings, not
        // here; this page only reflects and tests their current state, plus
        // which one is currently selected to actually handle requests.
        $openAiKeyValue = GlobalSetting::where('key', 'openai_api_key')->value('value');
        $openAiKeyConfigured = !empty($openAiKeyValue);
        $openAiKeyHint = $openAiKeyConfigured
            ? substr($openAiKeyValue, 0, 6) . str_repeat('•', 12) . substr($openAiKeyValue, -4)
            : null;

        $anthropicKeyValue = GlobalSetting::where('key', 'anthropic_api_key')->value('value');
        $anthropicKeyConfigured = !empty($anthropicKeyValue);
        $anthropicKeyHint = $anthropicKeyConfigured
            ? substr($anthropicKeyValue, 0, 6) . str_repeat('•', 12) . substr($anthropicKeyValue, -4)
            : null;

        $activeProvider = $this->aiService->activeProvider();

        // 4. Performance Thresholds from SQL table `global_settings`
        $latencyThresh = GlobalSetting::where('key', 'ai_max_latency_threshold')->first();
        $errorThresh   = GlobalSetting::where('key', 'ai_error_alert_threshold')->first();

        $perfThresholds = [
            [
                'key'       => 'ai_max_latency_threshold',
                'label'     => 'Latence Max Acceptable',
                'value'     => $latencyThresh ? (int)$latencyThresh->value : 85,
                'color'     => 'indigo',
                'min_label' => '0ms (Rapide)',
                'max_label' => '500ms (Critique)'
            ],
            [
                'key'       => 'ai_error_alert_threshold',
                'label'     => 'Seuil d\'Alerte Taux d\'Erreur',
                'value'     => $errorThresh ? (int)$errorThresh->value : 15,
                'color'     => 'rose',
                'min_label' => '0%',
                'max_label' => '5% (Alerte)'
            ],
        ];

        $lastDeployedAt = GlobalSetting::where('key', 'ai_last_deployed_at')->value('value');

        return view('SuperAdmin::ai-models', compact(
            'models', 'globalParams', 'perfThresholds', 'lastDeployedAt',
            'openAiKeyConfigured', 'openAiKeyHint',
            'anthropicKeyConfigured', 'anthropicKeyHint', 'activeProvider'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'provider'     => 'required|string|max:255',
            'status_label' => 'required|string|max:255',
            'latency'      => 'required|string|max:50',
            'color'        => 'required|string|max:50',
        ]);

        AIModel::create([
            'name'         => $validated['name'],
            'provider'     => $validated['provider'],
            'status'       => 'active',
            'status_label' => $validated['status_label'],
            'latency'      => $validated['latency'],
            'color'        => $validated['color'],
        ]);

        return redirect()->route('superadmin.ai-models')->with('success', 'Nouveau modèle IA enregistré et appliqué dans la base SQL !');
    }

    /**
     * Marks one catalog entry active/disabled — this only affects how it's
     * displayed and counted, since nothing in the app currently routes AI
     * calls based on this table (the one real integration, the support
     * ticket draft, always uses OpenAI directly).
     */
    public function toggleModelStatus(int $id)
    {
        $model = AIModel::findOrFail($id);
        $model->status = $model->status === 'active' ? 'disabled' : 'active';
        $model->save();

        return redirect()->route('superadmin.ai-models')->with(
            'success',
            $model->name . ' ' . ($model->status === 'active' ? 'activé' : 'désactivé') . '.'
        );
    }

    public function toggleSetting(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'enabled' => 'required|boolean',
        ]);

        GlobalSetting::updateOrCreate(
            ['key' => $validated['key']],
            [
                'value' => $validated['enabled'] ? 'true' : 'false',
                'type' => 'boolean',
                'is_public' => false
            ]
        );

        return response()->json(['success' => true, 'message' => 'Configuration IA enregistrée dans la base SQL.']);
    }

    public function updateThreshold(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'required|numeric',
        ]);

        GlobalSetting::updateOrCreate(
            ['key' => $validated['key']],
            [
                'value' => (string) $validated['value'],
                'type' => 'integer',
                'is_public' => false
            ]
        );

        return response()->json(['success' => true, 'message' => 'Seuil de performance enregistré dans la base SQL.']);
    }

    public function deployConfig()
    {
        GlobalSetting::updateOrCreate(
            ['key' => 'ai_last_deployed_at'],
            [
                'value' => date('d/m/Y H:i:s'),
                'type' => 'string',
                'is_public' => false
            ]
        );

        AIModel::where('status', '!=', 'disabled')->update(['status' => 'active']);

        return redirect()->route('superadmin.ai-models')->with('success', 'Configuration enregistrée.');
    }

    /**
     * Performs a real call against the given provider's API to verify the
     * key actually works — unlike the rest of this page, this one talks to
     * the real provider and reports what actually happened.
     */
    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string|in:openai,claude',
        ]);

        return response()->json($this->aiService->testConnection($validated['provider']));
    }

    /**
     * Which provider (OpenAI or Claude) actually handles AI requests
     * app-wide — everything calling AIService::generateText() routes here.
     */
    public function setProvider(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string|in:openai,claude',
        ]);

        GlobalSetting::updateOrCreate(
            ['key' => 'ai_provider'],
            ['value' => $validated['provider'], 'type' => 'string', 'is_public' => false]
        );

        return response()->json(['success' => true, 'message' => 'Fournisseur IA mis à jour.']);
    }
}

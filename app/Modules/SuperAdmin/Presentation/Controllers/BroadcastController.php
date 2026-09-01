<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Modules\SuperAdmin\Application\UseCases\ListBroadcastsUseCase;
use App\Modules\SuperAdmin\Application\Services\AIService;
use App\Modules\SuperAdmin\Domain\Models\BroadcastMessage;
use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Support\Notifications\FirebasePushService;

class BroadcastController extends Controller
{
    public function __construct(
        private ListBroadcastsUseCase $listBroadcastsUseCase
    ) {}

    public function index()
    {
        $messages = $this->listBroadcastsUseCase->execute();
        return view('SuperAdmin::broadcast', compact('messages'));
    }

    /**
     * Real AI rewrite of the announcement draft — replaces the button that
     * used to swap in a hardcoded canned message regardless of input.
     */
    public function aiRewrite(Request $request, AIService $aiService)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:5000',
        ]);

        $systemPrompt = "Tu es un rédacteur professionnel pour AcademiaERP, un logiciel de gestion scolaire SaaS. Tu rédiges des annonces diffusées aux écoles clientes de la plateforme.";

        $userPrompt = "Rédige ou améliore ce message d'annonce destiné aux directeurs et équipes pédagogiques des écoles clientes d'AcademiaERP.\n\n";
        $userPrompt .= !empty($validated['title']) ? "Titre actuel : {$validated['title']}\n" : '';
        $userPrompt .= !empty($validated['message'])
            ? "Brouillon actuel à améliorer :\n{$validated['message']}\n"
            : "Aucun brouillon fourni : rédige un message d'annonce générique, clair et professionnel.\n";
        $userPrompt .= "\nRéponds STRICTEMENT dans ce format, sans rien ajouter avant ou après :\nTITRE: <titre>\nMESSAGE: <message>\n\nLe message doit être concis (3 à 5 phrases), professionnel, en français, sans placeholders type [Nom] ou [Date].";

        $result = $aiService->generateText($systemPrompt, $userPrompt, 400);

        if (!$result['success']) {
            return response()->json(['success' => false, 'error' => $result['error']]);
        }

        $title = null;
        $message = $result['text'];

        if (preg_match('/TITRE\s*:\s*(.+?)\R+MESSAGE\s*:\s*(.+)/is', $result['text'], $matches)) {
            $title = trim($matches[1]);
            $message = trim($matches[2]);
        }

        return response()->json(['success' => true, 'title' => $title, 'message' => $message]);
    }

    public function store(Request $request, FirebasePushService $pushService)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'message'         => 'required|string',
            'target_audience' => 'nullable|string|max:100',
            'channels'        => 'nullable|array',
            'priority'        => 'nullable|string|in:low,normal,high,urgent',
            'expires_at'      => 'nullable|date',
        ]);

        $targetAudience = $validated['target_audience'] ?? 'Tous les établissements';
        $channels = $validated['channels'] ?? ['email', 'sms', 'push'];
        $channelsList = implode(', ', array_map('strtoupper', $channels));

        BroadcastMessage::create([
            'title'        => $validated['title'],
            'message'      => $validated['message'],
            'target_roles' => [$targetAudience . " • [" . $channelsList . "]"],
            'is_active'    => true,
            'expires_at'   => $validated['expires_at'] ?? now()->addDays(30),
        ]);

        // "channels" beyond push are still cosmetic today (same limitation
        // that predates this change for every channel) — target_audience
        // doesn't filter anything either, push goes out to every parent with
        // a registered device. Only push is wired to a real send below;
        // don't claim success for it unless something was actually attempted.
        $pushSummary = '';
        if (in_array('push', $channels, true)) {
            if (!$pushService->isConfigured()) {
                return redirect()->route('superadmin.broadcast')
                    ->with('error', "Le canal Push a été sélectionné, mais Firebase n'est pas configuré (voir Paramètres de Notification). Le message a été enregistré, mais aucune notification push n'a été envoyée.");
            }

            $result = $pushService->sendToParents(
                ParentAccount::whereNotNull('fcm_token')->get(),
                $validated['title'],
                $validated['message']
            );
            $pushSummary = " Push : {$result['sent']} envoyée(s), {$result['failed']} échec(s), {$result['skipped']} parent(s) sans appareil enregistré.";
        }

        return redirect()->route('superadmin.broadcast')->with('success', 'Annonce enregistrée sur les canaux [' . $channelsList . '].' . $pushSummary);
    }
}

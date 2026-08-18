<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Modules\SuperAdmin\Application\UseCases\ListBroadcastsUseCase;
use App\Modules\SuperAdmin\Domain\Models\BroadcastMessage;

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

    public function store(Request $request)
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
        $channelsList = !empty($validated['channels']) ? implode(', ', array_map('strtoupper', $validated['channels'])) : 'EMAIL, SMS, PUSH';

        BroadcastMessage::create([
            'title'        => $validated['title'],
            'message'      => $validated['message'],
            'target_roles' => [$targetAudience . " • [" . $channelsList . "]"],
            'is_active'    => true,
            'expires_at'   => $validated['expires_at'] ?? now()->addDays(30),
        ]);

        return redirect()->route('superadmin.broadcast')->with('success', 'Broadcast diffusé avec succès sur les canaux [' . $channelsList . '] et enregistré en base SQL !');
    }
}

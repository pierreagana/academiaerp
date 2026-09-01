<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Modules\SuperAdmin\Domain\Models\SupportTicket;
use App\Modules\SuperAdmin\Domain\Models\SupportTicketMessage;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $tickets = SupportTicket::orderByRaw("CASE status
            WHEN 'open'        THEN 1
            WHEN 'in_progress' THEN 2
            WHEN 'resolved'    THEN 3
            WHEN 'closed'      THEN 4
            END")
            ->orderByRaw("CASE priority
                WHEN 'critical' THEN 1
                WHEN 'high'     THEN 2
                WHEN 'normal'   THEN 3
                WHEN 'low'      THEN 4
                END")
            ->get();

        // KPIs — calculated from real DB data
        $kpis = [
            'open_tickets'      => $tickets->whereIn('status', ['open'])->count(),
            'in_progress'       => $tickets->where('status', 'in_progress')->count(),
            'resolved_today'    => $tickets->where('status', 'resolved')->count(),
            'critical_pending'  => $tickets->whereIn('priority', ['critical', 'high'])
                                           ->whereIn('status', ['open', 'in_progress'])
                                           ->count(),
        ];

        // Category distribution (for KPI 3)
        $categoryStats = $tickets->groupBy('category')->map->count()->sortByDesc(fn ($v) => $v);
        $total = $tickets->count() ?: 1;
        $categoryDistribution = $categoryStats->map(fn ($count) => [
            'count'   => $count,
            'percent' => round(($count / $total) * 100),
        ]);

        // Active ticket (from query param or first open)
        $activeId  = (int) $request->query('ticket', $tickets->first()?->id ?? 0);
        $activeTicket = $tickets->firstWhere('id', $activeId) ?? $tickets->first();

        return view('SuperAdmin::support', compact('tickets', 'kpis', 'categoryDistribution', 'activeTicket'));
    }

    public function reply(Request $request, int $id)
    {
        $request->validate([
            'reply' => 'required|string|min:5|max:2000',
        ]);

        $ticket = SupportTicket::findOrFail($id);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type' => SupportTicketMessage::SENDER_SUPPORT,
            'sender_name' => auth()->user()->name ?? 'Support AcademiaERP',
            'message' => $request->input('reply'),
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return redirect()
            ->route('superadmin.support', ['ticket' => $id])
            ->with('success', 'Réponse envoyée à ' . $ticket->school_name . ' pour le ticket #' . $ticket->ticket_id . '.');
    }

    public function close(Request $request, int $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->update(['status' => 'resolved']);

        return redirect()
            ->route('superadmin.support')
            ->with('success', 'Ticket #' . $ticket->ticket_id . ' résolu et clôturé avec succès.');
    }

    public function generateAiDraft(Request $request, int $id, \App\Modules\SuperAdmin\Application\Services\AIService $aiService)
    {
        $ticket = SupportTicket::findOrFail($id);
        
        $result = $aiService->generateSupportDraft(
            $ticket->subject ?? 'Sans sujet',
            $ticket->description ?? 'Aucune description',
            $ticket->school_name ?? 'École non spécifiée'
        );

        return response()->json($result);
    }
}

<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Domain\Models\SupportTicket;
use App\Modules\SuperAdmin\Domain\Models\SupportTicketMessage;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public const CATEGORIES = ['Technique', 'Facturation', 'Commercial', 'Assistance'];
    public const PRIORITIES = [
        'low' => 'Faible',
        'normal' => 'Normale',
        'high' => 'Élevée',
        'critical' => 'Critique',
    ];

    public function index()
    {
        $schoolId = auth()->user()->school_id;

        $tickets = SupportTicket::where('school_id', $schoolId)
            ->withCount('messages')
            ->orderByRaw("CASE status
                WHEN 'open'        THEN 1
                WHEN 'in_progress' THEN 2
                WHEN 'resolved'    THEN 3
                WHEN 'closed'      THEN 4
                END")
            ->latest()
            ->get();

        $categories = self::CATEGORIES;
        $priorities = self::PRIORITIES;

        return view('SchoolDashboard::support.index', compact('tickets', 'categories', 'priorities'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'category' => ['required', 'string', 'in:' . implode(',', self::CATEGORIES)],
            'priority' => ['required', 'string', 'in:' . implode(',', array_keys(self::PRIORITIES))],
        ]);

        $school = auth()->user()->school;

        $lastId = (int) str_replace('TK-', '', (string) SupportTicket::orderByDesc('id')->value('ticket_id'));
        $data['ticket_id'] = 'TK-' . str_pad(max($lastId + 1, 1001), 4, '0', STR_PAD_LEFT);
        $data['school_id'] = $school->id;
        $data['school_name'] = $school->name;
        $data['status'] = 'open';

        SupportTicket::create($data);

        return redirect()->route('school.support')->with('success', 'Votre demande a été envoyée au support AcademiaERP. Notre équipe vous répondra dans les meilleurs délais.');
    }

    /** Scoped to the school's own tickets — findOrFail(id) alone would let one school read another's thread by guessing the URL. */
    private function findOwnTicket(int $id): SupportTicket
    {
        return SupportTicket::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function show(int $id)
    {
        $ticket = $this->findOwnTicket($id);

        return view('SchoolDashboard::support.show', compact('ticket'));
    }

    public function reply(Request $request, int $id)
    {
        $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $ticket = $this->findOwnTicket($id);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type' => SupportTicketMessage::SENDER_SCHOOL,
            'sender_name' => $ticket->school_name,
            'message' => $request->input('message'),
        ]);

        // A school replying to a ticket the support team had marked resolved/closed
        // means the issue isn't actually settled — reopen it so it resurfaces in
        // the SuperAdmin queue instead of silently sitting in a "done" bucket.
        if (in_array($ticket->status, ['resolved', 'closed'], true)) {
            $ticket->update(['status' => 'open']);
        }

        return redirect()->route('school.support.show', $id)->with('success', 'Votre réponse a été envoyée au support AcademiaERP.');
    }
}

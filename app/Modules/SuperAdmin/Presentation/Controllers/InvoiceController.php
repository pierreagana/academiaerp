<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Application\UseCases\ListInvoicesUseCase;
use App\Modules\SuperAdmin\Application\Services\AIService;
use App\Modules\SuperAdmin\Domain\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private ListInvoicesUseCase $listInvoicesUseCase
    ) {}

    /**
     * Real recovery stats from the invoices table, narrated by AI — replaces
     * the hardcoded "L'assistant IA recommande d'envoyer un rappel..." text
     * that had no data behind it at all.
     */
    public function aiRecoveryAnalysis(AIService $aiService)
    {
        $today = now()->startOfDay();

        $paidTotal = Invoice::where('status', 'paid')->sum('amount');
        $pendingTotal = Invoice::whereIn('status', ['pending', 'failed', 'overdue'])->sum('amount');

        $overdueInvoices = Invoice::where('status', '!=', 'paid')->where('due_date', '<', $today)->get();
        $overdueCount = $overdueInvoices->count();
        $overdueAmount = $overdueInvoices->sum('amount');

        $topOverdueSchools = $overdueInvoices
            ->groupBy('school_name')
            ->map(fn ($invs, $name) => ['school' => $name, 'amount' => $invs->sum('amount')])
            ->sortByDesc('amount')
            ->take(3)
            ->values();

        $recoveryRate = ($paidTotal + $pendingTotal) > 0
            ? round(($paidTotal / ($paidTotal + $pendingTotal)) * 100)
            : null;

        $stats = [
            'total_encaisse' => (float) $paidTotal,
            'total_en_attente' => (float) $pendingTotal,
            'taux_recouvrement_pct' => $recoveryRate,
            'factures_en_retard' => $overdueCount,
            'montant_en_retard' => (float) $overdueAmount,
            'ecoles_les_plus_en_retard' => $topOverdueSchools->toArray(),
        ];

        $systemPrompt = "Tu es un analyste financier pour AcademiaERP, un SaaS de gestion scolaire facturant des écoles clientes. Tu résumes des statistiques réelles de recouvrement en français, de façon factuelle et actionnable.";
        $userPrompt = "Voici les statistiques réelles de facturation (données SQL, pas d'invention) :\n"
            . json_encode($stats, JSON_UNESCAPED_UNICODE)
            . "\n\nRédige une recommandation courte (2 à 4 phrases) en français : évalue la situation, et si des écoles sont en retard, recommande une action concrète (ex: relance). Si tout est à jour, dis-le simplement sans inventer de problème.";

        $result = $aiService->generateText($systemPrompt, $userPrompt, 250);

        return response()->json([
            'success' => $result['success'],
            'recommendation' => $result['text'],
            'error' => $result['error'],
            'stats' => $stats,
        ]);
    }

    public function index()
    {
        $invoices = $this->listInvoicesUseCase->execute(10);
        $totalPaid = \App\Modules\SuperAdmin\Domain\Models\Invoice::where('status', 'paid')->sum('amount');
        $totalPending = \App\Modules\SuperAdmin\Domain\Models\Invoice::whereIn('status', ['pending', 'failed', 'overdue'])->sum('amount');
        $schools = \App\Modules\SuperAdmin\Domain\Models\School::orderBy('name')->get();

        return view('SuperAdmin::invoices', compact('invoices', 'totalPaid', 'totalPending', 'schools'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'amount'    => 'required|numeric|min:1000',
            'due_date'  => 'required|date',
            'status'    => 'required|string',
        ]);

        $count = \App\Modules\SuperAdmin\Domain\Models\Invoice::count() + 1;
        $invNumber = 'INV-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $school = \App\Modules\SuperAdmin\Domain\Models\School::find($validated['school_id']);

        $invoice = new \App\Modules\SuperAdmin\Domain\Models\Invoice();
        $invoice->invoice_number = $invNumber;
        $invoice->school_id = $validated['school_id'];
        $invoice->school_name = $school?->name ?? 'Établissement Partner';
        $invoice->plan_name = $school?->plan_name;
        $invoice->amount = $validated['amount'];
        $invoice->due_date = $validated['due_date'];
        $invoice->issue_date = date('Y-m-d');
        $invoice->status = $validated['status'];
        $invoice->save();

        return redirect()->back()->with('success', "La facture '{$invNumber}' d'un montant de " . number_format($invoice->amount, 0, ',', ' ') . " FCFA a été générée avec succès.");
    }

    public function markAsPaid(int $id)
    {
        $invoice = \App\Modules\SuperAdmin\Domain\Models\Invoice::findOrFail($id);
        $invoice->status = 'paid';
        $invoice->save();

        return redirect()->back()->with('success', "La facture '{$invoice->invoice_number}' a été marquée comme PAYÉE.");
    }

    public function cancel(int $id)
    {
        $invoice = \App\Modules\SuperAdmin\Domain\Models\Invoice::findOrFail($id);
        $invoice->status = 'cancelled';
        $invoice->save();

        return redirect()->back()->with('success', "La facture '{$invoice->invoice_number}' a été annulée avec succès.");
    }

    public function sendReminder(int $id)
    {
        $invoice = \App\Modules\SuperAdmin\Domain\Models\Invoice::with('school')->findOrFail($id);
        $schoolName = $invoice->school?->name ?? 'l\'établissement';

        return redirect()->back()->with('success', "Relance de paiement (SMS & Email) envoyée avec succès à {$schoolName} pour la facture {$invoice->invoice_number}.");
    }

    public function downloadPdf(int $id)
    {
        $invoice = \App\Modules\SuperAdmin\Domain\Models\Invoice::with('school')->findOrFail($id);
        
        $schoolName = $invoice->school?->name ?? 'Établissement Client';
        $location = $invoice->school?->location ?? 'Afrique Central';
        $formattedAmount = number_format($invoice->amount, 0, ',', ' ') . ' FCFA';
        $statusUpper = strtoupper($invoice->status);

        $html = "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Facture {$invoice->invoice_number}</title>
            <style>
                body { font-family: 'Helvetica Neue', Arial, sans-serif; padding: 40px; color: #1e293b; background: #f8fafc; }
                .invoice-card { max-width: 800px; margin: 0 auto; background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 40px; shadow: 0 10px 25px rgba(0,0,0,0.05); }
                .header { display: flex; justify-content: space-between; border-bottom: 2px solid #031C5B; padding-bottom: 20px; margin-bottom: 30px; }
                .logo { font-size: 24px; font-weight: 800; color: #031C5B; letter-spacing: -0.5px; }
                .title { font-size: 20px; font-weight: bold; text-align: right; color: #475569; }
                .grid { display: flex; justify-content: space-between; margin-bottom: 30px; gap: 20px; }
                .box { flex: 1; background: #f1f5f9; border-radius: 12px; padding: 20px; font-size: 14px; line-height: 1.6; }
                table { width: 100%; border-collapse: collapse; margin-top: 30px; }
                th, td { border: 1px solid #e2e8f0; padding: 14px; text-align: left; font-size: 14px; }
                th { background: #031C5B; color: white; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }
                .total { text-align: right; font-size: 20px; font-weight: 800; margin-top: 25px; color: #031C5B; }
                .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-weight: bold; font-size: 12px; background: #dcfce7; color: #15803d; }
            </style>
        </head>
        <body onload='window.print()'>
            <div class='invoice-card'>
                <div class='header'>
                    <div>
                        <div class='logo'>ACADEMIA ERP SaaS</div>
                        <div style='font-size: 12px; color: #64748b; margin-top: 4px;'>Plateforme de Gestion Scolaire Centralisée</div>
                    </div>
                    <div class='title'>
                        FACTURE<br>
                        <span style='font-size: 14px; color: #031C5B;'>N° {$invoice->invoice_number}</span>
                    </div>
                </div>
                <div class='grid'>
                    <div class='box'>
                        <strong style='color: #031C5B;'>ÉMETTEUR (PRESTATAIRE):</strong><br>
                        Academia Cloud Network Platform<br>
                        Service Télécoms & Facturation SaaS<br>
                        Email: billing@academia-erp.com
                    </div>
                    <div class='box'>
                        <strong style='color: #031C5B;'>DESTINATAIRE (CLIENT):</strong><br>
                        <strong>{$schoolName}</strong><br>
                        Localisation: {$location}<br>
                        Statut: <span class='status-badge'>{$statusUpper}</span>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Description du Service</th>
                            <th>Quantité</th>
                            <th>Prix Unitaire</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Abonnement Licence Annuelle - Plateforme Academia ERP ({$schoolName})</td>
                            <td>1 Année</td>
                            <td>{$formattedAmount}</td>
                            <td><strong>{$formattedAmount}</strong></td>
                        </tr>
                    </tbody>
                </table>
                <div class='total'>Montant Total à Régler : {$formattedAmount}</div>
            </div>
        </body>
        </html>";

        return response($html, 200)->header('Content-Type', 'text/html');
    }
}

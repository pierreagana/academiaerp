<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Modules\Finance\Domain\Exceptions\InsufficientWalletBalanceException;
use App\Modules\SuperAdmin\Domain\Models\GlobalSetting;
use App\Modules\SuperAdmin\Domain\Models\Invoice;
use App\Modules\SuperAdmin\Domain\Models\PaymentGateway;
use App\Modules\SuperAdmin\Domain\Models\SystemLog;
use App\Support\Payments\CurrencyCode;
use App\Support\Payments\GatewayCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BillingController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;

        // Invoice::BelongsToSchool already scopes this to the authenticated
        // school — no manual where('school_id', ...) needed.
        $invoices = Invoice::orderByDesc('issue_date')->paginate(10);
        $gateways = PaymentGateway::where('status', 'active')->orderBy('id')->get();
        $wallet = $school->getOrCreateWallet();

        return view('SchoolDashboard::billing', compact('invoices', 'gateways', 'wallet'));
    }

    public function pay(Request $request, Invoice $invoice, string $method, GatewayCheckoutService $checkoutService)
    {
        if ($invoice->status === 'paid') {
            return redirect()->route('school.billing')->with('error', 'Cette facture est déjà payée.');
        }

        $school = auth()->user()->school;
        $amount = (float) $invoice->amount;

        if ($method === 'wallet') {
            $wallet = $school->getOrCreateWallet();

            try {
                $wallet->debit($amount, $invoice->invoice_number, "Paiement facture {$invoice->invoice_number}");
            } catch (InsufficientWalletBalanceException $e) {
                return redirect()->route('school.billing')->with('error', "Solde Academia Pay insuffisant ({$e->balance} disponible, {$amount} requis).");
            }

            $invoice->update(['status' => 'paid']);

            SystemLog::create([
                'level' => 'info',
                'message' => "Facture {$invoice->invoice_number} payée via le portefeuille Academia Pay",
                'context' => ['invoice_id' => $invoice->id, 'wallet_id' => $wallet->id, 'amount' => $amount],
                'source' => 'wallet_payment',
            ]);

            return redirect()->route('school.billing')->with('success', 'Facture payée avec votre portefeuille Academia Pay.');
        }

        if ($method === 'cash') {
            $cashEnabled = PaymentGateway::where('slug', 'cash')->where('status', 'active')->exists();
            if (!$cashEnabled) {
                return redirect()->route('school.billing')->with('error', "Le paiement en espèces n'est pas disponible pour le moment.");
            }

            // No money has actually moved yet — this only records the school's
            // intent so the superadmin knows to expect a cash payment. The
            // invoice stays unpaid until manually confirmed on reception
            // (InvoiceController::markAsPaid), exactly like the existing
            // fully-manual invoice workflow.
            SystemLog::create([
                'level' => 'info',
                'message' => "École {$school->name} a signalé un paiement en espèces à venir pour la facture {$invoice->invoice_number}",
                'context' => ['invoice_id' => $invoice->id, 'school_id' => $school->id, 'amount' => $amount],
                'source' => 'cash_payment_intent',
            ]);

            return redirect()->route('school.billing')->with('success', "Choix enregistré : remettez le montant en espèces à un représentant de la plateforme. La facture sera marquée payée dès confirmation de réception par notre équipe.");
        }

        $gateway = PaymentGateway::where('slug', $method)->where('status', 'active')->first();
        if (!$gateway) {
            return redirect()->route('school.billing')->with('error', 'Cette passerelle de paiement n\'est pas disponible.');
        }

        $currencyIso = CurrencyCode::iso(GlobalSetting::where('key', 'currency')->value('value') ?? 'Franc CFA (XOF)');

        $result = $checkoutService->createCheckout(
            gateway: $gateway,
            reference: $invoice->invoice_number,
            amount: $amount,
            currencyIso: $currencyIso,
            description: "Facture {$invoice->invoice_number} — {$invoice->plan_name}",
            successUrl: route('school.billing.success'),
            cancelUrl: route('school.billing.cancel'),
            payerEmail: $school->contact_email ?? auth()->user()->email,
            payerName: $school->name,
        );

        if ($result['type'] === 'error') {
            return redirect()->route('school.billing')->with('error', $result['message']);
        }

        if ($result['type'] === 'razorpay_checkout') {
            return view('SchoolDashboard::billing-razorpay', [
                'orderId' => $result['order_id'],
                'key' => $result['key'],
                'amount' => $result['amount'],
                'currency' => $result['currency'],
                'invoice' => $invoice,
                'school' => $school,
                'callbackUrl' => route('school.billing.success'),
            ]);
        }

        return redirect()->away($result['url']);
    }

    public function success()
    {
        return view('SchoolDashboard::billing-return', [
            'title' => 'Paiement en cours de confirmation',
            'message' => "Merci ! Votre paiement est en cours de vérification — la facture sera marquée payée dès confirmation de la passerelle (généralement quelques secondes).",
            'isCancel' => false,
        ]);
    }

    public function cancel()
    {
        return view('SchoolDashboard::billing-return', [
            'title' => 'Paiement annulé',
            'message' => "Le paiement a été annulé. Aucun montant n'a été débité.",
            'isCancel' => true,
        ]);
    }
}

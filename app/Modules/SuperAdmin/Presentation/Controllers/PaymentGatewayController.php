<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Domain\Models\PaymentGateway;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        $gateways = PaymentGateway::orderBy('id')->get()->keyBy('slug');

        // Academia Pay is the platform's native rail — always shown first, the rest
        // (third-party gateways) keep their insertion order after it.
        $gateways = collect(['academia_pay'])
            ->merge($gateways->keys()->diff(['academia_pay']))
            ->mapWithKeys(fn ($slug) => [$slug => $gateways->get($slug)])
            ->filter();

        return view('SuperAdmin::payment-gateways', compact('gateways'));
    }

    public function update(Request $request, string $slug)
    {
        $gateway = PaymentGateway::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'status' => ['nullable', 'in:active'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'secret_key' => ['nullable', 'string', 'max:500'],
            'webhook_secret' => ['nullable', 'string', 'max:500'],
        ]);

        // The toggle is a checkbox, not a select — absent from the submission means
        // "off", matching every other checkbox in this codebase (see SpecificConfigurationController).
        $data['status'] = $request->has('status') ? 'active' : 'inactive';

        // All three credential fields are encrypted and never echoed back into the
        // form (see the view) — an empty submission means "leave the existing value
        // alone", not "clear it". Some gateways disable one of these fields entirely
        // (e.g. Wave's secret_key, PayStack's webhook_secret aren't used for signature
        // verification) — a disabled input is never submitted at all, so the key may
        // be completely absent from $data rather than merely empty.
        foreach (['api_key', 'secret_key', 'webhook_secret'] as $field) {
            if (empty($data[$field] ?? null)) {
                unset($data[$field]);
            }
        }

        $gateway->update($data);

        return redirect()->route('superadmin.payment-gateways')
            ->with('success', "Passerelle {$gateway->name} mise à jour.");
    }
}

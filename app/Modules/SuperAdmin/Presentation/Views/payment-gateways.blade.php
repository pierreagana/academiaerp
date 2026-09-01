@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Page Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[28px] font-extrabold text-[#111827]">Passerelles de Paiement</h2>
            <p class="text-[15px] text-slate-500 mt-1">Configurez les passerelles utilisées pour la facturation des abonnements SaaS des écoles.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-xl flex items-center justify-between text-sm font-semibold shadow-xs">
        <div class="flex items-center gap-2.5">
            <i class="ph ph-check-circle text-emerald-600 text-xl font-bold"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-lg font-bold">✕</button>
    </div>
    @endif

    @php
        $badges = [
            'academia_pay' => ['label' => 'AP', 'color' => 'bg-[#031C5B]'],
            'stripe' => ['label' => 'ST', 'color' => 'bg-indigo-600'],
            'razorpay' => ['label' => 'RP', 'color' => 'bg-blue-600'],
            'paystack' => ['label' => 'PS', 'color' => 'bg-teal-500'],
            'flutterwave' => ['label' => 'FL', 'color' => 'bg-amber-500'],
            'wave' => ['label' => 'WV', 'color' => 'bg-sky-400'],
            'cash' => ['label' => 'ESP', 'color' => 'bg-emerald-600'],
        ];
    @endphp

    <div class="space-y-4">
        @foreach($gateways as $slug => $gateway)
            @php $badge = $badges[$slug] ?? ['label' => strtoupper(substr($gateway->name, 0, 2)), 'color' => 'bg-slate-500']; @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <!-- Header: icon + name + toggle -->
                <div class="flex items-center justify-between p-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl {{ $badge['color'] }} text-white flex items-center justify-center font-black text-sm shadow-sm">
                            {{ $badge['label'] }}
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-base font-bold text-slate-900">{{ $gateway->name }}</span>
                            @if($slug === 'academia_pay')
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-purple-100 text-purple-700">Natif</span>
                            @elseif($slug === 'cash')
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-700">Manuel</span>
                            @endif
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" form="gateway-form-{{ $slug }}" name="status" value="active" class="sr-only peer"
                            onchange="toggleGatewayConfig('{{ $slug }}', this.checked)" {{ $gateway->isActive() ? 'checked' : '' }}>
                        <div class="w-12 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-6 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:border-slate-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#031C5B]"></div>
                    </label>
                </div>

                <!-- Config panel, revealed once the toggle is on -->
                <div id="gateway-config-{{ $slug }}" class="gateway-config-panel {{ $gateway->isActive() ? '' : 'hidden' }} border-t border-slate-100 bg-slate-50/40 p-6">
                    @if(in_array($slug, ['academia_pay', 'cash']))
                        <form id="gateway-form-{{ $slug }}" action="{{ route('superadmin.payment-gateways.update', $slug) }}" method="POST" class="flex items-center justify-between gap-4">
                            @csrf
                            @method('PUT')
                            <p class="text-sm {{ $slug === 'cash' ? 'text-emerald-700' : 'text-indigo-700' }} font-medium">
                                @if($slug === 'cash')
                                    Paiement manuel — l'école remet les espèces à un représentant de la plateforme ; le superadmin confirme la réception depuis Facturation & Revenus.
                                @else
                                    Géré nativement par la plateforme — aucune clé API ni webhook externe requis.
                                @endif
                            </p>
                            <button type="submit" class="flex items-center gap-2 bg-[#031C5B] text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-blue-900 transition shadow-sm cursor-pointer shrink-0">
                                <i class="ph ph-floppy-disk text-lg font-bold"></i> Enregistrer
                            </button>
                        </form>
                    @else
                    <form id="gateway-form-{{ $slug }}" action="{{ route('superadmin.payment-gateways.update', $slug) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Clé API {{ $gateway->name }}
                                @if($slug !== 'wave') <span class="text-red-500">*</span> @endif
                            </label>
                            <input type="password" name="api_key" value="" autocomplete="new-password" placeholder="{{ $gateway->api_key ? '••••••••••••••••  (configurée — laisser vide pour ne pas changer)' : 'Clé API ' . $gateway->name }}" class="w-full bg-white border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium transition">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Clé secrète {{ $gateway->name }}
                                @if(!in_array($slug, ['wave'])) <span class="text-red-500">*</span> @endif
                            </label>
                            @if($slug === 'wave')
                                <input type="text" disabled value="Non utilisé pour la réception des paiements" class="w-full bg-slate-100 border border-slate-200 text-slate-400 rounded-lg px-4 py-2.5 outline-none font-medium">
                            @else
                                <input type="password" name="secret_key" value="" autocomplete="new-password" placeholder="{{ $gateway->secret_key ? '••••••••••••••••  (configurée — laisser vide pour ne pas changer)' : 'Clé secrète ' . $gateway->name }}" class="w-full bg-white border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium transition">
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Secret du webhook {{ $gateway->name }}
                                @if($slug !== 'paystack') <span class="text-red-500">*</span> @endif
                            </label>
                            @if($slug === 'paystack')
                                <input type="text" disabled value="Non utilisé — PayStack signe avec la Clé secrète" class="w-full bg-slate-100 border border-slate-200 text-slate-400 rounded-lg px-4 py-2.5 outline-none font-medium">
                            @else
                                <input type="password" name="webhook_secret" value="" autocomplete="new-password" placeholder="{{ $gateway->webhook_secret ? '••••••••••••••••  (configuré — laisser vide pour ne pas changer)' : $gateway->name . ' Webhook Secret' }}" class="w-full bg-white border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium transition">
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">URL du webhook {{ $gateway->name }}</label>
                            <input type="text" readonly value="{{ route('subscription.webhook', $slug) }}" onclick="this.select()" class="w-full bg-slate-100 border border-slate-200 text-slate-600 rounded-lg px-4 py-2.5 outline-none font-medium cursor-text">
                        </div>

                        <div class="md:col-span-2 flex items-center justify-between pt-2">
                            <p class="text-xs text-slate-400">À renseigner dans le tableau de bord {{ $gateway->name }} comme URL de notification webhook.</p>
                            <button type="submit" class="flex items-center gap-2 bg-[#031C5B] text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-blue-900 transition shadow-sm cursor-pointer shrink-0">
                                <i class="ph ph-floppy-disk text-lg font-bold"></i> Enregistrer {{ $gateway->name }}
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <script>
        function toggleGatewayConfig(slug, isOn) {
            const panel = document.getElementById('gateway-config-' + slug);
            if (panel) panel.classList.toggle('hidden', !isOn);
        }
    </script>
@endsection

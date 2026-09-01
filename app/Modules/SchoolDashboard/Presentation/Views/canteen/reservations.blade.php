@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::canteen._tabs')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Réservations & Facturation</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Gérez les repas, les soldes et générez des rapports comptables.</p>
        </div>
        <a href="{{ route('school.canteen.reservations.export') }}" class="flex items-center gap-2 bg-purple-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-purple-700 transition shadow-sm">
            <i class="ph-bold ph-file-text text-lg"></i> Générer Rapport Mensuel
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="p-4 text-sm text-red-800 rounded-xl bg-red-50 border border-red-100" role="alert">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Repas Prévus (Aujourd'hui)</p>
                <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center text-blue-600"><i class="ph-fill ph-fork-knife text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ number_format($mealsToday, 0, ',', ' ') }}</h3>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Solde Global Négatif</p>
                <div class="w-9 h-9 rounded-full bg-red-50 flex items-center justify-center text-red-600"><i class="ph-fill ph-coins text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold {{ $negativeBalanceTotal < 0 ? 'text-red-600' : 'text-slate-800' }}">{{ number_format(abs($negativeBalanceTotal), 0, ',', ' ') }} <span class="text-base text-slate-400 font-semibold">FCFA</span></h3>
        </div>
        <div class="bg-gradient-to-br from-[#F5F3FF] to-purple-50/50 border border-purple-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <i class="ph-fill ph-sparkle text-purple-600 text-lg"></i>
                <h3 class="font-extrabold text-slate-800 text-[13px]">Prédiction IA</h3>
            </div>
            <p class="text-[12px] text-slate-600 font-medium leading-relaxed" id="canteenForecastText">Analyse des repas de la semaine en cours...</p>
            <a href="{{ route('school.canteen.inventory') }}" class="inline-flex items-center gap-1 mt-2 text-[12.5px] font-bold text-purple-700 hover:underline">Ajuster les commandes <i class="ph-bold ph-arrow-right"></i></a>
        </div>
    </div>

    <!-- Commandes de la semaine -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-[16px] font-bold text-slate-900">Commandes de la Semaine</h3>
            <p class="text-slate-500 text-[12.5px] font-medium mt-0.5">Choix confirmés par les parents depuis l'application — semaine du {{ \Illuminate\Support\Carbon::parse($week)->translatedFormat('d') }} au {{ \Illuminate\Support\Carbon::parse($week)->endOfWeek(\Illuminate\Support\Carbon::SUNDAY)->translatedFormat('d F Y') }}.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Jour</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Élève</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Classe</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Repas</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Plat choisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($todaysOrders as $order)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-3 text-[13px] font-semibold text-slate-600">{{ $order->date->translatedFormat('D d/m') }}</td>
                            <td class="px-5 py-3 text-[13px] font-bold text-slate-800">{{ $order->student->first_name }} {{ $order->student->last_name }}</td>
                            <td class="px-5 py-3 text-[13px] font-semibold text-slate-600">{{ $order->student->academicClass->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-[13px] font-semibold text-slate-600">{{ \App\Modules\Canteen\Domain\Models\CanteenReservation::SLOT_LABELS[$order->slot] ?? $order->slot }}</td>
                            <td class="px-5 py-3 text-[13px] font-semibold text-slate-800">{{ $order->menuItem->title ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-500 font-medium">Aucune commande confirmée cette semaine.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Fréquentation -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Fréquentation Journalière — Cette Semaine</h3>
            @php $maxCount = max(1, collect($dailyCounts)->max('count')); @endphp
            <div class="flex items-end justify-between gap-3 h-52">
                @foreach($dailyCounts as $day)
                    @php $heightPct = $day['count'] > 0 ? max(6, round(($day['count'] / $maxCount) * 100)) : 3; @endphp
                    <div class="flex-1 flex flex-col items-center justify-end h-full gap-2">
                        <span class="text-[11px] font-bold text-slate-500">{{ $day['count'] }}</span>
                        <div class="w-full max-w-[52px] bg-[#1E3A8A] rounded-t-lg" style="height: {{ $heightPct }}%"></div>
                        <span class="text-[11px] font-semibold text-slate-400 uppercase">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <h3 class="text-[15px] font-bold text-slate-900 mb-4">Filtres Rapides</h3>
            <form method="GET" action="{{ route('school.canteen.reservations') }}" class="space-y-4">
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Classe / Groupe</label>
                    <select name="class_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-[13px] font-semibold rounded-lg px-3 py-2.5">
                        <option value="">Toutes les classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ (string) $filters['class_id'] === (string) $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Statut</label>
                    <label class="flex items-center gap-2 text-[13px] font-semibold text-slate-600">
                        <input type="radio" name="status" value="demi_pensionnaire" onchange="this.form.submit()" {{ $filters['status'] === 'demi_pensionnaire' ? 'checked' : '' }} class="text-[#031C5B]"> Demi-pensionnaire
                    </label>
                    <label class="flex items-center gap-2 text-[13px] font-semibold text-slate-600 mt-1.5">
                        <input type="radio" name="status" value="externe" onchange="this.form.submit()" {{ $filters['status'] === 'externe' ? 'checked' : '' }} class="text-[#031C5B]"> Externe
                    </label>
                    @if($filters['status'])
                    <a href="{{ route('school.canteen.reservations') }}" class="text-[11.5px] text-slate-400 hover:text-slate-600 mt-1 inline-block">Réinitialiser</a>
                    @endif
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Recherche</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Nom de l'élève ou du personnel..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[13px] rounded-lg px-3 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
                <button type="submit" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-[12.5px] font-bold transition">Rechercher</button>
            </form>
        </div>
    </div>

    <!-- Liste -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-[16px] font-bold text-slate-900">Liste des Élèves & Personnel</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Nom Complet</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Groupe</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Statut</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Repas (mois)</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Solde Compte</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($accounts as $account)
                        @php $holder = $account->holder; @endphp
                        @continue(!$holder)
                        <tr class="hover:bg-slate-50/50 transition" x-data="{ open: false, mealOpen: false, creditOpen: false }">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-[#031C5B]/10 text-[#031C5B] flex items-center justify-center font-bold text-[11px]">
                                        {{ substr($holder->first_name, 0, 1) }}{{ substr($holder->last_name, 0, 1) }}
                                    </div>
                                    <span class="text-[13px] font-bold text-slate-800">{{ $holder->first_name }} {{ $holder->last_name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">
                                {{ $account->holder_type === 'student' ? ($holder->academicClass->name ?? '-') : 'Personnel' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $account->status === 'demi_pensionnaire' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $account->status === 'demi_pensionnaire' ? 'Demi-Pens.' : 'Externe' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">
                                {{ $account->meals_this_month }} / {{ $weekdaysThisMonth }}
                            </td>
                            <td class="px-5 py-4 text-[13px] font-bold {{ $account->balance < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ $account->balance >= 0 ? '' : '-' }}{{ number_format(abs($account->balance), 2, ',', ' ') }} FCFA
                            </td>
                            <td class="px-5 py-4 text-right relative">
                                <button type="button" @click="open = !open" @click.away="open = false" class="p-1.5 text-slate-400 hover:text-[#031C5B] rounded-lg transition"><i class="ph-bold ph-dots-three-vertical text-xl"></i></button>
                                <div x-show="open" x-cloak class="absolute right-5 top-full mt-1 w-52 bg-white border border-slate-200 rounded-xl shadow-lg z-20 py-1 text-left" style="display: none;">
                                    <button type="button" @click="mealOpen = true; open = false" class="w-full flex items-center gap-2 px-4 py-2 text-[12.5px] font-semibold text-slate-700 hover:bg-slate-50">
                                        <i class="ph-bold ph-fork-knife"></i> Enregistrer un repas
                                    </button>
                                    <button type="button" @click="creditOpen = true; open = false" class="w-full flex items-center gap-2 px-4 py-2 text-[12.5px] font-semibold text-slate-700 hover:bg-slate-50">
                                        <i class="ph-bold ph-plus-circle"></i> Créditer le compte
                                    </button>
                                </div>

                                <!-- Record Meal Modal -->
                                <div x-show="mealOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
                                    <div @click.away="mealOpen = false" class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden text-left">
                                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                                            <h3 class="text-[15px] font-extrabold text-[#031C5B]">Enregistrer un repas</h3>
                                            <button type="button" @click="mealOpen = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
                                        </div>
                                        <form action="{{ route('school.canteen.reservations.meals.store') }}" method="POST" class="p-5 space-y-3">
                                            @csrf
                                            <input type="hidden" name="account_id" value="{{ $account->id }}">
                                            <p class="text-[12.5px] text-slate-500">{{ $holder->first_name }} {{ $holder->last_name }}</p>
                                            <div class="space-y-1.5">
                                                <label class="block text-[12px] font-bold text-slate-500 uppercase">Prix (FCFA)</label>
                                                <input type="number" step="0.01" min="0" name="price" required value="500" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-3 py-2 outline-none focus:border-[#031C5B]">
                                            </div>
                                            <button type="submit" class="w-full py-2.5 bg-[#031C5B] text-white font-bold text-[13px] rounded-xl hover:bg-[#031C5B]/90 transition">Confirmer</button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Credit Modal -->
                                <div x-show="creditOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
                                    <div @click.away="creditOpen = false" class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden text-left">
                                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                                            <h3 class="text-[15px] font-extrabold text-[#031C5B]">Créditer le compte</h3>
                                            <button type="button" @click="creditOpen = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
                                        </div>
                                        <form action="{{ route('school.canteen.reservations.credit') }}" method="POST" class="p-5 space-y-3">
                                            @csrf
                                            <input type="hidden" name="account_id" value="{{ $account->id }}">
                                            <p class="text-[12.5px] text-slate-500">{{ $holder->first_name }} {{ $holder->last_name }} — Solde actuel : {{ number_format($account->balance, 2, ',', ' ') }} FCFA</p>
                                            <div class="space-y-1.5">
                                                <label class="block text-[12px] font-bold text-slate-500 uppercase">Montant à créditer (FCFA)</label>
                                                <input type="number" step="0.01" min="0.01" name="amount" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-3 py-2 outline-none focus:border-[#031C5B]">
                                            </div>
                                            <button type="submit" class="w-full py-2.5 bg-emerald-700 text-white font-bold text-[13px] rounded-xl hover:bg-emerald-800 transition">Créditer</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-500 font-medium">Aucun compte trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 flex items-center justify-between">
            <span class="text-[12.5px] text-slate-500 font-semibold">{{ number_format($accounts->total(), 0, ',', ' ') }} compte(s) au total</span>
            <div class="flex items-center">{{ $accounts->links('pagination::tailwind') }}</div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        fetch('{{ route("school.canteen.reservations.ai-forecast") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            const el = document.getElementById('canteenForecastText');
            el.innerText = data.success ? data.forecast : (data.error || "Analyse IA indisponible pour le moment.");
        })
        .catch(() => {
            document.getElementById('canteenForecastText').innerText = "Erreur de communication avec le serveur.";
        });
    });
</script>
@endsection

@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addOpen: false, adjustOpen: false, adjustType: 'in' }">
    @include('SchoolDashboard::infirmary._tabs')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Pharmacie</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1 max-w-xl">Gérez les fournitures médicales et suivez les niveaux de stock.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" @click="adjustType = 'out'; adjustOpen = true" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-50 transition shadow-sm">
                <i class="ph-bold ph-pencil-simple text-lg"></i> Ajuster le Stock
            </button>
            <button type="button" @click="adjustType = 'in'; adjustOpen = true" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm">
                <i class="ph-bold ph-truck text-lg"></i> Enregistrer une Livraison
            </button>
        </div>
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
        <div class="bg-blue-50/60 border border-blue-100 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-red-500 shadow-sm"><i class="ph-fill ph-warning text-lg"></i></div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Attention</span>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $expiringSoon->count() }} <span class="text-base font-semibold text-slate-500">Article(s)</span></h3>
            <p class="text-[12px] text-slate-500 font-semibold mt-1">Expirent dans les 30 jours</p>
        </div>
        <div class="bg-blue-50/60 border border-blue-100 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-emerald-600 shadow-sm"><i class="ph-fill ph-archive text-lg"></i></div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ $globalStockLevel >= 80 ? 'Optimal' : ($globalStockLevel >= 50 ? 'À Surveiller' : 'Critique') }}</span>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $globalStockLevel }}%</h3>
            <p class="text-[12px] text-slate-500 font-semibold mt-1">Niveau Global de Stock</p>
        </div>
        <div class="bg-white border border-purple-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600"><i class="ph-fill ph-sparkle text-lg"></i></div>
                <span class="text-[10px] font-bold text-purple-600 uppercase tracking-wider">Insight IA</span>
            </div>
            <h3 class="text-[15px] font-bold text-slate-800">Risque de Rupture</h3>
            @if($stockoutRisk)
                <p class="text-[12.5px] text-slate-600 mt-1"><strong class="text-purple-700">{{ $stockoutRisk['medication']->name }}</strong> — rupture estimée dans <strong>{{ $stockoutRisk['daysRemaining'] }} jour(s)</strong> selon la consommation récente.</p>
            @else
                <p class="text-[12.5px] text-slate-600 mt-1">Pas assez de données de consommation pour estimer un risque.</p>
            @endif
        </div>
    </div>

    <!-- Inventory List -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-[16px] font-bold text-slate-900">Liste de l'Inventaire</h3>
            <button type="button" @click="addOpen = true" class="text-[12.5px] font-bold text-[#031C5B] hover:underline flex items-center gap-1"><i class="ph-bold ph-plus"></i> Nouveau Médicament</button>
        </div>
        @php
            $statusStyles = ['optimal' => 'bg-emerald-100 text-emerald-700', 'to_monitor' => 'bg-amber-100 text-amber-700', 'critical' => 'bg-red-100 text-red-700'];
            $statusLabels = ['optimal' => 'Optimal', 'to_monitor' => 'À Surveiller', 'critical' => 'Critique'];
        @endphp
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Article</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Catégorie</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Quantité</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Expiration</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Niveau d'Alerte</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($medications as $medication)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-[13.5px] font-bold text-slate-800">{{ $medication->name }}</td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $medication->category ?? '-' }}</td>
                        <td class="px-5 py-4 text-[13px] font-bold text-slate-700">
                            {{ $medication->quantity }}@if($medication->max_quantity) <span class="text-slate-400 font-medium">/ {{ $medication->max_quantity }}</span>@endif
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $medication->expiry_date ? $medication->expiry_date->format('M Y') : 'N/A' }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $statusStyles[$medication->status] }}">{{ $statusLabels[$medication->status] }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-500 font-medium">Aucun médicament enregistré.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 flex items-center justify-between">
            <span class="text-[12.5px] text-slate-500 font-semibold">{{ number_format($medications->total(), 0, ',', ' ') }} article(s) au total</span>
            <div class="flex items-center">{{ $medications->links('pagination::tailwind') }}</div>
        </div>
    </div>

    <!-- Recent Movements -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-[16px] font-bold text-slate-900">Mouvements Récents</h3>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($recentMovements as $movement)
            <div class="p-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full {{ $movement->type === 'in' ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center shrink-0">
                    <i class="ph-bold {{ $movement->type === 'in' ? 'ph-arrow-down' : 'ph-arrow-up' }}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-bold text-slate-800">{{ $movement->source ?: ($movement->type === 'in' ? 'Entrée de stock' : 'Sortie de stock') }}</p>
                    <p class="text-[11.5px] text-slate-500">{{ $movement->created_at->format('d/m/Y H:i') }} · {{ $movement->medication->name ?? 'Médicament supprimé' }}</p>
                </div>
                <span class="text-[13px] font-bold {{ $movement->type === 'in' ? 'text-emerald-600' : 'text-slate-500' }} shrink-0">
                    {{ $movement->type === 'in' ? '+' : '-' }}{{ $movement->quantity }}
                </span>
            </div>
            @empty
            <p class="text-slate-400 text-[13px] text-center py-10">Aucun mouvement enregistré.</p>
            @endforelse
        </div>
    </div>

    <!-- Add Medication Modal -->
    <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
        <div @click.away="addOpen = false" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-[18px] font-extrabold text-[#031C5B]">Nouveau Médicament</h3>
                <button type="button" @click="addOpen = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
            </div>
            <form action="{{ route('school.infirmary.pharmacy.medications.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Nom <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Catégorie</label>
                        <input type="text" name="category" placeholder="Ex: Antalgique, Antibiotique..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Quantité initiale <span class="text-red-500">*</span></label>
                        <input type="number" min="0" name="quantity" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Quantité max.</label>
                        <input type="number" min="1" name="max_quantity" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Seuil faible</label>
                        <input type="number" min="0" name="low_stock_threshold" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Seuil critique</label>
                        <input type="number" min="0" name="critical_threshold" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Date d'expiration</label>
                    <input type="date" name="expiry_date" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="addOpen = false" class="px-5 py-2.5 border border-slate-200 rounded-xl text-[13px] font-bold text-slate-600 hover:bg-slate-50 transition">Annuler</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Adjust Stock Modal -->
    <div x-show="adjustOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
        <div @click.away="adjustOpen = false" class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-[16px] font-extrabold text-[#031C5B]" x-text="adjustType === 'in' ? 'Enregistrer une Livraison' : 'Ajuster le Stock'"></h3>
                <button type="button" @click="adjustOpen = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
            </div>
            <form action="{{ route('school.infirmary.pharmacy.adjust') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="type" x-bind:value="adjustType">
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Médicament</label>
                    <select name="medication_id" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                        <option value="">Sélectionner...</option>
                        @foreach($medications as $medication)
                            <option value="{{ $medication->id }}">{{ $medication->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Quantité</label>
                    <input type="number" min="1" name="quantity" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Source / Motif</label>
                    <input type="text" name="source" placeholder="Ex: Fournisseur, Utilisation..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
                <button type="submit" class="w-full py-2.5 bg-[#031C5B] text-white font-bold text-[13px] rounded-xl hover:bg-[#031C5B]/90 transition">Confirmer</button>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addOpen: false }">
    @include('SchoolDashboard::canteen._tabs')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Gestion des Stocks</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1 max-w-xl">Suivez les niveaux de stock, les livraisons et anticipez les besoins d'approvisionnement.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('school.canteen.inventory.export') }}" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-50 transition shadow-sm">
                <i class="ph-bold ph-download-simple text-lg"></i> Export
            </a>
            <button type="button" @click="addOpen = true" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm">
                <i class="ph-bold ph-plus text-lg"></i> Nouvelle Entrée
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Current Stock -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-[16px] font-bold text-slate-900">Stock Actuel</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F8FAFC]">
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Produit</th>
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Catégorie</th>
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Quantité</th>
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Statut</th>
                        </tr>
                    </thead>
                    @php
                        $statusStyles = ['optimal' => 'bg-emerald-100 text-emerald-700', 'low_stock' => 'bg-purple-100 text-purple-700', 'critical' => 'bg-red-100 text-red-700', 'expiring_soon' => 'bg-amber-100 text-amber-700'];
                        $statusLabels = ['optimal' => 'Optimal', 'low_stock' => 'Stock Faible', 'critical' => 'Critique', 'expiring_soon' => 'Bientôt Périmé'];
                    @endphp
                    <tbody class="divide-y divide-slate-100">
                        @forelse($products as $product)
                        <tr class="hover:bg-slate-50/50 transition {{ $product->status === 'critical' ? 'bg-red-50/40' : '' }}">
                            <td class="px-5 py-4">
                                <p class="text-[13.5px] font-bold {{ $product->status === 'critical' ? 'text-red-700' : 'text-slate-800' }}">{{ $product->name }}</p>
                                @if($product->expiry_date)
                                    <p class="text-[11px] {{ $product->status === 'expiring_soon' ? 'text-amber-600 font-bold' : 'text-slate-400' }}">Exp: {{ $product->expiry_date->format('d M Y') }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $product->category ?? '-' }}</td>
                            <td class="px-5 py-4 text-[13px] font-bold text-slate-700">{{ rtrim(rtrim($product->quantity, '0'), '.') }} <span class="text-slate-400 font-medium">{{ $product->unit }}</span></td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $statusStyles[$product->status] }}">
                                    @if($product->status === 'critical')<i class="ph-fill ph-warning"></i>@endif
                                    {{ $statusLabels[$product->status] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-slate-500 font-medium">Aucun produit enregistré.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[12.5px] text-slate-500 font-semibold">{{ number_format($products->total(), 0, ',', ' ') }} produit(s) au total</span>
                <div class="flex items-center">{{ $products->links('pagination::tailwind') }}</div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- AI Procurement Insight (décoratif) -->
            <div class="bg-gradient-to-br from-[#F5F3FF] to-purple-50/50 border border-purple-100 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <i class="ph-fill ph-sparkle text-purple-600 text-lg"></i>
                    <h3 class="font-extrabold text-slate-800 text-[14px]">Approvisionnement IA</h3>
                </div>
                <p class="text-[12.5px] text-slate-600 font-medium leading-relaxed">
                    Surveillez les produits en stock faible ou critique ci-contre et anticipez vos commandes fournisseurs en conséquence.
                </p>
            </div>

            <!-- Manual Adjustment -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h3 class="text-[15px] font-bold text-slate-900 mb-4">Ajustement Manuel</h3>
                <form action="{{ route('school.canteen.inventory.adjust') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-wider">Produit</label>
                        <select name="product_id" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                            <option value="">Sélectionner un produit...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-wider">Type</label>
                            <select name="type" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                                <option value="in">Entrée</option>
                                <option value="out">Sortie</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-wider">Qté</label>
                            <input type="number" step="0.01" min="0.01" name="quantity" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-wider">Catégorie (pour les sorties)</label>
                        <select name="category" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                            <option value="usage">Utilisation cuisine</option>
                            <option value="waste">Gaspillage / Perte</option>
                            <option value="expired">Périmé</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-wider">Source / Motif</label>
                        <input type="text" name="source" placeholder="Ex: Livraison SenAgri Ltd" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-[#031C5B] text-white font-bold text-[13px] rounded-xl hover:bg-[#031C5B]/90 transition">Mettre à jour le stock</button>
                </form>
            </div>
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
                    <p class="text-[11.5px] text-slate-500">{{ $movement->created_at->format('d/m/Y H:i') }} · {{ $movement->product->name ?? 'Produit supprimé' }}</p>
                </div>
                <span class="text-[13px] font-bold {{ $movement->type === 'in' ? 'text-emerald-600' : 'text-slate-500' }} shrink-0">
                    {{ $movement->type === 'in' ? '+' : '-' }}{{ rtrim(rtrim($movement->quantity, '0'), '.') }} {{ $movement->product->unit ?? '' }}
                </span>
            </div>
            @empty
            <p class="text-slate-400 text-[13px] text-center py-10">Aucun mouvement enregistré.</p>
            @endforelse
        </div>
    </div>

    <!-- Add Product Modal -->
    <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
        <div @click.away="addOpen = false" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-[18px] font-extrabold text-[#031C5B]">Nouveau Produit</h3>
                <button type="button" @click="addOpen = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
            </div>
            <form action="{{ route('school.canteen.inventory.products.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Nom <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Catégorie</label>
                        <input type="text" name="category" placeholder="Ex: Produce, Dry Goods..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Unité <span class="text-red-500">*</span></label>
                        <input type="text" name="unit" value="kg" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Quantité initiale <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="quantity" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Seuil stock faible</label>
                        <input type="number" step="0.01" min="0" name="low_stock_threshold" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Seuil critique</label>
                        <input type="number" step="0.01" min="0" name="critical_threshold" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
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
</div>
@endsection

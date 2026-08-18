@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::canteen._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Tableau de Bord Principal</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Aperçu en temps réel des opérations de la cantine scolaire.</p>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Repas Servis Aujourd'hui</p>
                <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center text-blue-600"><i class="ph-fill ph-fork-knife text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ number_format($stats['meals_today'], 0, ',', ' ') }}</h3>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Coût Moyen par Repas</p>
                <div class="w-9 h-9 rounded-full bg-purple-50 flex items-center justify-center text-purple-600"><i class="ph-fill ph-coins text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ number_format($stats['average_price_today'], 0, ',', ' ') }} <span class="text-base text-slate-400 font-semibold">FCFA</span></h3>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Taux de Gaspillage</p>
                <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600"><i class="ph-fill ph-trash text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $stats['waste_ratio'] }}%</h3>
            <p class="text-[12px] text-slate-400 font-semibold mt-1">Ce mois-ci, sur les sorties de stock</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Équilibre Nutritionnel (décoratif) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-bold text-slate-900">Équilibre Nutritionnel</h3>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 uppercase tracking-wider"><i class="ph-fill ph-sparkle"></i> Insight IA</span>
            </div>
            <p class="text-[13px] text-slate-500 mb-5">Analyse prédictive des menus de la semaine basée sur les recommandations nationales.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="flex items-end justify-between gap-2 h-32">
                    @foreach(['LUN' => 70, 'MAR' => 55, 'MER' => 85, 'JEU' => 40, 'VEN' => 78] as $day => $height)
                        <div class="flex-1 flex flex-col items-center justify-end h-full gap-1.5">
                            <div class="w-full max-w-[24px] bg-emerald-200 rounded-t-md" style="height: {{ $height }}%"></div>
                            <span class="text-[10px] font-bold text-slate-400">{{ $day }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100">
                    <p class="text-[12.5px] font-bold text-slate-800 flex items-center gap-1.5 mb-1"><i class="ph-fill ph-plant text-emerald-500"></i> Score Végétal</p>
                    <p class="text-[12px] text-slate-500">Bon apport en protéines végétales prévu cette semaine.</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100">
                    <p class="text-[12.5px] font-bold text-slate-800 flex items-center gap-1.5 mb-1"><i class="ph-fill ph-warning text-amber-500"></i> Alerte Sodium</p>
                    <p class="text-[12px] text-slate-500">Surveillez les plats en sauce, souvent plus salés.</p>
                </div>
            </div>
        </div>

        <!-- Alertes -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-[15px] font-bold text-slate-900 flex items-center gap-2"><i class="ph-fill ph-warning-circle text-red-500"></i> Alertes</h3>
                @if($criticalProducts->isNotEmpty())
                    <span class="w-6 h-6 rounded-full bg-red-600 text-white text-[11px] font-bold flex items-center justify-center">{{ $criticalProducts->count() }}</span>
                @endif
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($criticalProducts as $product)
                    <div class="p-4 border-l-4 border-red-500 bg-red-50/40">
                        <p class="text-[13px] font-bold text-red-700 flex items-center gap-1.5"><i class="ph-fill ph-package"></i> Rupture Imminente</p>
                        <p class="text-[12.5px] text-slate-600 mt-1">Stock critique de <strong>{{ $product->name }}</strong> ({{ $product->quantity }} {{ $product->unit }} restant(s)).</p>
                        <a href="{{ route('school.canteen.inventory') }}" class="inline-block mt-2 px-3 py-1.5 bg-[#031C5B] text-white rounded-lg text-[11.5px] font-bold hover:bg-[#031C5B]/90 transition">Voir le stock</a>
                    </div>
                @empty
                    <p class="text-slate-400 text-[13px] text-center py-10">Aucune alerte pour le moment.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

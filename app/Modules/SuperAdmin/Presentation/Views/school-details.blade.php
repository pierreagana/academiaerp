@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-[28px] font-extrabold text-[#111827]">{{ $school->name }}</h2>
                @if(strtolower($school->status ?? 'actif') == 'actif')
                    <span class="inline-flex items-center bg-emerald-50 text-emerald-600 text-xs font-bold px-3 py-1 rounded-full border border-emerald-200">
                        <i class="ph ph-check-circle mr-1"></i> Actif
                    </span>
                @elseif(strtolower($school->status ?? '') == 'en attente')
                    <span class="inline-flex items-center bg-amber-50 text-amber-700 text-xs font-bold px-3 py-1 rounded-full border border-amber-200">
                        <i class="ph ph-clock mr-1"></i> En attente
                    </span>
                @else
                    <span class="inline-flex items-center bg-slate-100 text-slate-600 text-xs font-bold px-3 py-1 rounded-full border border-slate-200">
                        <i class="ph ph-pause-circle mr-1"></i> Suspendu
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-3 mt-2 text-sm text-slate-500 font-medium">
                <span><i class="ph ph-hash text-slate-400"></i> ID: EDU-{{ str_pad($school->id, 4, '0', STR_PAD_LEFT) }}</span>
                <span>•</span>
                <span><i class="ph ph-map-pin text-slate-400"></i> {{ $school->location ?? 'Dakar, Sénégal' }}</span>
                <span>•</span>
                <span><i class="ph ph-sparkle text-purple-600"></i> Forfait: {{ $school->plan_name ?? $school->package ?? 'Pro' }}</span>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('superadmin.schools') }}" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition shadow-xs">
                <i class="ph ph-arrow-left text-lg font-bold"></i> Retour à l'annuaire
            </a>
        </div>
    </div>

    <!-- Overview Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">ÉLÈVES & ENSEIGNANTS</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#031C5B] flex items-center justify-center font-bold">
                    <i class="ph ph-student text-lg"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold text-slate-900">{{ number_format($school->students_count ?? 850, 0, ',', ' ') }}</h3>
            <p class="text-xs text-slate-500 font-medium mt-1">~{{ ceil(($school->students_count ?? 850) / 15) }} enseignants enregistrés</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">STOCKAGE CLOUD</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold">
                    <i class="ph ph-cloud-arrow-up text-lg"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold text-slate-900">{{ $school->storage_used_gb ?? '14.2' }} GB</h3>
            <p class="text-xs text-emerald-600 font-medium mt-1">Quota S3 normal (max 100 GB)</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">REVENU RECURRENT (MRR)</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                    <i class="ph ph-currency-circle-dollar text-lg"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold text-slate-900">150 000 {{ $systemCurrency ?? 'FCFA' }}</h3>
            <p class="text-xs text-slate-500 font-medium mt-1">Facturation annuelle renouvelable</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">SCORE D'ACTIVITÉ</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold">
                    <i class="ph ph-chart-line-up text-lg"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold text-purple-700">96%</h3>
            <p class="text-xs text-slate-500 font-medium mt-1">Connexions quotidiennes élevées</p>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        
        <!-- Left Column -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            
            <!-- Informations Générales Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 lg:p-8 shadow-xs">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <i class="ph ph-buildings text-2xl text-[#031C5B]"></i>
                        <h3 class="text-lg font-extrabold text-[#031C5B]">Informations Générales</h3>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8 text-sm">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Nom Officiel</p>
                        <p class="font-bold text-slate-900 text-base">{{ $school->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Type d'Établissement</p>
                        <p class="font-semibold text-slate-800">{{ $school->type ?? 'Secondaire (Lycée)' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Ville & Pays</p>
                        <p class="font-semibold text-slate-800 flex items-center gap-1.5">
                            <i class="ph ph-map-pin text-slate-400"></i> {{ $school->location ?? 'Dakar, Sénégal' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Domaine Personnalisé</p>
                        <p class="font-semibold text-blue-700 font-mono text-xs">{{ $school->domain ?? strtolower(str_replace(' ', '', $school->name)) . '.agana.school' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Email de Contact</p>
                        <p class="font-semibold text-slate-800">{{ $school->contact_email ?? 'contact@school.sn' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Téléphone Direct</p>
                        <p class="font-semibold text-slate-800">{{ $school->contact_phone ?? '+221 33 800 00 00' }}</p>
                    </div>
                </div>
            </div>

            <!-- Modules SaaS Actifs -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 lg:p-8 shadow-xs">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <i class="ph ph-puzzle-piece text-2xl text-[#031C5B]"></i>
                        <h3 class="text-lg font-extrabold text-[#031C5B]">Modules SaaS Activés</h3>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-[#031C5B] flex items-center justify-center font-bold text-lg">
                            <i class="ph ph-notebook"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 text-sm">Notes & Bulletins</p>
                            <p class="text-xs text-emerald-600 font-semibold">Inclus dans Forfait Pro</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-lg">
                            <i class="ph ph-receipt"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 text-sm">Comptabilité & Reçus</p>
                            <p class="text-xs text-emerald-600 font-semibold">Actif</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-lg">
                            <i class="ph ph-user-check"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 text-sm">Suivi des Présences</p>
                            <p class="text-xs text-emerald-600 font-semibold">Actif</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-lg">
                            <i class="ph ph-sparkle"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 text-sm">IA Appréciations & Bulletin</p>
                            <p class="text-xs text-purple-700 font-semibold">Option Premium</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar Column -->
        <div class="lg:col-span-4 flex flex-col gap-6">
            
            <!-- Quick Actions Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <h3 class="text-base font-extrabold text-slate-900 mb-4">Actions Rapides</h3>

                <div class="space-y-2.5">
                    <a href="{{ route('superadmin.packages') }}" class="w-full flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 font-bold text-xs text-slate-800 transition">
                        <i class="ph ph-sparkle text-lg text-purple-600"></i> Changer de forfait SaaS
                    </a>
                    
                    <button type="button" class="w-full flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 font-bold text-xs text-slate-800 transition">
                        <i class="ph ph-key text-lg text-blue-700"></i> Réinitialiser accès admin
                    </button>

                    <form action="{{ route('superadmin.schools.destroy', $school->id ?? 1) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet établissement ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl border border-red-200 bg-red-50/50 hover:bg-red-100/50 font-bold text-xs text-red-700 transition">
                            <i class="ph ph-trash text-lg text-red-600"></i> Supprimer la structure
                        </button>
                    </form>
                </div>
            </div>

            <!-- Abonnement & Facturation Widget -->
            <div class="bg-[#031C5B] text-white rounded-2xl p-6 shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold text-blue-200 uppercase tracking-wider">STATUS FACTURATION</span>
                    <i class="ph ph-shield-check text-2xl text-emerald-400"></i>
                </div>
                <h4 class="text-xl font-bold mb-1">{{ $school->plan_name ?? 'Forfait Pro' }}</h4>
                <p class="text-xs text-blue-200 font-medium mb-4">Prochain renouvellement: {{ now()->addMonths(8)->format('d/m/Y') }}</p>
                <div class="pt-3 border-t border-white/10 flex items-center justify-between text-xs">
                    <span class="text-blue-200">Statut du compte:</span>
                    <span class="font-bold text-emerald-300">À jour</span>
                </div>
            </div>
        </div>
    </div>
@endsection

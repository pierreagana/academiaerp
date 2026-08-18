@extends('SchoolDashboard::layouts.app')

@section('content')
<div x-data="{ domainOpen: false }" class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Configuration des Référentiels</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Définissez et gérez les domaines de compétences par cycle scolaire.</p>
        </div>
        <button type="button" @click="domainOpen = true" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition">
            <i class="ph-bold ph-plus text-lg"></i> Nouveau Domaine
        </button>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    @if($domains->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-16 text-center">
            <i class="ph-bold ph-tree-structure text-5xl text-slate-300 mb-4"></i>
            <p class="text-slate-500 font-medium">Aucun domaine défini. Créez-en un pour construire le référentiel de compétences.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($domains as $domain)
            <div x-data="{ open: {{ $loop->first ? 'true' : 'false' }}, subOpen: false, subdomainId: null }" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <button @click="open = !open" class="w-full flex items-center justify-between p-5 hover:bg-slate-50/60 transition">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-[#031C5B]/10 text-[#031C5B] flex items-center justify-center"><i class="ph-bold ph-graduation-cap text-lg"></i></span>
                        <div class="text-left">
                            <p class="text-[15px] font-extrabold text-slate-900">{{ $domain->name }}</p>
                            <p class="text-[12px] text-slate-500">{{ $domain->cycle }} &middot; {{ $domain->subdomains->count() }} sous-domaine(s)</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <form action="{{ route('school.report-card.domains.destroy', $domain->id) }}" method="POST" onsubmit="event.stopPropagation(); return confirm('Supprimer ce domaine et tout son contenu ?');" onclick="event.stopPropagation()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-600 transition"><i class="ph-bold ph-trash text-[16px]"></i></button>
                        </form>
                        <i class="ph ph-caret-down text-[16px] text-slate-400 transition-transform" :class="{ 'rotate-180': open }"></i>
                    </div>
                </button>

                <div x-show="open" x-collapse class="border-t border-slate-100 px-5 pb-5">
                    @foreach($domain->subdomains as $subdomain)
                    <div class="mt-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[13px] font-bold text-slate-700 flex items-center gap-1.5"><i class="ph-bold ph-arrow-elbow-down-right text-slate-400"></i> {{ $subdomain->name }}</p>
                            <form action="{{ route('school.report-card.subdomains.destroy', $subdomain->id) }}" method="POST" onsubmit="return confirm('Supprimer ce sous-domaine ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-600 transition"><i class="ph-bold ph-x text-[13px]"></i></button>
                            </form>
                        </div>
                        <div class="pl-6 space-y-1.5">
                            @forelse($subdomain->competencies as $competency)
                                <div class="flex items-center justify-between py-1.5 border-b border-slate-50 last:border-0">
                                    <span class="text-[13px] text-slate-600">{{ $competency->statement }}</span>
                                    <form action="{{ route('school.report-card.competencies.destroy', $competency->id) }}" method="POST" onsubmit="return confirm('Supprimer cette compétence ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-300 hover:text-red-600 transition"><i class="ph-bold ph-x text-[12px]"></i></button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-[12px] text-slate-400 italic">Aucune compétence définie pour ce sous-domaine.</p>
                            @endforelse

                            <form action="{{ route('school.report-card.competencies.store') }}" method="POST" class="flex items-center gap-2 pt-2">
                                @csrf
                                <input type="hidden" name="subdomain_id" value="{{ $subdomain->id }}">
                                <input type="text" name="statement" required placeholder="Ajouter une compétence..." class="flex-1 bg-slate-50 border border-slate-200 text-[12.5px] rounded-lg px-3 py-2 outline-none focus:border-[#031C5B]">
                                <button type="submit" class="text-[#031C5B] font-bold text-[12px] px-3 py-2 hover:bg-slate-50 rounded-lg">Ajouter</button>
                            </form>
                        </div>
                    </div>
                    @endforeach

                    <form action="{{ route('school.report-card.subdomains.store') }}" method="POST" class="flex items-center gap-2 mt-4 pt-4 border-t border-dashed border-slate-200">
                        @csrf
                        <input type="hidden" name="domain_id" value="{{ $domain->id }}">
                        <input type="text" name="name" required placeholder="Nom du nouveau sous-domaine..." class="flex-1 bg-slate-50 border border-slate-200 text-[12.5px] rounded-lg px-3 py-2 outline-none focus:border-[#031C5B]">
                        <button type="submit" class="flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[12px] px-3 py-2 rounded-lg transition">
                            <i class="ph-bold ph-plus"></i> Sous-domaine
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    <!-- Create Domain Modal -->
    <div x-show="domainOpen" x-cloak class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" style="display:none">
        <div @click.outside="domainOpen = false" class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-[18px] font-extrabold text-[#0F172A] mb-4">Nouveau Domaine</h3>
            <form action="{{ route('school.report-card.domains.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Cycle</label>
                    <select name="cycle" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] font-medium rounded-xl px-4 py-3 outline-none focus:border-[#031C5B]">
                        @foreach($cycles as $cycle)
                            <option value="{{ $cycle }}">{{ $cycle }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Nom du domaine</label>
                    <input type="text" name="name" required placeholder="Ex: Mathématiques" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] font-medium rounded-xl px-4 py-3 outline-none focus:border-[#031C5B]">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="domainOpen = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[13.5px] rounded-xl hover:bg-slate-50">Annuler</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[13.5px] rounded-xl hover:bg-[#031C5B]/90">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>[x-cloak]{display:none!important}</style>
@endsection

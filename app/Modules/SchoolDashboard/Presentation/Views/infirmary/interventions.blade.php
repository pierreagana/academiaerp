@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::infirmary._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Nouvelle Intervention</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Enregistrez une nouvelle visite d'élève au centre de santé.</p>
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

    <form action="{{ route('school.infirmary.interventions.store') }}" method="POST" x-data="{ motive: '{{ old('motive', '') }}', decision: '{{ old('decision', 'retour_classe') }}' }">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- New Incident -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                <h3 class="text-[16px] font-bold text-slate-900">Nouvelle Intervention</h3>

                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Recherche Élève</label>
                    <input type="text" name="student_identifier" value="{{ old('student_identifier') }}" required placeholder="Rechercher par nom ou matricule..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Heure d'Arrivée</label>
                        <input type="time" name="arrival_time" value="{{ old('arrival_time', now()->format('H:i')) }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Température (°C)</label>
                        <input type="number" step="0.1" min="30" max="45" name="temperature" value="{{ old('temperature') }}" placeholder="37.0" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    </div>
                </div>

                <div class="space-y-1.5" x-data="{ addingMotive: false }">
                    <div class="flex items-center justify-between">
                        <label class="block text-[13px] font-bold text-slate-700">Motif de Consultation</label>
                        <button type="button" @click="addingMotive = !addingMotive" class="text-[11.5px] font-bold text-[#031C5B] hover:underline">+ Gérer les motifs</button>
                    </div>
                    <div x-show="addingMotive" x-cloak class="flex items-center gap-2 mb-2">
                        <form action="{{ route('school.infirmary.interventions.motives.store') }}" method="POST" class="flex items-center gap-1.5 flex-1">
                            @csrf
                            <input type="text" name="name" required placeholder="Nouveau motif..." class="flex-1 bg-slate-50 border border-slate-200 text-slate-900 text-[12.5px] rounded-lg px-2.5 py-1.5 outline-none focus:border-[#031C5B]">
                            <button type="submit" class="px-2.5 py-1.5 bg-[#031C5B] text-white rounded-lg text-[11.5px] font-bold">OK</button>
                        </form>
                    </div>
                    <div x-show="addingMotive" x-cloak class="flex flex-wrap gap-1.5 mb-2">
                        @foreach($motives as $motive)
                            <form action="{{ route('school.infirmary.interventions.motives.destroy', $motive->id) }}" method="POST" onsubmit="return confirm('Retirer ce motif ?');" class="inline-flex items-center gap-1 bg-slate-100 rounded-full px-2.5 py-1">
                                @csrf
                                @method('DELETE')
                                <span class="text-[11.5px] font-semibold text-slate-600">{{ $motive->name }}</span>
                                <button type="submit" class="text-slate-400 hover:text-red-500"><i class="ph-bold ph-x text-[10px]"></i></button>
                            </form>
                        @endforeach
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @forelse($motives as $motive)
                            <button type="button" @click="motive = '{{ $motive->name }}'" class="px-4 py-2 rounded-full text-[13px] font-bold border transition" :class="motive === '{{ $motive->name }}' ? 'bg-[#031C5B] text-white border-[#031C5B]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#031C5B]'">
                                {{ $motive->name }}
                            </button>
                        @empty
                            <p class="text-[12.5px] text-slate-400">Aucun motif défini. Utilisez "Gérer les motifs" pour en ajouter.</p>
                        @endforelse
                    </div>
                    <input type="hidden" name="motive" x-model="motive">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Soins Administrés & Médicaments</label>
                    <textarea name="care_notes" rows="4" placeholder="Détaillez le traitement fourni..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] resize-y">{{ old('care_notes') }}</textarea>
                </div>
            </div>

            <!-- Decision -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <h3 class="text-[16px] font-bold text-slate-900">Décision d'Intervention</h3>
                @foreach(\App\Modules\Infirmary\Domain\Models\Intervention::DECISIONS as $key => $label)
                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition" :class="decision === '{{ $key }}' ? 'border-[#031C5B] bg-blue-50/50' : 'border-slate-200'">
                        <input type="radio" name="decision" value="{{ $key }}" x-model="decision" class="text-[#031C5B] focus:ring-[#031C5B]">
                        <span class="text-[13.5px] font-bold text-slate-700">{{ $label }}</span>
                    </label>
                @endforeach

                <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-4">
                    <p class="text-[11px] font-extrabold text-purple-600 uppercase tracking-wider mb-1.5 flex items-center gap-1.5"><i class="ph-fill ph-sparkle"></i> Insight IA</p>
                    <p class="text-[12.5px] text-slate-700 leading-relaxed">Sur la base de cas similaires récents, pensez à vérifier une éventuelle allergie saisonnière locale.</p>
                </div>

                <button type="submit" class="w-full py-3.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition shadow-sm">
                    Enregistrer l'Intervention
                </button>
            </div>
        </div>
    </form>

    <!-- Latest Interventions -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-[16px] font-bold text-slate-900">Dernières Interventions</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Heure</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Élève</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Motif</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Statut</th>
                    </tr>
                </thead>
                @php
                    $decisionStyles = ['retour_classe' => 'bg-emerald-100 text-emerald-700', 'repos_infirmerie' => 'bg-blue-100 text-blue-700', 'parents_appeles' => 'bg-red-100 text-red-700'];
                @endphp
                <tbody class="divide-y divide-slate-100">
                    @forelse($recent as $intervention)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $intervention->arrival_time->format('H:i') }}</td>
                        <td class="px-5 py-4 text-[13.5px] font-bold text-slate-800">{{ $intervention->student->first_name ?? '' }} {{ $intervention->student->last_name ?? '' }}</td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $intervention->motive }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $decisionStyles[$intervention->decision] ?? '' }}">
                                {{ \App\Modules\Infirmary\Domain\Models\Intervention::DECISIONS[$intervention->decision] ?? $intervention->decision }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-slate-500 font-medium">Aucune intervention enregistrée.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

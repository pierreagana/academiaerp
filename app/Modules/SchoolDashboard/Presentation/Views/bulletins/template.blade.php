@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6" x-data="{
        showCoefficient: {{ old('show_coefficient', $template->show_coefficient) ? 'true' : 'false' }},
        showClassAverage: {{ old('show_class_average', $template->show_class_average) ? 'true' : 'false' }},
        showHighestLowest: {{ old('show_highest_lowest', $template->show_highest_lowest) ? 'true' : 'false' }},
        showRanking: {{ old('show_ranking', $template->show_ranking) ? 'true' : 'false' }},
        showSignatureArea: {{ old('show_signature_area', $template->show_signature_area) ? 'true' : 'false' }},
        suggestedRemarksEnabled: {{ old('suggested_remarks_enabled', $template->suggested_remarks_enabled) ? 'true' : 'false' }},
    }">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Modèle de Bulletin</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Configurez les éléments affichés sur les bulletins générés. L'aperçu à droite se met à jour en direct.</p>
        </div>
        @if($previewStudent)
            <a href="{{ route('school.academic.bulletins.print', $previewStudent->id) }}" target="_blank"
               class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-50 transition">
                <i class="ph-bold ph-file-pdf text-lg"></i> Bulletin réel ({{ $previewStudent->first_name }} {{ $previewStudent->last_name }})
            </a>
        @endif
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <form action="{{ route('school.academic.bulletins.template.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Nom du modèle</label>
                <input type="text" name="name" value="{{ old('name', $template->name) }}" required class="w-full max-w-md bg-slate-50 border border-slate-200 text-slate-900 text-[14px] font-medium rounded-xl px-4 py-3 outline-none focus:border-[#031C5B]">
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-[15px] font-extrabold text-slate-900 mb-4">Colonnes à Afficher</h3>
                <div class="space-y-3">
                    <label class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-200 hover:bg-slate-50/60 cursor-pointer transition">
                        <span class="text-[13.5px] font-semibold text-slate-700">Coefficient</span>
                        <input type="checkbox" name="show_coefficient" value="1" x-model="showCoefficient" class="w-4 h-4 rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]/20">
                    </label>
                    <label class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-200 hover:bg-slate-50/60 cursor-pointer transition">
                        <span class="text-[13.5px] font-semibold text-slate-700">Rang par Matière</span>
                        <input type="checkbox" name="show_class_average" value="1" x-model="showClassAverage" class="w-4 h-4 rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]/20">
                    </label>
                    <label class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-200 hover:bg-slate-50/60 cursor-pointer transition">
                        <span class="text-[13.5px] font-semibold text-slate-700">Meilleure / Plus Faible Note</span>
                        <input type="checkbox" name="show_highest_lowest" value="1" x-model="showHighestLowest" class="w-4 h-4 rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]/20">
                    </label>
                    <label class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-200 hover:bg-slate-50/60 cursor-pointer transition">
                        <span class="text-[13.5px] font-semibold text-slate-700">Classement (Rang Général)</span>
                        <input type="checkbox" name="show_ranking" value="1" x-model="showRanking" class="w-4 h-4 rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]/20">
                    </label>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-[15px] font-extrabold text-slate-900 mb-4">Autres Éléments</h3>
                <label class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-200 hover:bg-slate-50/60 cursor-pointer transition">
                    <span class="text-[13.5px] font-semibold text-slate-700">Zone de Signature (Professeur Principal / Directeur)</span>
                    <input type="checkbox" name="show_signature_area" value="1" x-model="showSignatureArea" class="w-4 h-4 rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]/20">
                </label>
            </div>

            <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-6">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="suggested_remarks_enabled" value="1" x-model="suggestedRemarksEnabled" class="mt-1 w-4 h-4 rounded border-blue-300 text-[#031C5B] focus:ring-[#031C5B]/20">
                    <span>
                        <span class="block text-[13.5px] font-bold text-slate-800">Appréciations Suggérées par Barème</span>
                        <span class="block text-[12px] text-slate-500 mt-1">Si aucune appréciation n'a été saisie pour une note, le bulletin imprimé affiche une appréciation générée selon un barème fixe (≥16 Excellent, ≥14 Bien, ≥12 Assez bien, ≥10 Passable, &lt;10 Insuffisant) — pas de génération par intelligence artificielle, un barème déterministe et modifiable à tout moment lors de la saisie.</span>
                    </span>
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all shadow-sm flex items-center gap-2">
                    <i class="ph-bold ph-check"></i> Enregistrer le Modèle
                </button>
            </div>
        </form>

        <!-- Aperçu en direct -->
        <div class="lg:sticky lg:top-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-100 flex items-center gap-2">
                    <i class="ph-bold ph-eye text-[#031C5B]"></i>
                    <span class="text-[13px] font-extrabold text-slate-800">Aperçu en direct</span>
                    <span class="text-[11px] text-slate-400 font-medium">(données d'exemple)</span>
                </div>
                <div class="p-5">
                    <div class="mb-4">
                        <p class="text-[15px] font-extrabold text-slate-900">Aïssatou Diallo</p>
                        <p class="text-[11.5px] text-slate-500">Classe : 3ème A &middot; Matricule : STU-0042</p>
                    </div>

                    <div class="overflow-x-auto -mx-1">
                        <table class="w-full text-[11px] border-collapse">
                            <thead>
                                <tr class="text-left text-slate-400 uppercase text-[9.5px] font-bold border-b-2 border-slate-100">
                                    <th class="px-1.5 py-2">Matière</th>
                                    <th class="px-1.5 py-2">Note/20</th>
                                    <template x-if="showCoefficient">
                                        <th class="px-1.5 py-2">Coef</th>
                                    </template>
                                    <template x-if="showCoefficient">
                                        <th class="px-1.5 py-2">Points</th>
                                    </template>
                                    <template x-if="showClassAverage">
                                        <th class="px-1.5 py-2">Rang</th>
                                    </template>
                                    <template x-if="showHighestLowest">
                                        <th class="px-1.5 py-2">Meil./Faib.</th>
                                    </template>
                                    <th class="px-1.5 py-2">Appréciation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach([
                                    ['subject' => 'Mathématiques', 'score' => 15.5, 'coef' => 4, 'rank' => '2e / 24', 'hilo' => '18 / 06'],
                                    ['subject' => 'Français', 'score' => 12.0, 'coef' => 3, 'rank' => '9e / 24', 'hilo' => '16 / 05'],
                                    ['subject' => 'Anglais', 'score' => 8.5, 'coef' => 2, 'rank' => '17e / 24', 'hilo' => '17 / 04'],
                                ] as $row)
                                @php
                                    $points = $row['score'] * $row['coef'];
                                    $remark = $row['score'] >= 16 ? 'Excellent' : ($row['score'] >= 14 ? 'Bien' : ($row['score'] >= 12 ? 'Assez bien' : ($row['score'] >= 10 ? 'Passable' : 'Insuffisant')));
                                @endphp
                                <tr class="border-b border-slate-50">
                                    <td class="px-1.5 py-2 font-bold text-slate-700">{{ $row['subject'] }}</td>
                                    <td class="px-1.5 py-2 font-bold text-slate-800">{{ number_format($row['score'], 2) }}</td>
                                    <template x-if="showCoefficient">
                                        <td class="px-1.5 py-2 text-slate-500">{{ $row['coef'] }}</td>
                                    </template>
                                    <template x-if="showCoefficient">
                                        <td class="px-1.5 py-2 text-slate-500">{{ number_format($points, 2) }}</td>
                                    </template>
                                    <template x-if="showClassAverage">
                                        <td class="px-1.5 py-2 text-slate-500">{{ $row['rank'] }}</td>
                                    </template>
                                    <template x-if="showHighestLowest">
                                        <td class="px-1.5 py-2 text-slate-500">{{ $row['hilo'] }}</td>
                                    </template>
                                    <td class="px-1.5 py-2 italic text-slate-500">
                                        <span x-show="suggestedRemarksEnabled">{{ $remark }}</span>
                                        <span x-show="!suggestedRemarksEnabled">—</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="grid gap-2 mt-4" :class="showRanking ? 'grid-cols-3' : 'grid-cols-2'">
                        <div class="bg-[#031C5B] text-white rounded-lg p-2.5 text-center">
                            <p class="text-[15px] font-extrabold">12.60/20</p>
                            <p class="text-[8.5px] uppercase font-bold opacity-70 mt-0.5">Moy. Générale</p>
                        </div>
                        <template x-if="showRanking">
                            <div class="bg-slate-50 rounded-lg p-2.5 text-center">
                                <p class="text-[15px] font-extrabold text-slate-800">9e</p>
                                <p class="text-[8.5px] uppercase font-bold text-slate-400 mt-0.5">Rang sur 24</p>
                            </div>
                        </template>
                        <div class="bg-slate-50 rounded-lg p-2.5 text-center">
                            <p class="text-[15px] font-extrabold text-slate-800">1</p>
                            <p class="text-[8.5px] uppercase font-bold text-slate-400 mt-0.5">Absence Inj.</p>
                        </div>
                    </div>

                    <template x-if="showSignatureArea">
                        <div class="flex justify-between mt-6 pt-4 border-t border-slate-100">
                            <div class="text-center w-1/2">
                                <div class="border-t border-slate-300 mx-4 pt-1.5 text-[10px] text-slate-400">Le Professeur Principal</div>
                            </div>
                            <div class="text-center w-1/2">
                                <div class="border-t border-slate-300 mx-4 pt-1.5 text-[10px] text-slate-400">Le Directeur</div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

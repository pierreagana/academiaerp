@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6" x-data="{ search: '' }">
    @include('SchoolDashboard::infirmary._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Répertoire Étudiants</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Gérez les dossiers de santé actifs et les alertes médicales.</p>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Directory -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <div class="relative">
                    <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" x-model="search" placeholder="Rechercher par nom, matricule ou classe..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[13px] rounded-lg pl-9 pr-3 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F8FAFC]">
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Nom de l'Élève</th>
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Classe</th>
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Alertes Santé</th>
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Dernière Visite</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $student)
                            @php $rowText = strtolower($student->first_name . ' ' . $student->last_name . ' ' . $student->roll_number . ' ' . ($student->academicClass->name ?? '')); @endphp
                            <tr x-show="!search || {{ \Illuminate\Support\Js::from($rowText) }}.includes(search.toLowerCase())" class="hover:bg-slate-50/50 transition cursor-pointer {{ $selectedStudent && $selectedStudent->id === $student->id ? 'bg-blue-50/50' : '' }}" onclick="window.location='{{ route('school.infirmary.students', ['student' => $student->id]) }}'">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-[#031C5B]/10 text-[#031C5B] flex items-center justify-center font-bold text-[11px] shrink-0">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-[13.5px] font-bold text-slate-800">{{ $student->first_name }} {{ $student->last_name }}</p>
                                            <p class="text-[11px] text-slate-500">#{{ $student->roll_number }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $student->academicClass->name ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @if($student->allergies)
                                            <span class="px-2 py-1 rounded-full text-[10.5px] font-bold bg-red-100 text-red-700">Allergie</span>
                                        @endif
                                        @if($student->medical_conditions)
                                            <span class="px-2 py-1 rounded-full text-[10.5px] font-bold bg-purple-100 text-purple-700">Condition Médicale</span>
                                        @endif
                                        @if(!$student->allergies && !$student->medical_conditions)
                                            <span class="px-2 py-1 rounded-full text-[10.5px] font-bold bg-slate-100 text-slate-500">Aucune Alerte</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-[13px] font-semibold text-slate-500">{{ $student->last_visit ? \Illuminate\Support\Carbon::parse($student->last_visit)->format('d M Y') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-slate-500 font-medium">Aucun élève enregistré.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Dossier Actif -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            @if($selectedStudent)
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-[16px] font-bold text-[#031C5B] flex items-center gap-2"><i class="ph-bold ph-folder-open"></i> Dossier Actif</h3>
                    <a href="{{ route('school.infirmary.students') }}" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></a>
                </div>

                <div class="text-center mb-5">
                    <div class="w-20 h-20 rounded-full bg-[#031C5B]/10 text-[#031C5B] flex items-center justify-center font-bold text-2xl mx-auto mb-3">
                        {{ substr($selectedStudent->first_name, 0, 1) }}{{ substr($selectedStudent->last_name, 0, 1) }}
                    </div>
                    <h4 class="text-[18px] font-extrabold text-slate-800">{{ $selectedStudent->first_name }} {{ $selectedStudent->last_name }}</h4>
                    <p class="text-[12px] text-slate-400 font-semibold">ID: #{{ $selectedStudent->roll_number }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div class="bg-slate-50 rounded-xl p-3 text-center">
                        <p class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Groupe Sanguin</p>
                        <p class="text-[16px] font-extrabold text-slate-800 mt-0.5">{{ $selectedStudent->blood_group ?? 'Inconnu' }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3 text-center">
                        <p class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Classe</p>
                        <p class="text-[16px] font-extrabold text-slate-800 mt-0.5">{{ $selectedStudent->academicClass->name ?? '-' }}</p>
                    </div>
                </div>

                @if($selectedStudent->allergies || $selectedStudent->medical_conditions)
                <div class="bg-red-50/60 border border-red-100 rounded-xl p-4 mb-5">
                    <p class="text-[11px] font-extrabold text-red-600 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="ph-fill ph-warning"></i> Alertes & Antécédents</p>
                    @if($selectedStudent->allergies)
                        <div class="flex items-start gap-2 mb-2">
                            <i class="ph-bold ph-first-aid text-red-500 mt-0.5"></i>
                            <div>
                                <p class="text-[12.5px] font-bold text-slate-800">Allergies</p>
                                <p class="text-[12px] text-slate-600">{{ $selectedStudent->allergies }}</p>
                            </div>
                        </div>
                    @endif
                    @if($selectedStudent->medical_conditions)
                        <div class="flex items-start gap-2">
                            <i class="ph-bold ph-heartbeat text-red-500 mt-0.5"></i>
                            <div>
                                <p class="text-[12.5px] font-bold text-slate-800">Conditions Médicales</p>
                                <p class="text-[12px] text-slate-600">{{ $selectedStudent->medical_conditions }}</p>
                            </div>
                        </div>
                    @endif
                </div>
                @else
                <div class="bg-slate-50 rounded-xl p-4 mb-5 text-center">
                    <p class="text-[12.5px] text-slate-400">Aucune alerte médicale enregistrée.</p>
                </div>
                @endif

                @php $emergencyContact = $selectedStudent->guardians->first(); @endphp
                <div class="bg-slate-50 rounded-xl p-4 mb-5">
                    <p class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider mb-2">Contact d'Urgence</p>
                    @if($emergencyContact)
                        <p class="text-[13.5px] font-bold text-slate-800">{{ $emergencyContact->name }}</p>
                        <p class="text-[12.5px] text-slate-500 flex items-center gap-1.5 mt-0.5"><i class="ph-bold ph-phone"></i> {{ $emergencyContact->phone }}</p>
                    @else
                        <p class="text-[12.5px] text-slate-400">Aucun tuteur lié à cet élève.</p>
                    @endif
                </div>

                @if($showFullHistory)
                    <div class="mb-5">
                        <p class="text-[13px] font-bold text-slate-800 mb-2">Historique Complet</p>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            @forelse($history as $item)
                                <div class="bg-slate-50 rounded-lg p-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[12px] font-bold text-slate-700">{{ $item->arrival_time->format('d/m/Y H:i') }}</span>
                                        <span class="text-[10.5px] font-bold text-slate-500">{{ \App\Modules\Infirmary\Domain\Models\Intervention::DECISIONS[$item->decision] ?? $item->decision }}</span>
                                    </div>
                                    <p class="text-[12px] text-slate-600 mt-1">{{ $item->motive }}@if($item->care_notes) — {{ $item->care_notes }}@endif</p>
                                </div>
                            @empty
                                <p class="text-[12px] text-slate-400">Aucune intervention enregistrée.</p>
                            @endforelse
                        </div>
                    </div>
                @else
                    <a href="{{ route('school.infirmary.students', ['student' => $selectedStudent->id, 'full_history' => 1]) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition mb-3">
                        <i class="ph-bold ph-clock-counter-clockwise"></i> Voir l'Historique Complet
                    </a>
                @endif

                <a href="{{ route('school.infirmary.students.print', $selectedStudent->id) }}" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-slate-200 text-slate-600 rounded-xl text-[13px] font-bold hover:bg-slate-50 transition">
                    <i class="ph-bold ph-printer"></i> Imprimer le Dossier
                </a>
            @else
                <div class="text-center py-16">
                    <i class="ph-bold ph-user-circle text-4xl text-slate-300 mb-3 block"></i>
                    <p class="text-[13px] text-slate-400">Sélectionnez un élève pour voir son dossier.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

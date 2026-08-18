@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            @if($student->photo_path)
                <img src="{{ asset('storage/' . $student->photo_path) }}" class="w-16 h-16 rounded-xl object-cover border border-slate-200">
            @else
                <div class="w-16 h-16 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400"><i class="ph-bold ph-user text-2xl"></i></div>
            @endif
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-[22px] font-extrabold text-[#0F172A]">{{ $student->first_name }} {{ $student->last_name }}</h2>
                    @if($student->academicClass)
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700">{{ $student->academicClass->name }}</span>
                    @endif
                </div>
                <p class="text-[12.5px] text-slate-500 mt-0.5">Matricule: #{{ $student->roll_number }} @if($student->dob) &middot; Né(e) le {{ \Carbon\Carbon::parse($student->dob)->format('d M Y') }} @endif</p>
                @if($student->guardians->isNotEmpty())
                    <p class="text-[12.5px] text-slate-500 mt-0.5 flex items-center gap-3">
                        <span class="flex items-center gap-1"><i class="ph ph-envelope"></i> {{ $student->guardians->first()->email ?? '—' }}</span>
                        <span class="flex items-center gap-1"><i class="ph ph-phone"></i> {{ $student->guardians->first()->phone ?? '—' }}</span>
                    </p>
                @endif
            </div>
        </div>
        <a href="{{ route('school.report-card.student.print', $student->id) }}" target="_blank" class="flex items-center gap-2 bg-[#031C5B] text-white px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition">
            <i class="ph-bold ph-file-pdf text-lg"></i> Livret Officiel PDF
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-[16px] font-extrabold text-slate-900">Cartographie des Compétences</h3>
                    <div class="flex items-center gap-3 text-[11px] font-medium text-slate-500">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Acquis</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span> En cours</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> Non acquis</span>
                    </div>
                </div>

                @forelse($competencyTree as $domainName => $subdomains)
                    <div class="mb-5 last:mb-0">
                        <h4 class="text-[13px] font-extrabold text-slate-700 mb-2">{{ $domainName }}</h4>
                        @foreach($subdomains as $subdomainName => $competencies)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-3">
                                @foreach($competencies as $competency)
                                    @php $level = $competencyMap[$competency->id]->level ?? null; @endphp
                                    <div class="flex items-center justify-between px-3 py-2 rounded-lg border border-slate-100 bg-slate-50/60">
                                        <span class="text-[12.5px] text-slate-600">{{ $competency->statement }}</span>
                                        @if($level === 'acquis')
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">Acquis</span>
                                        @elseif($level === 'en_cours')
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">En cours</span>
                                        @elseif($level === 'non_acquis')
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-50 text-red-700">Non acquis</span>
                                        @else
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-400">Non évalué</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @empty
                    <p class="text-[13px] text-slate-400">Aucun référentiel de compétences configuré pour ce cycle.</p>
                @endforelse
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center"><i class="ph-bold ph-brain text-lg"></i></span>
                    <h3 class="text-[16px] font-extrabold text-slate-900">Observations & Comportement</h3>
                </div>

                <div class="space-y-4 mb-4">
                    @forelse($observations as $observation)
                        <div class="border-l-2 border-slate-200 pl-4">
                            <p class="text-[11.5px] font-extrabold text-slate-500 uppercase tracking-wide">{{ $observation->teacher ? strtoupper(trim($observation->teacher->first_name . ' ' . $observation->teacher->last_name)) : '—' }}</p>
                            <p class="text-[13px] text-slate-600 mt-1">{{ $observation->comment }}</p>
                        </div>
                    @empty
                        <p class="text-[13px] text-slate-400">Aucune observation enregistrée pour cet élève.</p>
                    @endforelse
                </div>

                <form action="{{ route('school.report-card.observations.store') }}" method="POST" class="pt-4 border-t border-slate-100">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <textarea name="comment" required rows="2" placeholder="Ajouter une observation..." class="w-full bg-slate-50 border border-slate-200 text-[13px] rounded-xl px-3 py-2.5 outline-none focus:border-[#031C5B]"></textarea>
                    <div class="flex justify-end mt-2">
                        <button type="submit" class="text-[12.5px] font-bold text-white bg-[#031C5B] px-4 py-2 rounded-lg hover:bg-[#031C5B]/90">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="ph-bold ph-chart-line-up text-lg"></i></span>
                    <h3 class="text-[15px] font-extrabold text-slate-900">Profil d'Apprentissage</h3>
                </div>
                @if($radarData->isEmpty())
                    <p class="text-[12.5px] text-slate-400">Pas encore assez d'évaluations pour comparer les semestres.</p>
                @else
                    <div class="space-y-3">
                        @php $domainNames = $radarData->flatMap(fn($s) => $s->keys())->unique(); @endphp
                        @foreach($domainNames as $domainName)
                            <div>
                                <p class="text-[11.5px] font-bold text-slate-600 mb-1">{{ $domainName }}</p>
                                @foreach($radarData as $semesterName => $scores)
                                    @if(isset($scores[$domainName]))
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-[10px] text-slate-400 w-8">{{ $semesterName }}</span>
                                            <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-[#031C5B]" style="width: {{ $scores[$domainName] }}%"></div>
                                            </div>
                                            <span class="text-[10px] font-bold text-slate-500 w-8 text-right">{{ $scores[$domainName] }}%</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="ph-bold ph-calendar-check text-lg"></i></span>
                    <h3 class="text-[15px] font-extrabold text-slate-900">Assiduité (Année)</h3>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-blue-50/60 rounded-xl p-3 text-center">
                        <p class="text-[24px] font-extrabold text-[#031C5B]">{{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}</p>
                        <p class="text-[10.5px] font-bold text-slate-500">Taux de présence</p>
                    </div>
                    <div class="bg-red-50/60 rounded-xl p-3 text-center">
                        <p class="text-[24px] font-extrabold text-red-600">{{ $unjustifiedAbsences }}</p>
                        <p class="text-[10.5px] font-bold text-slate-500">Absences nj.</p>
                    </div>
                </div>
                <p class="text-[10.5px] font-extrabold text-slate-400 uppercase tracking-wide mb-2">Derniers signalements</p>
                <div class="space-y-2">
                    @forelse($recentRecords as $record)
                        <div class="flex items-center justify-between text-[12px]">
                            <span class="text-slate-500 flex items-center gap-1.5"><i class="ph ph-calendar text-slate-400"></i> {{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}</span>
                            @if($record->status === 'absent')
                                <span class="font-bold px-2 py-0.5 rounded-full {{ $record->justified ? 'bg-slate-100 text-slate-500' : 'bg-red-50 text-red-700' }}">{{ $record->justified ? 'Absence (Justifiée)' : 'Absence' }}</span>
                            @elseif($record->status === 'late')
                                <span class="font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">Retard{{ $record->late_minutes ? ' (' . $record->late_minutes . 'm)' : '' }}</span>
                            @else
                                <span class="font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">Présent</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-[12px] text-slate-400">Aucun signalement récent.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
